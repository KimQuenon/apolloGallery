<?php

namespace App\Controller;

use App\Repository\FaqRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(FaqRepository $faqRepo): Response
    {
        $faqs = $faqRepo->findAll();
        return $this->render('home.html.twig', [
            'faqs' => $faqs,
        ]);
    }
}
