<?php

namespace App\Controller\Api\Admin;

use App\Entity\PushSubscription;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/push-subscription')]
#[IsGranted('ROLE_ADMIN')]
class PushSubscriptionController extends AbstractController
{
    #[Route('', name: 'api_admin_push_subscribe', methods: ['POST'])]
    public function subscribe(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['endpoint'])) {
            return new JsonResponse(['error' => 'Données d\'abonnement invalides.'], 400);
        }

        /** @var User $user */
        $user = $this->getUser();

        // Check if already exists
        $existing = $entityManager->getRepository(PushSubscription::class)->findOneBy([
            'endpoint' => $data['endpoint']
        ]);

        if (!$existing) {
            $subscription = new PushSubscription();
            $subscription->setEndpoint($data['endpoint']);
            $subscription->setP256dh($data['keys']['p256dh'] ?? '');
            $subscription->setAuth($data['keys']['auth'] ?? '');
            $subscription->setAdmin($user);

            $entityManager->persist($subscription);
            $entityManager->flush();
        }

        return new JsonResponse(['message' => 'Abonnement enregistré.']);
    }
}
