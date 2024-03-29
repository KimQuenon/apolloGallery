<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\FaqRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(FaqRepository $faqRepo, Request $request, EntityManagerInterface $manager): Response
    {
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
        }
        return $this->render('home.html.twig', [
            'faqs' => $faqs,
            'myForm' => $form->createView()
        ]);
    }
}
