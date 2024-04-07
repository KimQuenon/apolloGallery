<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Entity\Conversation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConversationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
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
}    