<?php

namespace App\Controller;

use App\Repository\ArtworkRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    //page du profil utilisateur
    #[Route("/account/profile/", name:"account_profile")]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render("profile/index.html.twig",[
            'user'=>$user
        ]);
    }

    //oeuvres de l'utilisateur
    #[Route("/account/artworks", name:"account_artworks")]
    #[IsGranted('ROLE_USER')]
    public function displayArtworks(ArtworkRepository $artworkRepo)
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté
        $artworks = $artworkRepo->findArtworkUserDesc($user);
        $currentDate = new \DateTime();

        return $this->render('profile/artworks.html.twig', [
            'artworks' => $artworks,
            'currentDate' => $currentDate
        ]);
    }
}
