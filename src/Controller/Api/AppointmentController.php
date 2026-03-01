<?php

namespace App\Controller\Api;

use App\Entity\Appointment;
use App\Entity\Lead;
use App\Enum\LeadStatus;
use App\Repository\LeadRepository;
use App\Repository\AppointmentRepository;
use App\Service\AppointmentService;
use App\Service\EmailService;
use App\Service\SystemLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/appointments')]
class AppointmentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LeadRepository $leadRepository,
        private AppointmentRepository $appointmentRepository,
        private AppointmentService $appointmentService,
        private SystemLogService $logService,
        private EmailService $emailService
    ) {}

    #[Route('/slots', name: 'api_appointment_slots', methods: ['GET'])]
    public function getSlots(Request $request): JsonResponse
    {
        $dateStr = $request->query->get('date');
        if (!$dateStr) {
            return new JsonResponse(['error' => 'Date manquante'], 400);
        }

        try {
            $date = new \DateTimeImmutable($dateStr);
            $slots = $this->appointmentService->getAvailableSlots($date);
            return new JsonResponse($slots);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Format de date invalide'], 400);
        }
    }

    #[Route('/book', name: 'api_appointment_book', methods: ['POST'])]
    public function book(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $email = $data['email'] ?? null;
        if (!$email) {
            return new JsonResponse(['status' => 'error', 'message' => 'Email requis'], 400);
        }

        $lead = $this->leadRepository->findOneBy(['email' => $email]);
        if (!$lead) {
            return new JsonResponse(['status' => 'error', 'message' => 'Identité non reconnue. Veuillez d\'abord remplir le formulaire de contact.'], 404);
        }

        $datetimeStr = $data['datetime'] ?? null;
        if (!$datetimeStr) {
            return new JsonResponse(['status' => 'error', 'message' => 'Créneau requis'], 400);
        }

        try {
            $startsAt = new \DateTimeImmutable($datetimeStr);
            
            // SÉCURITÉ CRITIQUE : Vérifier que le créneau est BIEN dans les disponibilités configurées
            $availableSlots = $this->appointmentService->getAvailableSlots($startsAt);
            $isValidSlot = false;
            foreach ($availableSlots as $slot) {
                if ($slot['datetime'] === $startsAt->format(\DateTimeInterface::ATOM)) {
                    $isValidSlot = true;
                    break;
                }
            }

            if (!$isValidSlot) {
                return new JsonResponse(['status' => 'error', 'message' => 'Ce créneau n\'est pas ou plus disponible.'], 400);
            }

            $endsAt = $startsAt->modify('+30 minutes');

            $appointment = new Appointment();
            $appointment->setStartsAt($startsAt);
            $appointment->setEndsAt($endsAt);
            $appointment->setStatus('pending');
            $appointment->setLead($lead);

            $lead->setStatus(LeadStatus::APPOINTMENT_PENDING);

            $this->entityManager->persist($appointment);
            $this->entityManager->flush();

            $this->emailService->sendAppointmentPending($appointment);

            $this->logService->success(
                sprintf("Demande de RDV : %s pour le %s", $lead->getEmail(), $startsAt->format('d/m/Y H:i')),
                "APPOINTMENT"
            );

            return new JsonResponse(['status' => 'success', 'message' => 'Votre demande est enregistrée. Un email de confirmation vous a été envoyé.']);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur lors de la réservation.'], 500);
        }
    }
}
