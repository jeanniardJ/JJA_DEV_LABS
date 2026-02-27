<?php

namespace App\Controller\Api\Admin;

use App\Entity\Lead;
use App\Enum\LeadStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/leads')]
#[IsGranted('ROLE_ADMIN')]
class LeadApiController extends AbstractController
{
    #[Route('/{id}/status', name: 'api_admin_lead_update_status', methods: ['PATCH'])]
    public function updateStatus(Lead $lead, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $statusValue = $data['status'] ?? null;

        if (!$statusValue) {
            return new JsonResponse(['error' => 'Status manquant.'], 400);
        }

        $status = LeadStatus::tryFrom($statusValue);
        if (!$status) {
            return new JsonResponse(['error' => 'Status invalide.'], 400);
        }

        $lead->setStatus($status);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Status mis à jour avec succès.']);
    }
}
