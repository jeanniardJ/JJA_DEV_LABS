<?php

namespace App\EventSubscriber;

use App\Entity\Lead;
use App\Message\Admin\PushNotificationMessage;
use App\Service\AdminNotificationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Lead::class)]
class LeadNotificationSubscriber
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly UrlGeneratorInterface $router,
        private readonly AdminNotificationService $adminNotificationService
    ) {
    }

    public function postPersist(Lead $lead): void
    {
        $title = "🚀 Nouveau Lead !";
        $body = sprintf("%s via %s", $lead->getName(), $lead->getSubject());
        
        $url = $this->router->generate('admin_dashboard', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // 1. Notification Push PWA (Asynchrone)
        $this->bus->dispatch(new PushNotificationMessage($title, $body, $url));

        // 2. Notification Interne (Base de données)
        $this->adminNotificationService->success(
            $title,
            $body,
            $this->router->generate('admin_dashboard')
        );
    }
}
