<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Entity\Auction;
use App\Form\AuctionType;
use App\Repository\AuctionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
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

        return $this->render('profile/sales/index.html.twig', [
            'sales' => $sales,
        ]);
    }

    #[Route("/account/sales/{slug}", name:"account_sales_show")]
    #[IsGranted('ROLE_USER')]
    public function showSales(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, AuctionRepository $auctionRepo)
    {
        $user = $this->getUser();
        $artworkOwner = $artwork->getAuthor();

        // verif si user connecté = user artwork
        if ($user === $artworkOwner) {
            // recup les enchères liées à l'artwork
            $auctions = $auctionRepo->findAuctionsByArtwork($artwork);

            return $this->render('profile/sales/show.html.twig', [
                'artwork' => $artwork,
                'auctions' => $auctions,
            ]);
        } else {
            $this->addFlash('danger', 'Vous ne pouvez pas voir les enchères d\'autres utilisateurs');

            return $this->redirectToRoute('artworks_show', [
                'slug'=> $artwork->getSlug()
            ]);     
        }
    }

    #[Route("/artworks/{slug}/make-a-bid", name: "auctions_create")]
    #[IsGranted('ROLE_USER')]
    public function create(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, Request $request, EntityManagerInterface $manager): Response
    {
        $auction = new Auction();
        $form = $this->createform(AuctionType::class, $auction);

        //traitement des données - associations aux champs respectifs - validation
        $form->handleRequest($request);

        //form complet et valid -> envoi bdd + message et redirection
        if($form->isSubmitted() && $form->IsValid())
        {
            $auction->setUser($this->getUser());
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

        return $this->render("artworks/auction.html.twig",[
            'myForm' => $form->createView(),  
        ]);
    }


}
