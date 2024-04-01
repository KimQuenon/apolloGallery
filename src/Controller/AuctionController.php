<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Repository\AuctionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuctionController extends AbstractController
{
    #[Route("/account/auctions", name:"account_auctions")]
    #[IsGranted('ROLE_USER')]
    public function displayAuctions()
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté
        $auctions = $user->getAuctions(); // Récupérer les enchères liées à l'utilisateur

        return $this->render('profile/auctions.html.twig', [
            'auctions' => $auctions,
        ]);
    }

    #[Route("/account/sales", name:"account_sales_index")]
    #[IsGranted('ROLE_USER')]
    public function displaySales(AuctionRepository $auctionRepo)
    {
        $user = $this->getUser(); // recup l'utilisateur connecté
        $sales = $auctionRepo->findAuctionsOfUserArtworks($user);

        return $this->render('profile/sales/index.html.twig', [
            'sales' => $sales,
        ]);
    }

    #[Route("/account/sales/{slug}", name:"account_sales_show")]
    #[IsGranted('ROLE_USER')]
    public function displaySalesByArtwork(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, AuctionRepository $auctionRepo)
    {
        $user = $this->getUser(); // recup l'utilisateur connecté
        $auctions = $artwork->getAuctions();

        return $this->render('profile/sales/show.html.twig', [
            'artwork' => $artwork,
            'auctions' => $auctions,
        ]);
    }
}
