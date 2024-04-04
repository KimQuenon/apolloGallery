<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\DeleteType;
use App\Entity\AvatarModify;
use App\Entity\PasswordModify;
use App\Form\AvatarModifyType;
use App\Form\RegistrationType;
use App\Form\AccountModifyType;
use App\Form\PasswordModifyType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
    
    #[Route('/logout', name: 'account_logout')]
    public function logout(): Void
    {
    }

    #[Route("/register", name:"account_register")]
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid())
        {
            //gestion de l'image
            $file = $form['picture']->getData(); //recup données dans le form

            //si champs rempli
            if(!empty($file))
            {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); //recup nom du fichier sans l'extension
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename); //enlève les caractères spéciaux
                $newFilename = $safeFilename."-".uniqid().'.'.$file->guessExtension(); //nom unique
                try{
                    $file->move(
                        $this->getParameter('uploads_directory'), //déplacement du fichier
                        $newFilename
                    );
                }catch(FileException $e){
                    return $e->getMessage();
                }
                $user->setPicture($newFilename);
            }

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
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser(); //récupère l'user connecté

        $filename = $user->getPicture();
        if(!empty($filename)){
            $user->setPicture(
                new File($this->getParameter('uploads_directory').'/'.$user->getPicture())
            );
        }
        
        $form = $this->createForm(AccountModifyType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $user->setSlug('')
            ->setPicture($filename);

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

    #[Route("/account/delete", name: "account_delete")]
    #[IsGranted('ROLE_USER')]
    public function deleteAccount(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $manager, TokenStorageInterface $tokenStorage): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(DeleteType::class);

        //si l'user n'est pas connecté, renvoi vers connexion
        if (!$user) {

            $this->addFlash(
                'danger',
                'Connectez-vous à votre compte avant de le supprimer.'
            );
            return $this->redirectToRoute('account_login');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $submittedEmail = $data['email'];
            $submittedPassword = $data['password'];

            //verif si email est dans bdd
            if ($user->getEmail() === $submittedEmail) {
                $isPasswordValid = $hasher->isPasswordValid($user, $submittedPassword);

                //verif mdp
                if ($isPasswordValid) {

                    $avatarFilename = $user->getPicture();
                    if ($avatarFilename) {
                        $avatarFilePath = $this->getParameter('uploads_directory') . '/' . $avatarFilename;
                        if (file_exists($avatarFilePath)) {
                            unlink($avatarFilePath);
                        }
                    }

                    //forcer la déconnexion
                    $tokenStorage->setToken(null);
                    //remove si tout est ok
                    $manager->remove($user);
                    $manager->flush();

                    $this->addFlash(
                        'success',
                        'Votre compte a été supprimé avec succès.'
                    );

                    return $this->redirectToRoute('homepage');
                }
            }

            $this->addFlash(
                'danger',
                'L\'adresse e-mail ou le mot de passe est incorrect.'
            );
        }

        return $this->render('account/delete.html.twig', [
            'myForm' => $form->createView()
        ]);
    }

    #[Route("/account/password-modify", name:"account_password")]
    #[IsGranted('ROLE_USER')]
    public function modifyPassword(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher):Response
    {
        $passwordModify = new PasswordModify();
        $user = $this->getUser();
        $form = $this->createForm(PasswordModifyType::class, $passwordModify);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //verif mdp bdd & mdp du form
            if (!password_verify($passwordModify->getOldPassword(), $user->getPassword())) {
                $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez renseigné n'est pas votre mot de passe actuel"));
            }else{
                $newPassword = $passwordModify->getNewPassword();

                // verif si new mdp == old mdp
                if ($newPassword === $passwordModify->getOldPassword()) {
                    $form->get('newPassword')->addError(new FormError("Le nouveau mot de passe ne peut être identique à l'ancien mot de passe"));
                } else {
                    $hash = $hasher->hashPassword($user, $newPassword);

                    $user->setPassword($hash);
                    $manager->persist($user);
                    $manager->flush();

                    $this->addFlash(
                        'success',
                        'Le nouveau mot de passe a été modifié avec succès'
                    );

                    return $this->redirectToRoute('homepage');
                }
            }
        }

        return $this->render("account/password.html.twig",[
            'myForm' => $form->createView()
        ]);
    }

    #[Route("account/avatar-modify", name:"account_avatar")]
    #[IsGranted('ROLE_USER')]
    public function avatarModify(Request $request, EntityManagerInterface $manager):Response
    {
        $avatarModify = new AvatarModify();
        $user = $this->getUser();
        $form = $this->createForm(AvatarModifyType::class, $avatarModify);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {

            if(!empty($user->getPicture()))
            {
                unlink($this->getParameter('uploads_directory').'/'.$user->getPicture());
            }

            //gestion de l'image
            $file = $form['newPicture']->getData();
            if(!empty($file))
            {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename); //enlève les caractères spéciaux
                $newFilename = $safeFilename."-".uniqid().'.'.$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                }catch(FileException $e){
                    return $e->getMessage();
                }
                $user->setPicture($newFilename);
            }
            $manager->persist($user);
            $manager->flush();


            $this->addFlash(
                'success',
                'Votre avatar a été modifié avec succès'    
            );

            return $this->redirectToRoute('account_profile');


        }

        return $this->render("account/avatar.html.twig",[
            'myForm'=>$form->createView()
        ]);
    }

    #[Route("account/avatar-delete", name:"account_avatar_delete")]
    #[IsGranted('ROLE_USER')]
    public function deleteAvatar(EntityManagerInterface $manager):Response
    {
        $user = $this->getUser(); //recup user
        if(!empty($user->getPicture())) //si champs rempli
        {
            unlink($this->getParameter('uploads_directory').'/'.$user->getPicture()); //supp du dossier
            $user->setPicture(''); //img vide
            $manager->persist($user); //save bdd
            $manager->flush();
            $this->addFlash(
                'success',
                'Votre avatar a bien été supprimé'
            );
        }
        return $this->redirectToRoute('account_profile');
    }
}
