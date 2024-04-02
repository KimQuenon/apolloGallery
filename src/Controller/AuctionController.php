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
    #[Route("/account/auctions", name:"account_auctions")]
    #[IsGranted('ROLE_USER')]
    public function displayAuctions(AuctionRepository $auctionRepo)
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté
        $auctions = $auctionRepo->findAuctionsByUser($user);

        return $this->render('profile/auctions.html.twig', [
            'auctions' => $auctions,
        ]);
    }

    #[Route("/account/sales", name:"account_sales_index")]
    #[IsGranted('ROLE_USER')]
    public function indexSales(AuctionRepository $auctionRepo)
    {
        $user = $this->getUser(); // recup l'utilisateur connecté
        $sales = $auctionRepo->findSales($user);
        // $currentDate = new \DateTime();

        return $this->render('profile/sales/index.html.twig', [
            'sales' => $sales,
            // 'currentDate' => $currentDate,
        ]);
    }

    #[Route("/account/sales/{slug}", name:"account_sales_show")]
    #[IsGranted('ROLE_USER')]
    public function showSales(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, AuctionRepository $auctionRepo)
    {
        $user = $this->getUser();
        $artworkOwner = $artwork->getAuthor();
        $currentDate = new \DateTime();

        $auctionAccepted = $auctionRepo->findAcceptedAuction($artwork);
        $auctionCount = $auctionRepo->countAuctionsByArtwork($artwork);

        // verif si user connecté = user artwork
        if ($user === $artworkOwner) {
            // recup les enchères liées à l'artwork
            $auctions = $auctionRepo->findAuctionsByArtwork($artwork);

            return $this->render('profile/sales/show.html.twig', [
                'artwork' => $artwork,
                'auctions' => $auctions,
                'currentDate' => $currentDate,
                'auctionAccepted' => $auctionAccepted,
                'auctionCount' => $auctionCount,
            ]);
        } else {
            $this->addFlash('danger', 'Vous ne pouvez pas voir les enchères d\'autres utilisateurs');

            return $this->redirectToRoute('artworks_show', [
                'slug'=> $artwork->getSlug(),
            ]);     
        }
    }

    #[Route("/account/sales/{id}/accept", name:"account_sales_accept")]
    #[IsGranted('ROLE_USER')]
    public function acceptAuction(#[MapEntity(mapping: ['id' => 'id'])] Auction $auction, AuctionRepository $auctionRepo, EntityManagerInterface $manager)
    {
        $artwork = $auction->getArtwork();
    
        // verif si une enchère a déjà été acceptée pour l'œuvre
        $existingSoldAuction = $auctionRepo->findAcceptedAuction($artwork);
    
        if ($existingSoldAuction !== null) {
            $this->addFlash(
                'warning',
                "Une enchère pour cette œuvre a déjà été acceptée."
            );
    
            return $this->redirectToRoute('account_sales_show', [
                'slug'=> $artwork->getSlug()
            ]);
        }
        
        
        //enchère acceptée
        $auction->setSold('yes');
        $manager->persist($auction);
        $manager->flush();
        
        $this->addFlash(
            'success',
            "L'offre de <strong>".$auction->getUser()->getFullName()."</strong> a bien été acceptée."
        );
        
        return $this->redirectToRoute('account_sales_show', [
            'slug'=> $auction->getArtwork()->getSlug()
        ]);
    }

    #[Route("/account/sales/{id}/refuse", name:"account_sales_refuse")]
    #[IsGranted('ROLE_USER')]
    public function refuseAuction(#[MapEntity(mapping: ['id' => 'id'])] Auction $auction, AuctionRepository $auctionRepo, EntityManagerInterface $manager)
    {

        $manager->remove($auction);
        $manager->flush();

        $this->addFlash(
            'danger',
            "L'enchère de <strong>".$auction->getUser()->getFullName()."</strong> a été refusée."
        );

        return $this->redirectToRoute('account_sales_show', [
            'slug'=> $auction->getArtwork()->getSlug()
        ]);
    }

    #[Route("/account/sales/{slug}/relaunch", name:"account_sales_relaunch")]
    #[IsGranted('ROLE_USER')]
    public function relaunchAuction(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, AuctionRepository $auctionRepo, EntityManagerInterface $manager): RedirectResponse
    {
        $currentDate = new \DateTime();
        $endDate = $artwork->getEndDate();

        // si pas d'offres au temps écoulé => relancer une semaine
        if ($endDate <= $currentDate && $auctionRepo->countAuctionsByArtwork($artwork) === 0) {
            $newEndDate = $currentDate->modify('+1 week');
            $artwork->setEndDate($newEndDate);

            $manager->persist($artwork);
            $manager->flush();

            $this->addFlash('success', 'La date de fin de l\'enchère a été prolongée d\'une semaine.');

            return $this->redirectToRoute('account_sales_show', [
                'slug'=> $artwork->getSlug(),
            ]);
        } else {
            $this->addFlash('danger', 'Cette enchère ne peut pas être prolongée.');

            return $this->redirectToRoute('account_sales_show', [
                'slug'=> $artwork->getSlug(),
            ]);
        }
    }


    #[Route("/artworks/{slug}/make-a-bid", name: "auctions_create")]
    #[IsGranted('ROLE_USER')]
    public function create(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, Request $request, EntityManagerInterface $manager, AuctionRepository $auctionRepo): Response
    {
        $user = $this->getUser();
        $artworkOwner = $artwork->getAuthor();

        if ($user === $artworkOwner) {
            $this->addFlash('danger', 'Vous ne pouvez pas faire d\'enchère sur votre propre œuvre.');
            
            return $this->redirectToRoute('artworks_show', [
                'slug'=> $artwork->getSlug()
            ]);
        }

        $endDate = $artwork->getEndDate();

        if ($endDate <= new \DateTime()) {
            $this->addFlash('danger', "L'enchère pour cette œuvre est terminée.");
            
            return $this->redirectToRoute('artworks_show', [
                'slug'=> $artwork->getSlug()
            ]);
        }

        // Vérifier si l'utilisateur a déjà fait une enchère sur cet artwork
        $existingAuction = $auctionRepo->findOneBy(['user' => $user, 'artwork' => $artwork]);
        if ($existingAuction !== null) {
            $this->addFlash('danger', "Vous avez déjà fait une enchère sur cette œuvre.");
            
            return $this->redirectToRoute('artworks_show', [
                'slug'=> $artwork->getSlug()
            ]);
        }

        $auction = new Auction();
        $form = $this->createform(AuctionType::class, $auction);

        //traitement des données - associations aux champs respectifs - validation
        $form->handleRequest($request);

        //form complet et valid -> envoi bdd + message et redirection
        if($form->isSubmitted() && $form->IsValid())
        {
            $amount = $form->get('amount')->getData();
            $priceInit = $artwork->getPriceInit();

            //verif si montant proposé >= prix initial
            if ($amount < $priceInit) {
                $form->addError(new FormError("Le montant proposé ne peut pas être inférieur au prix initial de l'œuvre."));
            } else {
                $auction->setUser($user);
                $auction->setArtwork($artwork);
                $auction->setSubmissionDate(new \DateTime());
    
                $manager->persist($auction);    
                $manager->flush();
    
                $this->addFlash(
                    'success',
                    "Votre enchère pour <strong>".$artwork->getTitle()."</strong> a bien été enregistrée."
                );
            
                return $this->redirectToRoute('artworks_show', [
                    'slug'=> $artwork->getSlug()
                ]);
            }
        }
        return $this->render("artworks/auction.html.twig",[
            'myForm' => $form->createView(),  
        ]);
    }

}
