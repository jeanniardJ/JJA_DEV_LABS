<?php

namespace App\Controller\Admin;

use App\Entity\Lead;
use App\Repository\ScanResultRepository;
use App\Repository\AppointmentRepository;
use App\Service\SystemLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/leads')]
#[IsGranted('ROLE_ADMIN')]
class LeadController extends AbstractController
{
    #[Route('/{id}', name: 'admin_lead_show', methods: ['GET'])]
    public function show(Lead $lead, ScanResultRepository $scanResultRepository, AppointmentRepository $appointmentRepository): Response
    {
        $events = [];

        $events[] = [
            'type' => 'creation',
            'date' => $lead->getCreatedAt(),
            'label' => 'Lead créé via ' . $lead->getSubject(),
            'icon' => 'user-plus',
            'color' => 'lab-terminal'
        ];

        if (preg_match('/Analyse de (https?:\/\/[^\s\)]+)/', $lead->getSubject(), $matches)) {
            $url = $matches[1];
            $scans = $scanResultRepository->findBy(['url' => $url], ['createdAt' => 'DESC']);
            foreach ($scans as $scan) {
                $events[] = [
                    'type' => 'scan',
                    'date' => $scan->getCreatedAt(),
                    'label' => 'Audit de sécurité terminé (Score: ' . $scan->getMaxSeverity() . ')',
                    'icon' => 'shield-search',
                    'color' => 'lab-primary',
                    'link' => $this->generateUrl('api_scanner_download', ['scanId' => $scan->getScanId()])
                ];
            }
        }

        foreach ($lead->getAppointments() as $appointment) {
            $events[] = [
                'type' => 'appointment',
                'date' => $appointment->getStartsAt(),
                'label' => 'Rendez-vous planifié pour le ' . $appointment->getStartsAt()->format('d/m/Y H:i'),
                'icon' => 'calendar',
                'color' => 'lab-cyan'
            ];
        }

        usort($events, fn(array $a, array $b) => $b['date'] <=> $a['date']);

        return $this->render('admin/leads/show.html.twig', [
            'lead' => $lead,
            'events' => $events,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_lead_delete', methods: ['POST'])]
    public function delete(Request $request, Lead $lead, EntityManagerInterface $em, SystemLogService $logService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lead->getId(), $request->getPayload()->getString('_token'))) {
            $email = $lead->getEmail();
            $em->remove($lead);
            $em->flush();

            $logService->warning("Lead supprimé par l'administrateur : " . $email, "LEAD");
            $this->addFlash('success', 'Lead supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_dashboard');
    }
}
