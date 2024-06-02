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
    /**
     * Write a review
     *
     * @param Artwork $artwork
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/artworks/{slug}/review', name: 'artworks_review')]
    #[IsGranted('ROLE_USER')]
    public function create(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();

        // recup les enchères de l'artwork
        $auctions = $artwork->getAuctions();

        // check if user connected = winner of the auction?
        $isAuthorOfAuction = false;
        foreach ($auctions as $auction) {
            if ($auction->getUser() === $user) {
                $isAuthorOfAuction = true;
                break;
            }
        }

        // check user connected == owner of the artwork ?
        $isAuthorOfArtwork = ($user === $artwork->getAuthor());

        // if is owner of the artwork or not the winner of the auction
        if (!$isAuthorOfAuction && !$isAuthorOfArtwork) {
            $this->addFlash(
                'danger',
                "You are not allowed to review this artworks"
            );
            return $this->redirectToRoute('artworks_index');
        }

        //check if the artwork has been reviewed
        $existingReview = $artwork->getReview();
        if ($existingReview !== null) {
            $this->addFlash(
                'danger',
                "This artwork has already been reviewed."
            );
            return $this->redirectToRoute('artworks_index');
        }

        $review = new Review();
        $form = $this->createform(ReviewType::class, $review);
        //handle form
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->IsValid())
        {
            $review->setAuthor($user);
            $review->setArtwork($artwork);

            $manager->persist($review);    
            $manager->flush();

            $this->addFlash(
                'success',
                "Thank you for this feedback !"
            );

            return $this->redirectToRoute('artworks_index', []);
        }

        return $this->render('artworks/review.html.twig', [
            'myForm' => $form->createView(),
            'artwork'=> $artwork,
        ]);
    }
}
