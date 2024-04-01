<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
}
