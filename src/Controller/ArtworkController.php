<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Entity\Movement;
use App\Form\ArtworkType;
use App\Service\PaginationService;
use App\Repository\ArtworkRepository;
use App\Repository\MovementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArtworkController extends AbstractController
{
    #[Route('/artworks/{page<\d+>?1}', name: 'artworks_index')]
    public function index(PaginationService $pagination, int $page, MovementRepository $movementRepo): Response
    {

        $pagination->setEntityClass(Artwork::class)
        ->setPage($page)
        ->setLimit(9);

        $movements = $movementRepo->findAll();

        return $this->render('artworks/index.html.twig', [
            'pagination' => $pagination,
            'movements' => $movements,
        ]);
    }

    
    #[Route("/artworks/new", name:"artworks_create")]
    #[IsGranted('ROLE_USER')]
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

    #[Route("artworks/movements/{slug}", name: "movements_show")]
    public function showMovement(#[MapEntity(mapping: ['slug' => 'slug'])] Movement $movement, ArtworkRepository $repo): Response
    {
        $artworks = $movement->getArtwork();
        
        return $this->render('artworks/movements/show.html.twig', [
            'movement' => $movement,
            'artworks' => $artworks,
        ]);

    }

    #[Route("artworks/{slug}/delete", name:"artworks_delete")]
    #[IsGranted(
        attribute: new Expression('(user === subject and is_granted("ROLE_USER")) or is_granted("ROLE_ADMIN")'),
        subject: new Expression('args["artwork"].getAuthor()'),
        message: "Cette annonce ne vous appartient pas, vous ne pouvez pas la supprimer"
    )]
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

    

    #[Route("/artworks/{slug}", name: "artworks_show")]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, ArtworkRepository $artworkRepo): Response
    {

        $movements = $artwork->getMovements();
        $author = $artwork->getAuthor();
        $currentDate = new \DateTime();

        //recup l'auteur de l'annonce
        $seller = $artwork->getAuthor();
        //recup les autres voitures de ce même auteur et les mettre dans un tab
        $otherArtworks = $seller->getArtworks()->toArray(); 
        //évite que la voiture de l'annonce apparaisse dans les suggestions
        $otherArtworks = array_filter($otherArtworks, function ($otherArtwork) use ($artwork) {
            return $otherArtwork !== $artwork;
        });

        return $this->render("artworks/show.html.twig", [
            'artwork' => $artwork,
            'movements' => $movements,
            'author' => $author,
            'currentDate' => $currentDate,
            'seller' => $seller,
            'otherArtworks' => $otherArtworks,
        ]);
    }

    #[Route("artworks/{slug}/edit", name:"artworks_edit")]
    #[IsGranted(
        attribute: new Expression('(user === subject and is_granted("ROLE_USER")) or is_granted("ROLE_ADMIN")'),
        subject: new Expression('args["artwork"].getAuthor()'),
        message: "Cette annonce ne vous appartient pas, vous ne pouvez pas l'éditer"
    )]
    public function edit(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, Request $request, EntityManagerInterface $manager): Response
    {
        if ($artwork->getArchived()) {
            $this->addFlash('danger', 'Impossible de modifier une oeuvre archivée.');
            return $this->redirectToRoute('artworks_show', ['id' => $artwork->getSlug()]);
        }

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
