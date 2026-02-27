<?php

namespace App\Service;

use App\Entity\PushSubscription;
use App\Repository\PushSubscriptionRepository;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class NotificationService
{
    public function __construct(
        private readonly PushSubscriptionRepository $repository,
        private readonly string $vapidPublicKey,
        private readonly string $vapidPrivateKey
    ) {
    }

    public function notifyAdmins(string $title, string $body, string $url = '/admin/dashboard'): void
    {
        $auth = [
            'VAPID' => [
                'subject' => 'mailto:noreply@jjadevlab.com',
                'publicKey' => $this->vapidPublicKey,
                'privateKey' => $this->vapidPrivateKey,
            ],
        ];

        $webPush = new WebPush($auth);
        $subscriptions = $this->repository->findAll();

        foreach ($subscriptions as $pushSub) {
            $subscription = Subscription::create([
                'endpoint' => $pushSub->getEndpoint(),
                'publicKey' => $pushSub->getP256dh(),
                'authToken' => $pushSub->getAuth(),
            ]);

            $webPush->queueNotification(
                $subscription,
                json_encode([
                    'title' => $title,
                    'body' => $body,
                    'url' => $url,
                ])
            );
        }

        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                // TODO: Handle expired subscriptions (delete from DB)
                // $endpoint = $report->getEndpoint();
            }
        }
    }
}
