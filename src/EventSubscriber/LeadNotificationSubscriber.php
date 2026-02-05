<?php

namespace App\EventSubscriber;

use App\Entity\Lead;
use App\Message\Admin\PushNotificationMessage;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Lead::class)]
class LeadNotificationSubscriber
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly UrlGeneratorInterface $router
    ) {
    }

    public function postPersist(Lead $lead): void
    {
        $title = "🚀 Nouveau Lead !";
        $body = sprintf("%s via %s", $lead->getName(), $lead->getSubject());
        
        // Generate absolute URL for the admin
        $url = $this->router->generate('admin_dashboard', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->bus->dispatch(new PushNotificationMessage($title, $body, $url));
    }
}
