<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Form\ArtworkType;
use App\Repository\ArtworkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminArtworkController extends AbstractController
{
    #[Route('/admin/artworks', name: 'admin_artworks_index')]
    public function index(ArtworkRepository $repo): Response
    {
        return $this->render('admin/artworks/index.html.twig', [
            'artworks' => $repo->findAll(),
        ]);
    }

    #[Route("/admin/artworks/{slug}/edit", name: "admin_artworks_edit")]
    public function edit(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(ArtworkType::class, $artwork);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($artwork);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>".$artwork->getTitle()."</strong> a bien été modifiée"
            );

        }

        return $this->render("admin/artworks/edit.html.twig",[
            "artwork" => $artwork,
            "myForm" => $form->createView()
        ]);

    }

    #[Route("/admin/artworks/{slug}/delete", name: "admin_artworks_delete")]
    public function delete(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, EntityManagerInterface $manager): Response
    {
        // // on en peut pas supprimer une annonce qui possède des enchères
        // if(count($ad->getBookings()) > 0)
        // {
        //     $this->addFlash(
        //         'warning',
        //         "Vous ne pouvez pas supprimer l'annonce <strong>".$ad->getTitle()."</strong> car elle possède des réservations"
        //     );
        // }else{
        //     $this->addFlash(
        //         "success",
        //         "L'annonce <strong>".$ad->getTitle()."</strong> a bien été supprimée"
        //     );
        //     $manager->remove($ad);
        //     $manager->flush();
        // }

        $manager->remove($artwork);
        $manager->flush();

        $this->addFlash(
            'success',
            "L'annonce <strong>".$artwork->getTitle()."</strong> a bien été supprimée!"
        );

        return $this->redirectToRoute('admin_artworks_index');
    }


}
