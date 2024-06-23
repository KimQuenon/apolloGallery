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
    /**
     * Log in Page
     *
     * @param AuthenticationUtils $utils
     * @return Response
     */
    #[Route('/login', name: 'account_login')]
    public function index(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError(); //last error
        $username = $utils->getLastUsername(); //last user connected

        $loginError = null; //var for toomanyattempts

        if($error instanceof TooManyLoginAttemptsAuthenticationException)
        {
            //limited number of login attempts
            $loginError= "Too many attemps, try again later..";

        }

        return $this->render('account/index.html.twig', [
            'hasError' => $error !== null, //if not null => display error
            'username'=> $username,
            'loginError'=> $loginError
        ]);
    }
    
    /**
     * Log out
     *
     * @return Void
     */
    #[Route('/logout', name: 'account_logout')]
    public function logout(): Void
    {
    }

    /**
     * Create an account
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route("/register", name:"account_register")]
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            //img handle
            $file = $form['picture']->getData(); //data of the pic

            //if field completed
            if(!empty($file))
            {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); //filename without extension
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename); //get rid of special caracters
                $newFilename = $safeFilename."-".uniqid().'.'.$file->guessExtension(); //unique name
                try{
                    $file->move(
                        $this->getParameter('uploads_directory'), //moving the file
                        $newFilename
                    );
                }catch(FileException $e){
                    return $e->getMessage();
                }
                $user->setPicture($newFilename);
            }

            //hash password
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
    
    /**
     * Edit profile
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/account/edit", name:"account_edit")]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser(); //get connected user

        $filename = $user->getPicture();

        //handle img
        if(!empty($filename)){
            $user->setPicture(
                new File($this->getParameter('uploads_directory').'/'.$user->getPicture())
            );
        }
        
        //edit form
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
            'Data has been saved successfully'    
            );
        }

        return $this->render("account/edit.html.twig",[
            'myForm'=>$form->createView()
        ]);
    }

    /**
     * Delete account
     *
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @param EntityManagerInterface $manager
     * @param TokenStorageInterface $tokenStorage
     * @return Response
     */
    #[Route("/account/delete", name: "account_delete")]
    #[IsGranted('ROLE_USER')]
    public function deleteAccount(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $manager, TokenStorageInterface $tokenStorage): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(DeleteType::class);

        //if user not logged in -> redirect to login page
        if (!$user) {

            $this->addFlash(
                'danger',
                'You must be logged in first.'
            );
            return $this->redirectToRoute('account_login');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $submittedEmail = $data['email'];
            $submittedPassword = $data['password'];

            //email address in db?
            if ($user->getEmail() === $submittedEmail) {
                $isPasswordValid = $hasher->isPasswordValid($user, $submittedPassword);

                //password verify
                if ($isPasswordValid) {

                    $avatarFilename = $user->getPicture();

                    //avatar deleted
                    if ($avatarFilename) {
                        $avatarFilePath = $this->getParameter('uploads_directory') . '/' . $avatarFilename;
                        if (file_exists($avatarFilePath)) {
                            unlink($avatarFilePath);
                        }
                    }

                    //set connexion token to null
                    $tokenStorage->setToken(null);
                    //remove
                    $manager->remove($user);
                    $manager->flush();

                    $this->addFlash(
                        'success',
                        'Account deleted, see you soon maybe ?'
                    );

                    return $this->redirectToRoute('homepage');
                }
            }

            $this->addFlash(
                'danger',
                'Incorrect email address and/or password.'
            );
        }

        return $this->render('account/delete.html.twig', [
            'myForm' => $form->createView()
        ]);
    }

    /**
     * Edit password
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route("/account/password-modify", name:"account_password")]
    #[IsGranted('ROLE_USER')]
    public function modifyPassword(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher):Response
    {
        $passwordModify = new PasswordModify();
        $user = $this->getUser();
        $form = $this->createForm(PasswordModifyType::class, $passwordModify);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //compare given password & db password
            if (!password_verify($passwordModify->getOldPassword(), $user->getPassword())) {
                $form->get('oldPassword')->addError(new FormError("This doesn't seem to be your current password..."));
            }else{
                $newPassword = $passwordModify->getNewPassword();

                // new password = old password?
                if ($newPassword === $passwordModify->getOldPassword()) {
                    $form->get('newPassword')->addError(new FormError("The new password can't be the old one..."));
                } else {
                    $hash = $hasher->hashPassword($user, $newPassword);

                    $user->setPassword($hash);
                    $manager->persist($user);
                    $manager->flush();

                    $this->addFlash(
                        'success',
                        'Password edited successfully !'
                    );

                    return $this->redirectToRoute('homepage');
                }
            }
        }

        return $this->render("account/password.html.twig",[
            'myForm' => $form->createView()
        ]);
    }

    /**
     * Edit avatar
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
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
            //get rid of the old avatar
            if(!empty($user->getPicture()))
            {
                unlink($this->getParameter('uploads_directory').'/'.$user->getPicture());
            }

            //handle img
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
                'Avatar edited successfully !'    
            );

            return $this->redirectToRoute('account_profile');


        }

        return $this->render("account/avatar.html.twig",[
            'myForm'=>$form->createView()
        ]);
    }

    /**
     * Delete avatar
     *
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("account/avatar-delete", name:"account_avatar_delete")]
    #[IsGranted('ROLE_USER')]
    public function deleteAvatar(EntityManagerInterface $manager):Response
    {
        $user = $this->getUser();

        //get rid of the avatar + delete it from the database
        if(!empty($user->getPicture()))
        {
            unlink($this->getParameter('uploads_directory').'/'.$user->getPicture());
            $user->setPicture(''); //this field can be null
            $manager->persist($user);
            $manager->flush();
            $this->addFlash(
                'success',
                'Avatar deleted !'
            );
        }
        return $this->redirectToRoute('account_profile');
    }
}
