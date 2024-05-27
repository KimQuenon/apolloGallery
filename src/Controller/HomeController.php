<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\FaqRepository;
use App\Repository\ArtworkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(ArtworkRepository $artworkRepo, FaqRepository $faqRepo, Request $request, EntityManagerInterface $manager): Response
    {
        //latest frames
        $recentArtworks = $artworkRepo->findBy([], ['id' => 'DESC'], 3);

        //faq
        $faqs = $faqRepo->findAll();

        //contact
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($contact);
            $manager->flush();

            $form = $this->createForm(ContactType::class, new Contact());//init d'un nouveau form

            $this->addFlash(
                'success',
                'Votre message nous est bien parvenu, nous ne manquerons pas de vous recontacter dans les plus bref délais.'    
            );
            // return new RedirectResponse($this->generateUrl('homepage').'#slide-contact');
            $this->redirect($this->generateUrl('homepage') . '#slide-contact');
        }
        return $this->render('home.html.twig', [
            'recentArtworks' => $recentArtworks,
            'faqs' => $faqs,
            'myForm' => $form->createView()
        ]);
    }
}
