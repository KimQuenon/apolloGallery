<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Message;
use App\Form\MessageType;
use App\Entity\Conversation;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConversationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConversationController extends AbstractController
{
    /**
     * Display all conversations by expert
     *
     * @param ConversationRepository $convRepo
     * @return Response
     */
    #[Route('/account/conversations', name: 'account_conversations')]
    #[IsGranted('ROLE_USER')]
    public function index(ConversationRepository $convRepo): Response
    {
        $user = $this->getUser();

        //delayed by roles
        if ($this->isGranted('ROLE_EXPERT')) {
            $conversations = $convRepo->findConversationsByExpert($user);
        } else {
            $conversations = $convRepo->sortConvByRecentMsg($user);
        }

        return $this->render('profile/conversations/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }


    /**
     * Display single conversation
     *
     * @param string $slug
     * @param ConversationRepository $convRepo
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/account/conversations/{slug}', name: 'conversation_show')]
    #[IsGranted('ROLE_USER')]
    public function show(string $slug, ConversationRepository $convRepo, Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $conversation = $convRepo->findConversationBySlugAndUser($slug, $user);
        
        //conversation not found
        if (!$conversation) {
            throw $this->createNotFoundException("Conversation not found.");
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
    
        //handle form
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $newMessage->setConversation($conversation);
            $newMessage->setSendBy($user);
    
            $manager->persist($newMessage);
            $manager->flush();
    
            $this->addFlash(
                'success',
                "Message sent !"
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

    /**
     * UCreate conversation with expert
     *
     * @param string $expertSlug
     * @param UserRepository $userRepo
     * @param ConversationRepository $convRepo
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse
     */
    #[Route('/account/conversations/create/{expertSlug}', name: 'create_conversation')]
    #[IsGranted('ROLE_USER')]
    public function createConversationWithExpert(string $expertSlug, UserRepository $userRepo, ConversationRepository $convRepo, EntityManagerInterface $entityManager): RedirectResponse
    {
        $user = $this->getUser();
        //get expert' slug
        $expert = $userRepo->findOneBy(['slug' => $expertSlug]);

        //expert not found
        if (!$expert) {
            $this->addFlash('danger', "This expert doesn't exist... yet ?");
            return $this->redirectToRoute('about');
        }

        // check if the contacted person === role_expert
        if (!in_array('ROLE_EXPERT', $expert->getRoles())) {
            $this->addFlash('warning', "You are not allowed to talk with this user.");
            return $this->redirectToRoute('about');
        }
    
        //if a conversation exists with this expert -> redirect to that one
        $existingConversation = $convRepo->findOneBy(['user' => $user, 'expert' => $expert]);

        if ($existingConversation) {
            $this->addFlash('warning', "You have already a conversation going on with this user.");
            return $this->redirectToRoute('conversation_show', ['slug' => $expertSlug]);
        }

        // create a conversation + set both contributors
        $conversation = new Conversation();
        $conversation->setUser($user);
        $conversation->setExpert($expert);

        $entityManager->persist($conversation);
        $entityManager->flush();

        $this->addFlash('success', "New conversation created ! Don't forget to say hi !");

        return $this->redirectToRoute('conversation_show', ['slug' => $expertSlug]);
    }

}    