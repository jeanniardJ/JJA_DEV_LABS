<?php

namespace App\Controller\Api\Admin;

use App\Entity\Lead;
use App\Enum\LeadStatus;
use App\Service\EmailService;
use App\Service\SystemLogService;
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
    #[Route('/{id}/status', name: 'api_admin_lead_update_status', methods: ['POST'])]
    public function updateStatus(
        Lead $lead, 
        Request $request, 
        EntityManagerInterface $em, 
        SystemLogService $logService,
        EmailService $emailService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $statusValue = $data['status'] ?? null;

        if (!$statusValue) {
            return new JsonResponse(['error' => 'Status missing'], 400);
        }

        try {
            $oldStatus = $lead->getStatus()->value;
            $newStatus = LeadStatus::from($statusValue);
            $lead->setStatus($newStatus);

            // LOGIQUE AUTOMATIQUE DES RENDEZ-VOUS
            foreach ($lead->getAppointments() as $appointment) {
                if ($appointment->getStatus() === 'pending') {
                    if ($newStatus === LeadStatus::APPOINTMENT_CONFIRMED) {
                        $appointment->setStatus('confirmed');
                        $emailService->sendAppointmentConfirmed($appointment);
                        $logService->success("Rendez-vous confirmé automatiquement pour " . $lead->getEmail(), "APPOINTMENT");
                    } elseif ($newStatus === LeadStatus::APPOINTMENT_REFUSED) {
                        $appointment->setStatus('refused');
                        $emailService->sendAppointmentRefused($appointment);
                        $logService->warning("Rendez-vous refusé automatiquement pour " . $lead->getEmail(), "APPOINTMENT");
                    }
                }
            }

            $em->flush();

            $logService->info(
                sprintf("Pipeline : Lead %s déplacé de %s vers %s", $lead->getEmail(), $oldStatus, $newStatus->value),
                "KANBAN"
            );

            return new JsonResponse(['status' => 'success']);
        } catch (\ValueError $e) {
            return new JsonResponse(['error' => 'Invalid status'], 400);
        }
    }
}
