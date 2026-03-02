<?php

namespace App\Controller\Api\Admin;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/notifications')]
#[IsGranted('ROLE_ADMIN')]
class NotificationApiController extends AbstractController
{
    #[Route('/read-all', name: 'api_admin_notifications_read_all', methods: ['POST'])]
    public function readAll(EntityManagerInterface $em): JsonResponse
    {
        $em->createQuery('UPDATE App\Entity\Notification n SET n.isRead = true')->execute();
        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/{id}/read', name: 'api_admin_notification_read', methods: ['POST'])]
    public function read(Notification $notification, EntityManagerInterface $em): JsonResponse
    {
        $notification->setIsRead(true);
        $em->flush();
        return new JsonResponse(['status' => 'success']);
    }
}
