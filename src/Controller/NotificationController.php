<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notifications')]
#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    public function dropdown(NotificationRepository $notificationRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return new Response('');
        }

        $notifications = $notificationRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC'],
            5
        );

        $unreadCount = $notificationRepository->count([
            'user' => $user,
            'isRead' => false
        ]);

        return $this->render('notification/_dropdown.html.twig', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    #[Route('/{id}/lire', name: 'app_notification_read', methods: ['GET'])]
    public function read(Notification $notification, EntityManagerInterface $em): Response
    {
        if ($notification->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if (!$notification->isRead()) {
            $notification->setIsRead(true);
            $em->flush();
        }

        if ($notification->getLink()) {
            return $this->redirect($notification->getLink());
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/tout-lire', name: 'app_notification_read_all', methods: ['POST'])]
    public function readAll(NotificationRepository $notificationRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $unreadNotifications = $notificationRepository->findBy([
            'user' => $user,
            'isRead' => false
        ]);

        foreach ($unreadNotifications as $notification) {
            $notification->setIsRead(true);
        }

        if (count($unreadNotifications) > 0) {
            $em->flush();
        }

        return $this->redirect($this->generateUrl('app_home'));
    }
}
