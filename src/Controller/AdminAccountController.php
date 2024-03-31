<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ArtworkRepository;
use App\Repository\ContactRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

class AdminAccountController extends AbstractController
{
    #[Route('/admin/login', name: 'admin_account_login')]
    public function index(AuthenticationUtils $utils): Response
    {

        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        $loginError = null;

        if($error instanceof TooManyLoginAttemptsAuthenticationException) 
        {
            $loginError = "Trop de tentatives de connexion. Réessayez plus tard";
        }

        return $this->render('admin/account/login.html.twig', [
            'hasError' => $error !== null,
            'username' => $username,
            'loginError' => $loginError
        ]);
    }

    /**
     * Permet de se déconnecter
     *
     * @return void
     */
    #[Route("/admin/logout", name: "admin_account_logout")]
    public function logout(): void
    {

    }
    
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(ArtworkRepository $artworkRepo, UserRepository $userRepo, ContactRepository $contactRepo): Response
    {
        
        return $this->render('admin/dashboard.html.twig', [
            'artworks' => $artworkRepo->findAll(),
            'users' => $userRepo->findAll(),
            'contacts' => $contactRepo->findAll()
        ]);
    }
}
