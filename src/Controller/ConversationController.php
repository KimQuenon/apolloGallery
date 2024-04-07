<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Message;
use App\Form\MessageType;
use App\Entity\Conversation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConversationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConversationController extends AbstractController
{
    #[Route('/account/conversations', name: 'account_conversations')]
    public function index(ConversationRepository $convRepo): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_EXPERT')) {
            $conversations = $convRepo->findConversationsByExpert($user);
        } else {
            $conversations = $convRepo->sortConvByRecentMsg($user);
        }

        return $this->render('profile/conversations/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    #[Route('/account/conversations/{slug}', name: 'conversation_show')]
    public function show(string $slug, ConversationRepository $convRepo, Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $conversation = $convRepo->findConversationBySlugAndUser($slug, $user);
        
        if (!$conversation) {
            throw $this->createNotFoundException('La conversation n\'existe pas');
        }
        
        $conversations = $convRepo->sortConvByRecentMsg($user);
        $messages = $conversation->getMessagesSorted();
    
        $isExpert = $this->isGranted('ROLE_EXPERT');
    
        if ($isExpert) {
            $conversations = $convRepo->findConversationsByExpert($user);
        } else {
            $conversations = $convRepo->sortConvByRecentMsg($user);
        }
    
        $newMessage = new Message();
        $form = $this->createForm(MessageType::class, $newMessage);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $newMessage->setConversation($conversation);
            $newMessage->setSendBy($user);
    
            $manager->persist($newMessage);
            $manager->flush();
    
            $this->addFlash(
                'success',
                "Message envoyé !"
            );
    
            return $this->redirectToRoute('conversation_show', [
                'slug' => $slug
            ]);
        }
    
        return $this->render('profile/conversations/show.html.twig', [
            'myForm' => $form->createView(),
            'conversation' => $conversation,
            'conversations' => $conversations,
            'messages' => $messages,
        ]);
    }

    #[Route('/account/conversations/create/{expertSlug}', name: 'create_conversation_with_expert')]
    public function createConversationWithExpert(string $expertSlug, EntityManagerInterface $entityManager): RedirectResponse
    {
        $user = $this->getUser();

        // Trouver l'expert par son slug
        $expert = $entityManager->getRepository(User::class)->findOneBy(['slug' => $expertSlug]);

        if (!$expert) {
            throw $this->createNotFoundException('Expert non trouvé.');
        }

        // Vérifier si l'utilisateur est déjà en conversation avec cet expert
        $existingConversation = $entityManager->getRepository(Conversation::class)->findOneBy(['user' => $user, 'expert' => $expert]);

        if ($existingConversation) {
            $this->addFlash('warning', 'Vous êtes déjà en conversation avec cet expert.');
            return $this->redirectToRoute('conversation_show', ['slug' => $expertSlug]);
        }

        // Créer une nouvelle conversation
        $conversation = new Conversation();
        $conversation->setUser($user);
        $conversation->setExpert($expert);

        $entityManager->persist($conversation);
        $entityManager->flush();

        $this->addFlash('success', 'Nouvelle conversation créée avec succès.');

        return $this->redirectToRoute('conversation_show', ['slug' => $expertSlug]);
    }

}    