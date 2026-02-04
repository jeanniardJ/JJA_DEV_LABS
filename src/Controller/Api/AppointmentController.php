<?php

namespace App\Controller\Api;

use App\Entity\Appointment;
use App\Entity\Lead;
use App\Form\AppointmentBookingType;
use App\Repository\LeadRepository;
use App\Repository\AppointmentRepository;
use App\Service\AppointmentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/appointments')]
class AppointmentController extends AbstractController
{
    public function __construct(
        private AppointmentService $appointmentService,
        private LeadRepository $leadRepository,
        private AppointmentRepository $appointmentRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/slots', name: 'api_appointments_slots', methods: ['GET'])]
    public function getSlots(Request $request): JsonResponse
    {
        $dateStr = $request->query->get('date');
        try {
            $date = new \DateTimeImmutable($dateStr ?: 'today');
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Invalid date format'], 400);
        }

        $slots = $this->appointmentService->getAvailableSlots($date);

        return new JsonResponse($slots);
    }

    #[Route('/book', name: 'api_appointments_book', methods: ['POST'])]
    public function book(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $form = $this->createForm(AppointmentBookingType::class);
        $form->submit($data);

        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
            return new JsonResponse(['error' => implode(' ', $errors)], 400);
        }

        $formData = $form->getData();
        $name = $formData['name'];
        $email = $formData['email'];
        $datetimeStr = $form->get('datetime')->getData();

        try {
            $datetime = new \DateTimeImmutable($datetimeStr);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Invalid datetime format'], 400);
        }

        // Check if already booked
        $existing = $this->appointmentRepository->findOneBy(['startsAt' => $datetime]);
        if ($existing) {
            return new JsonResponse(['error' => 'Slot already booked'], 409);
        }

        // Find or create Lead
        $lead = $this->leadRepository->findOneBy(['email' => $email]);
        if (!$lead) {
            $lead = new Lead();
            $lead->setEmail($email);
            $lead->setName($name);
            $lead->setSubject('Prise de rendez-vous');
            $lead->setMessage('Rendez-vous réservé via le calendrier.');
            $this->entityManager->persist($lead);
        }

        $appointment = new Appointment();
        $appointment->setStartsAt($datetime);
        $appointment->setEndsAt($datetime->modify('+15 minutes'));
        $appointment->setStatus('confirmed');
        $appointment->setLead($lead);

        $this->entityManager->persist($appointment);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Rendez-vous confirmé']);
    }
}