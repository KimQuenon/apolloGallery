<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Service\PaginationService;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminContactController extends AbstractController
{
    /**
     * Display msg from the contact form
     *
     * @param PaginationService $pagination
     * @param integer $page
     * @return Response
     */
    #[Route('/admin/contacts/{page<\d+>?1}', name: 'admin_contacts_index')]
    public function contact(PaginationService $pagination, int $page): Response
    {
        $pagination->setEntityClass(Contact::class)
        ->setPage($page)
        ->setLimit(10);

        return $this->render('admin/contacts/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Show message
     *
     * @param Contact $contact
     * @return Response
     */
    #[Route("/admin/contact/{id}", name: "admin_contact_show")]
    public function show(#[MapEntity(mapping: ['id' => 'id'])] Contact $contact): Response
    {
        return $this->render("admin/contacts/show.html.twig", [
            'contact' => $contact,
        ]);
    }

    /**
     * Delete message
     *
     * @param Contact $contact
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("contact/{id}/delete", name:"admin_contact_delete")]
    public function deleteContact(#[MapEntity(mapping: ['id' => 'id'])] Contact $contact, EntityManagerInterface $manager): Response
    {
            $manager->remove($contact);
            $manager->flush();

            $this->addFlash(
                'success',
                "Message <strong>".$contact->getfirstName()." ".$contact->getLastName()."</strong> deleted successfully !"
            );

        return $this->redirectToRoute('admin_contacts_index');
    }
}
