<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Entity\Movement;
use App\Repository\ArtworkRepository;
use App\Repository\MovementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArtworkController extends AbstractController
{
    #[Route('/artworks', name: 'artworks_index')]
    public function index(ArtworkRepository $repo, MovementRepository $movementRepo): Response
    {
        $artworks = $repo->findAll();
        $movements = $movementRepo->findAll();

        return $this->render('artworks/index.html.twig', [
            'artworks' => $artworks,
            'movements' => $movements,
        ]);
    }

    #[Route("artworks/movements/{slug}", name: "movements_show")]
    public function showMovement(#[MapEntity(mapping: ['slug' => 'slug'])] Movement $movement, ArtworkRepository $repo): Response
    {
        $artworks = $movement->getArtwork();
        return $this->render('artworks/movements/show.html.twig', [
            'movement' => $movement,
            'artworks' => $artworks
        ]);

    }
    
    #[Route("/artworks/{slug}", name: "artworks_show")]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork): Response
    {
        return $this->render("artworks/show.html.twig", [
            'artwork' => $artwork,
        ]);
    }

}
