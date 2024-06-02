<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Entity\Auction;
use App\Form\AuctionType;
use App\Repository\AuctionRepository;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuctionController extends AbstractController
{
    /**
     * Display auctions
     *
     * @param AuctionRepository $auctionRepo
     * @return void
     */
    #[Route("/account/auctions", name:"account_auctions")]
    #[IsGranted('ROLE_USER')]
    public function displayAuctions(AuctionRepository $auctionRepo)
    {
        $user = $this->getUser(); // get connected user
        $auctions = $auctionRepo->findAuctionsByUser($user); //get auctions made by user

        //get all refused auctions
        $refusedAuctions = [];
        foreach ($auctions as $auction) {
            $artwork = $auction->getArtwork();
            $refusedAuctions[$artwork->getId()] = $auctionRepo->findRefusedAuctionsByUser($artwork, $user);
        }

        //get current auctions
        $ongoingAuctions = $auctionRepo->findOngoingAuctionsByUser($user);

        return $this->render('profile/auctions.html.twig', [
            'auctions' => $auctions,
            'refusedAuctions' => $refusedAuctions,
            'ongoingAuctions' => $ongoingAuctions
        ]);
    }


    /**
     * Display auctions for a single artwork
     *
     * @param Artwork $artwork
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/account/sales/{slug}", name:"account_sales_show")]
    #[IsGranted('ROLE_USER')]
    public function showSales(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, AuctionRepository $auctionRepo)
    {
        $user = $this->getUser();
        $artworkOwner = $artwork->getAuthor();
        
        // check if connected user == artwork's author -> if not, redirection
        if ($user === $artworkOwner) {
            //current datetime to compare with artwork.endDate
            $currentDate = new \DateTime();

            //display artwork's movements
            $movements = $artwork->getMovements();

            // recup les enchères liées à l'artwork
            $auctions = $auctionRepo->findAuctionsByArtwork($artwork);

            //get the winning auction
            $auctionAccepted = $auctionRepo->findAcceptedAuction($artwork);
    
            //3 biggest auctions
            $topThree = $auctionRepo->topThree($artwork);

            return $this->render('profile/sales/show.html.twig', [
                'artwork' => $artwork,
                'auctions' => $auctions,
                'currentDate' => $currentDate,
                'auctionAccepted' => $auctionAccepted,
                'topThree' => $topThree,
                'movements' => $movements,
                //context for _display.html.twig (delayed display)
                'context' => 'account_sales_show',
            ]);
        } else {
            $this->addFlash('danger', "You are not allowed to see this artwork's auctions.");

            return $this->redirectToRoute('artworks_show', [
                'slug'=> $artwork->getSlug(),
            ]);     
        }
    }

    /**
     * Accept an auction
     *
     * @param Auction $auction
     * @param AuctionRepository $auctionRespo
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    #[Route("/account/sales/{id}/accept", name:"account_sales_accept")]
    #[IsGranted('ROLE_USER')]
    public function acceptAuction(#[MapEntity(mapping: ['id' => 'id'])] Auction $auction, AuctionRepository $auctionRepo, EntityManagerInterface $manager): RedirectResponse
    {
        $artwork = $auction->getArtwork();
    
        //if an auction as already been accepted
        $existingSoldAuction = $auctionRepo->findAcceptedAuction($artwork);
    
        if ($existingSoldAuction !== null) {
            $this->addFlash(
                'warning',
                "This artwork has already been sold."
            );
    
            return $this->redirectToRoute('account_sales_show', [
                'slug'=> $artwork->getSlug()
            ]);
        }
        
        
        //set auction to accepted + artwork archived
        $auction->setSold('yes');
        $artwork->setArchived(true);
        $manager->persist($auction);
        $manager->flush();
        
        $this->addFlash(
            'success',
            "The offer of <strong>".$auction->getUser()->getFullName()."</strong> has been accepted !"
        );
        
        return $this->redirectToRoute('account_sales_show', [
            'slug'=> $auction->getArtwork()->getSlug()
        ]);
    }

    /**
     * Refuse an auction
     *
     * @param Auction $auction
     * @param AuctionRepository $auctionRespo
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    #[Route("/account/sales/{id}/refuse", name:"account_sales_refuse")]
    #[IsGranted('ROLE_USER')]
    public function refuseAuction(#[MapEntity(mapping: ['id' => 'id'])] Auction $auction, AuctionRepository $auctionRepo, EntityManagerInterface $manager): RedirectResponse
    {
        //delete it from database
        $manager->remove($auction);
        $manager->flush();

        $this->addFlash(
            'danger',
            "The offer of <strong>".$auction->getUser()->getFullName()."</strong> has been refused."
        );

        return $this->redirectToRoute('account_sales_show', [
            'slug'=> $auction->getArtwork()->getSlug()
        ]);
    }

    /**
     * Relaunch the chrono for a week
     *
     * @param Artwork $artwork
     * @param AuctionRepository $auctionRespo
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    #[Route("/account/sales/{slug}/relaunch", name:"account_sales_relaunch")]
    #[IsGranted('ROLE_USER')]
    public function relaunchAuction(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, AuctionRepository $auctionRepo, EntityManagerInterface $manager): RedirectResponse
    {
        $currentDate = new \DateTime();
        $endDate = $artwork->getEndDate();

        // if time is up and auctions count == 0, relaunch for a week
        if ($endDate <= $currentDate && $auctionRepo->countAuctionsByArtwork($artwork) === 0) {
            $newEndDate = $currentDate->modify('+1 week');
            $artwork->setEndDate($newEndDate);

            $manager->persist($artwork);
            $manager->flush();

            $this->addFlash('success', "Countdown relaunched for a week");

            return $this->redirectToRoute('account_sales_show', [
                'slug'=> $artwork->getSlug(),
            ]);
        } else {
            $this->addFlash('danger', 'We cannot restart a countdown for this artwork.');

            return $this->redirectToRoute('account_sales_show', [
                'slug'=> $artwork->getSlug(),
            ]);
        }
    }


    /**
     * Make a bid
     *
     * @param Artwork $artwork
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param AuctionRepository $auctionRespo
     * @return Response
     */
    #[Route("/artworks/{slug}/make-a-bid", name: "auctions_create")]
    #[IsGranted('ROLE_USER')]
    public function create(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, Request $request, EntityManagerInterface $manager, AuctionRepository $auctionRepo): Response
    {
        $user = $this->getUser();
        $artworkOwner = $artwork->getAuthor();

        //a user cannot make a bid on his own artwork
        if ($user === $artworkOwner) {
            $this->addFlash('danger', "You are not allowed to place a bid on your own artwork.");
            
            return $this->redirectToRoute('artworks_show', [
                'slug'=> $artwork->getSlug()
            ]);
        }

        $endDate = $artwork->getEndDate();

        //check if time is up
        if ($endDate <= new \DateTime()) {
            $this->addFlash('danger', "The auctions for this artwork are closed");
            
            return $this->redirectToRoute('artworks_show', [
                'slug'=> $artwork->getSlug()
            ]);
        }

        // check if the auctionner has already placed a bid on this artwork
        $existingAuction = $auctionRepo->findOneBy(['user' => $user, 'artwork' => $artwork]);
        if ($existingAuction !== null) {
            $this->addFlash('danger', "You have already placed a bid on this artwork.");
            
            return $this->redirectToRoute('artworks_show', [
                'slug'=> $artwork->getSlug()
            ]);
        }

        $auction = new Auction();
        $form = $this->createform(AuctionType::class, $auction);

        //handle form
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->IsValid())
        {
            $amount = $form->get('amount')->getData();
            $priceInit = $artwork->getPriceInit();

            //check if the amount is at least equal to the initial price
            if ($amount < $priceInit) {
                $form->addError(new FormError("Your bid must be equal or greather than the initial price."));
            } else {
                $auction->setUser($user);
                $auction->setArtwork($artwork);
                $auction->setSubmissionDate(new \DateTime());
                $auction->setSold('no');
    
                $manager->persist($auction);    
                $manager->flush();
    
                $this->addFlash(
                    'success',
                    "Your bid for <strong>".$artwork->getTitle()."</strong> has been registered. Good luck !"
                );
            
                return $this->redirectToRoute('artworks_show', [
                    'slug'=> $artwork->getSlug()
                ]);
            }
        }
        return $this->render("artworks/auction.html.twig",[
            'artwork' => $artwork,
            'myForm' => $form->createView(),  
        ]);
    }

}
