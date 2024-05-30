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

        // recup les enchères de l'artwork
        $auctions = $artwork->getAuctions();

        // user connecté = auteur de l'enchère ?
        $isAuthorOfAuction = false;
        foreach ($auctions as $auction) {
            if ($auction->getUser() === $user) {
                $isAuthorOfAuction = true;
                break;
            }
        }

        // user = auteur de l'annonce?
        $isAuthorOfArtwork = ($user === $artwork->getAuthor());

        // si user = auteur de l'annonce ou n'a pas d'enchère = refus
        if (!$isAuthorOfAuction && !$isAuthorOfArtwork) {
            $this->addFlash(
                'danger',
                "Vous n'êtes pas autorisé à voter pour cette oeuvre."
            );
            return $this->redirectToRoute('artworks_index');
        }

        $existingReview = $artwork->getReview();
        if ($existingReview !== null) {
            $this->addFlash(
                'danger',
                "Cette oeuvre a déjà une review associée."
            );
            return $this->redirectToRoute('artworks_index');
        }

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
            'artwork'=> $artwork,
        ]);
    }
}
