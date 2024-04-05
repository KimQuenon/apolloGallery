<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\Artwork;
use App\Form\ReviewType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReviewController extends AbstractController
{
    #[Route('/artworks/{slug}/review', name: 'artworks_review')]
    #[IsGranted('ROLE_USER')]
    public function create(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();

        $review = new Review();
        $form = $this->createform(ReviewType::class, $review);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->IsValid())
        {
            $review->setAuthor($user);
            $review->setArtwork($artwork);

            $manager->persist($review);    
            $manager->flush();

            $this->addFlash(
                'success',
                "Merci pour votre avis !"
            );

            return $this->redirectToRoute('artworks_index', []);
        }

        return $this->render('artworks/review.html.twig', [
            'myForm' => $form->createView(),
        ]);
    }

    #[Route('/account/reviews', name: 'account_reviews')]
    public function index(): Response
    {
        $user = $this->getUser();
        $artworks = $user->getArtworks();

        $reviews = [];
        foreach ($artworks as $artwork) {
            $review = $artwork->getReview();
            if ($review !== null) {
                $reviews[] = $review;
            }
        }

        return $this->render('profile/reviews.html.twig', [
            'user' => $user,
            'reviews' => $reviews,
        ]);
    }
}
