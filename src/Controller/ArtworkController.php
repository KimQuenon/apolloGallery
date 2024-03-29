<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Entity\Movement;
use App\Form\ArtworkType;
use App\Repository\ArtworkRepository;
use App\Repository\MovementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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

    
    #[Route("/artworks/new", name:"artworks_create")]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $artwork = new Artwork();
        $form = $this->createform(ArtworkType::class, $artwork);

        //traitement des données - associations aux champs respectifs - validation
        $form->handleRequest($request);

        //form complet et valid -> envoi bdd + message et redirection
        if($form->isSubmitted() && $form->IsValid())
        {
            $artwork->setAuthor($this->getUser());

            $submissionDate = new \DateTime();
            $artwork->setSubmissionDate($submissionDate);

            $manager->persist($artwork);
            
            foreach ($artwork->getMovements() as $movement)
            {
                $movement->addArtwork($artwork);
                $manager->persist($artwork);
            }
            
            $manager->flush();

            $this->addFlash(
                'success',
                "La fiche de <strong>".$artwork->getTitle()."</strong> a bien été enregistrée."
            );
        
            return $this->redirectToRoute('artworks_show', [
                'slug'=> $artwork->getSlug()
            ]);
        }

        return $this->render("artworks/new.html.twig",[
            'myForm' => $form->createView(),
            
        ]);

    }

    #[Route("artworks/{slug}/delete", name:"artworks_delete")]
    public function deleteArtworks(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, EntityManagerInterface $manager): Response
    {
            $manager->remove($artwork);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>".$artwork->getTitle()."</strong> a bien été supprimée!"
            );

        return $this->redirectToRoute('account_artworks');
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

        $movements = $artwork->getMovements();
        $author = $artwork->getAuthor();
        $currentDate = new \DateTime();

        return $this->render("artworks/show.html.twig", [
            'artwork' => $artwork,
            'movements' => $movements,
            'author' => $author,
            'currentDate' => $currentDate
        ]);
    }

    #[Route("artworks/{slug}/edit", name:"artworks_edit")]
    public function edit(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(ArtworkType::class, $artwork);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($artwork);
            
            foreach ($artwork->getMovements() as $movement)
            {
                $movement->addArtwork($artwork);
                $manager->persist($artwork);
            }

              $manager->flush();

              $this->addFlash(
                'success',
                "L'oeuvre <strong>".$artwork->getTitle()."</strong> a bien été modifiée!"
              );

              return $this->redirectToRoute('artworks_show',[
                'slug' => $artwork->getSlug()
              ]);

        }



        return $this->render("artworks/edit.html.twig",[
            "artwork"=> $artwork,
            "myForm"=> $form->createView()
        ]);
    }

}
