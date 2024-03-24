<?php

namespace App\Controller;

use App\Repository\ArtworkRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArtworkController extends AbstractController
{
    #[Route('/artworks', name: 'artworks_index')]
    public function index(ArtworkRepository $repo): Response
    {
        $artworks = $repo->findAll();

        return $this->render('artworks/index.html.twig', [
            'artworks' => $artworks,
        ]);
    }
}
