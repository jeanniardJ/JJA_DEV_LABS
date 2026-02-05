<?php

namespace App\MessageHandler\Admin;

use App\Message\Admin\PushNotificationMessage;
use App\Service\NotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PushNotificationHandler
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {
    }

    public function __invoke(PushNotificationMessage $message): void
    {
        $this->notificationService->notifyAdmins(
            $message->getTitle(),
            $message->getBody(),
            $message->getUrl()
        );
    }
}
