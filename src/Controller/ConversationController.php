<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
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
        $conversations = $convRepo->sortConvByRecentMsg($user);

        return $this->render('profile/conversations/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    #[Route('/account/conversations/{id}', name: 'conversation_show')]
    public function show(#[MapEntity(mapping: ['id' => 'id'])] Conversation $conversation, ConversationRepository $convRepo): Response
    {
        $conversation = $convRepo->findOneById($conversation);
        $messages = $conversation->getMessagesSorted();

        if (!$conversation) {
            throw $this->createNotFoundException('La conversation n\'existe pas');
        }

        

        return $this->render('profile/conversations/show.html.twig', [
            'conversation' => $conversation,
            'messages' => $messages
        ]);
    }
}
