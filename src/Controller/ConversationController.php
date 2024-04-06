<?php

namespace App\Controller;

use App\Repository\ConversationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConversationController extends AbstractController
{
    #[Route('/account/conversations', name: 'account_conversation')]
    public function index(ConversationRepository $convRepo): Response
    {
        $user = $this->getUser();
        $conversations = $convRepo->sortConvByRecentMsg($user);

        return $this->render('profile/conversations/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }
}
