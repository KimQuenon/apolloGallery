<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Form\ArtworkType;
use App\Service\PaginationService;
use App\Repository\ArtworkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminArtworkController extends AbstractController
{
    #[Route('/admin/artworks/{page<\d+>?1}', name: 'admin_artworks_index')]
    public function index(PaginationService $pagination, int $page): Response
    {
        $pagination->setEntityClass(Artwork::class)
        ->setPage($page)
        ->setLimit(10);

        return $this->render('admin/artworks/index.html.twig', [
            'pagination' => $pagination
        ]);
    }



    #[Route("/admin/artworks/{slug}/edit", name: "admin_artworks_edit")]
    public function edit(#[MapEntity(mapping: ['slug' => 'slug'])] Artwork $artwork, Request $request, EntityManagerInterface $manager): Response
    {
        if ($artwork->isArchived()) {
            $this->addFlash('danger', 'Impossible de modifier une oeuvre archivée.');
            return $this->redirectToRoute('artworks_show', ['slug' => $artwork->getSlug()]);
        }

        $fileName = $artwork->getCoverImage();
        if(!empty($fileName)){
            $artwork->setCoverImage(
                new File($this->getParameter('uploads_directory').'/'.$artwork->getCoverImage())
            );
        }

        $form = $this->createForm(ArtworkType::class, $artwork, [
            'is_edit' => true
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $artwork->setCoverImage($fileName);

            $manager->persist($artwork);

            foreach ($artwork->getMovements() as $movement)
            {
                $movement->addArtwork($artwork);
                $manager->persist($artwork);
            }
            
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
        unlink($this->getParameter('uploads_directory').'/'.$artwork->getCoverImage()); 
        $manager->remove($artwork);
        $manager->flush();

        $this->addFlash(
            'success',
            "L'annonce <strong>".$artwork->getTitle()."</strong> a bien été supprimée!"
        );

        return $this->redirectToRoute('admin_artworks_index');
    }


}
