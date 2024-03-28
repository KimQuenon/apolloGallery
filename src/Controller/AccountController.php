<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\AccountModifyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

class AccountController extends AbstractController
{
    #[Route('/login', name: 'account_login')]
    public function index(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError(); //récupérer la dernière erreur engendrée
        $username = $utils->getLastUsername(); //récupérer le dernier username à s'être connecté

        $loginError = null; //init la var pour toomanyattempts

        if($error instanceof TooManyLoginAttemptsAuthenticationException)
        {
            //l'erreur est due à la limitation de tentative de connexion
            $loginError= "Trop de tentatives de connexion, réessayez plus tard...";

        }

        return $this->render('account/index.html.twig', [
            'hasError' => $error !== null, //pas nul => afficher erreur
            'username'=> $username,
            'loginError'=> $loginError
        ]);
    }

    #[Route("/register", name:"account_register")]
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid())
        {
            $hash = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();


            return $this->redirectToRoute('account_login');
        }

        return $this->render("account/registration.html.twig",[
            'myForm'=>$form->createView()
        ]);
    }

    #[Route("/account/edit", name:"account_edit")]
    public function edit(Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser(); //récupère l'user connecté
        $form = $this->createForm(AccountModifyType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
            'success',
            'Les données ont été enregistrées avec succès'    
            );
        }

        return $this->render("account/edit.html.twig",[
            'myForm'=>$form->createView()
        ]);
    }

    #[Route('/logout', name: 'account_logout')]
    public function logout(): Void
    {

    }

    #[Route("/account/profile/", name:"account_profile")]
    public function profile(): Response
    {
        $user = $this->getUser();
        return $this->render("account/profile.html.twig",[
            'user'=>$user
        ]);
    }

    #[Route("/account/artworks", name:"account_artworks")]
    public function displayArtworks()
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté
        $artworks = $user->getArtworks(); // Récupérer les oeuvres liées à l'utilisateur

        return $this->render('account/artworks.html.twig', [
            'artworks' => $artworks,
        ]);
    }
}
