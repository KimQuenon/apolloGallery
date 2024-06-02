<?php

namespace App\Controller;

use App\Repository\ArtworkRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    
    /**
     * Show profile
     *
     * @param ArtworkRepository $artworkRepo
     * @return Response
     */
    #[Route("/account/profile/", name:"account_profile")]
    #[IsGranted('ROLE_USER')]
    public function index(ArtworkRepository $artworkRepo): Response
    {
        $user = $this->getUser();
        $artworks = $user->getArtworks();
        //get 4 latest artworks (param int)
        $latestArtworks = $artworkRepo->findLatestArtworksByUser($user, 4);

        //get all reviews
        $reviews = [];
        foreach ($artworks as $artwork) {
            $review = $artwork->getReview();
            if ($review !== null) {
                $reviews[] = $review;
            }
        }
        
        $archivedArtworks = $artworkRepo->findArchivedArtworksByUser($user);

        return $this->render("profile/index.html.twig",[
            'user'=>$user,
            'reviews' => $reviews,
            'archivedArtworks' => $archivedArtworks,
            'latestArtworks' => $latestArtworks,
        ]);
    }

    /**
     * Display user's artworks
     *
     * @param ArtworkRepository $artworkRepo
     * @return void
     */
    #[Route("/account/artworks", name:"account_artworks")]
    #[IsGranted('ROLE_USER')]
    public function displayArtworks(ArtworkRepository $artworkRepo)
    {
        $user = $this->getUser();
        $artworks = $artworkRepo->findArtworkUserDesc($user);
        $currentDate = new \DateTime();

        return $this->render('profile/artworks.html.twig', [
            'artworks' => $artworks,
            'currentDate' => $currentDate
        ]);
    }

    /**
     * Display user's archives (sold artworks)
     *
     * @param ArtworkRepository $artworkRepo
     * @return void
     */
    #[Route("/account/archives", name:"account_archives")]
    #[IsGranted('ROLE_USER')]
    public function displayArchivedArtworks(ArtworkRepository $artworkRepo)
    {
        $user = $this->getUser();
        $archivedArtworks = $artworkRepo->findArchivedArtworksByUser($user);

        return $this->render('profile/archives.html.twig', [
            'archivedArtworks' => $archivedArtworks,
        ]);
    }
}
