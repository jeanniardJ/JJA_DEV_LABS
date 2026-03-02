<?php

namespace App\Service;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

class AdminNotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function notify(string $title, string $message, string $type = 'info', ?string $link = null): void
    {
        $notification = new Notification();
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->setType($type);
        $notification->setLink($link);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    public function success(string $title, string $message, ?string $link = null): void
    {
        $this->notify($title, $message, 'success', $link);
    }

    public function warning(string $title, string $message, ?string $link = null): void
    {
        $this->notify($title, $message, 'warning', $link);
    }

    public function danger(string $title, string $message, ?string $link = null): void
    {
        $this->notify($title, $message, 'danger', $link);
    }
}
