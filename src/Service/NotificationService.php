<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function createNotification(User $user, string $messageKey, array $params = [], ?string $link = null): Notification
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setMessageKey($messageKey);
        $notification->setParams($params);
        $notification->setLink($link);

        $this->em->persist($notification);
        
        return $notification;
    }
}
