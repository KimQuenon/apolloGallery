<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountModifyType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users_index')]
    public function index(UserRepository $userRepo): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $userRepo->findAll(),
        ]);
    }

    // #[Route("/admin/users/{id}/edit", name: "admin_users_edit")]
    // public function edit(#[MapEntity(mapping: ['id' => 'id'])] User $user, Request $request, EntityManagerInterface $manager): Response
    // {
    //     $form = $this->createForm(AccountModifyType::class, $user);
    //     $form->handleRequest($request);

    //     if($form->isSubmitted() && $form->isValid())
    //     {
    //         $manager->persist($user);
    //         $manager->flush();

    //         $this->addFlash(
    //             'success',
    //             "Le profil n° <strong>".$user->getId()."</strong> a bien été modifié."
    //         );

    //     }

    //     return $this->render("admin/users/edit.html.twig",[
    //         "user" => $user,
    //         "myForm" => $form->createView()
    //     ]);

    // }

    #[Route("/admin/users/{id}/delete", name: "admin_users_delete")]
    public function delete(#[MapEntity(mapping: ['id' => 'id'])] User $user, EntityManagerInterface $manager): Response
    {
        $manager->remove($user);
        $manager->flush();

        $this->addFlash(
            'success',
            "Le profil n° <strong>".$user->getId()."</strong> a bien été supprimé!"
        );

        return $this->redirectToRoute('admin_users_index');
    }
}
