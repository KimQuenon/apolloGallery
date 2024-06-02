<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AboutController extends AbstractController
{
    /**
     * About page + Experts display
     *
     * @param UserRepository $userRepo
     * @return Response
     */
    #[Route('/about', name: 'about')]
    public function index(UserRepository $userRepo): Response
    {
        $experts = $userRepo->findAll();

        return $this->render('about.html.twig', [
            'experts' => $experts,
        ]);
    }
}
