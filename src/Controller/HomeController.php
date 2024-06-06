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
    /**
     * Homepage
     *
     * @param ArtworkRepository $artworkRepo
     * @param FaqRepository $faqRepo
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
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
        //handle form
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($contact);
            $manager->flush();

            $form = $this->createForm(ContactType::class, new Contact());//clear form with a new one

            $this->addFlash(
                'success',
                'Thank you for your request ! We will get back to you as soon as possible !'    
            );
            return new RedirectResponse($this->generateUrl('homepage').'#slide-contact');
            $this->redirect($this->generateUrl('homepage') . '#slide-contact');
        }
        return $this->render('home.html.twig', [
            'recentArtworks' => $recentArtworks,
            'faqs' => $faqs,
            'myForm' => $form->createView()
        ]);
    }

    /**
     * Legal mentions
     *
     * @return void
     */
    #[Route('/private-policy', name: 'private_policy')]
    public function policy()
    {
        return $this->render('policy.html.twig', [

        ]);
    }

}
