<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountModifyType;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    /**
     * Display all users
     *
     * @param PaginationService $pagination
     * @param integer $page
     * @return Response
     */
    #[Route('/admin/users/{page<\d+>?1}', name: 'admin_users_index')]
    public function index(PaginationService $pagination, int $page): Response
    {
        $pagination->setEntityClass(User::class)
        ->setPage($page)
        ->setLimit(10);

        return $this->render('admin/users/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Delete user
     *
     * @param User $user
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/admin/users/{id}/delete", name: "admin_users_delete")]
    public function delete(#[MapEntity(mapping: ['id' => 'id'])] User $user, EntityManagerInterface $manager): Response
    {
        //delete user's avatar
        unlink($this->getParameter('uploads_directory').'/'.$user->getPicture()); 
        $manager->remove($user);
        $manager->flush();

        $this->addFlash(
            'success',
            "Profl of <strong>".$user->getFullName()."</strong> deleted successfully !"
        );

        return $this->redirectToRoute('admin_users_index');
    }
}
