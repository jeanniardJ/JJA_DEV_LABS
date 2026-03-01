<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Entity\AppointmentAvailability;
use App\Form\AppointmentAvailabilityType;
use App\Repository\AppointmentRepository;
use App\Repository\AppointmentAvailabilityRepository;
use App\Repository\LeadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/appointments')]
#[IsGranted('ROLE_ADMIN')]
class AppointmentController extends AbstractController
{
    #[Route('', name: 'admin_appointment_index')]
    public function index(AppointmentRepository $repository): Response
    {
        $appointments = $repository->findBy([], ['startsAt' => 'DESC']);

        $upcoming = array_filter($appointments, fn(Appointment $a) => $a->getStartsAt() >= new \DateTimeImmutable('today'));
        $past = array_filter($appointments, fn(Appointment $a) => $a->getStartsAt() < new \DateTimeImmutable('today'));

        return $this->render('admin/appointments/index.html.twig', [
            'upcoming' => $upcoming,
            'past' => $past,
            'total' => count($appointments),
        ]);
    }

    #[Route('/settings', name: 'admin_appointment_settings', methods: ['GET', 'POST'])]
    public function settings(Request $request, AppointmentAvailabilityRepository $availabilityRepository, EntityManagerInterface $em): Response
    {
        $availabilities = $availabilityRepository->findBy([], ['dayOfWeek' => 'ASC', 'startTime' => 'ASC']);
        
        $newAvailability = new AppointmentAvailability();
        $form = $this->createForm(AppointmentAvailabilityType::class, $newAvailability);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($newAvailability);
            $em->flush();
            $this->addFlash('success', 'Plage horaire ajoutée.');
            return $this->redirectToRoute('admin_appointment_settings');
        }

        return $this->render('admin/appointments/settings.html.twig', [
            'availabilities' => $availabilities,
            'form' => $form,
        ]);
    }

    #[Route('/settings/delete/{id}', name: 'admin_appointment_settings_delete', methods: ['POST'])]
    public function deleteAvailability(Request $request, AppointmentAvailability $availability, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $availability->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($availability);
            $em->flush();
            $this->addFlash('success', 'Plage horaire supprimée.');
        }

        return $this->redirectToRoute('admin_appointment_settings');
    }

    #[Route('/{id}', name: 'admin_appointment_show', methods: ['GET'])]
    public function show(Appointment $appointment): Response
    {
        return $this->render('admin/appointments/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    #[Route('/{id}/status', name: 'admin_appointment_status', methods: ['POST'])]
    public function updateStatus(Request $request, Appointment $appointment, EntityManagerInterface $em): Response
    {
        $status = $request->getPayload()->getString('status');
        $allowed = ['pending', 'confirmed', 'cancelled', 'completed'];

        if (in_array($status, $allowed, true) && $this->isCsrfTokenValid('status' . $appointment->getId(), $request->getPayload()->getString('_token'))) {
            $appointment->setStatus($status);
            $em->flush();
            $this->addFlash('success', 'Statut mis à jour : ' . $status);
        }

        return $this->redirectToRoute('admin_appointment_index');
    }

    #[Route('/{id}/delete', name: 'admin_appointment_delete', methods: ['POST'])]
    public function delete(Request $request, Appointment $appointment, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $appointment->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($appointment);
            $em->flush();
            $this->addFlash('success', 'Rendez-vous supprimé.');
        }

        return $this->redirectToRoute('admin_appointment_index');
    }
}
