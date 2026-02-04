<?php

namespace App\Controller\Admin;

use App\Entity\Lead;
use App\Repository\ScanResultRepository;
use App\Repository\AppointmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        // Aggregate events for timeline
        $events = [];

        // 1. Creation
        $events[] = [
            'type' => 'creation',
            'date' => $lead->getCreatedAt(),
            'label' => 'Lead créé via ' . $lead->getSubject(),
            'icon' => 'user-plus',
            'color' => 'lab-terminal'
        ];

        // 2. Scan Results (match by email or URL if stored)
        // For now, let's assume we can find scans related to this lead's email if we had that link
        // Or just show scans that happened around the same time? 
        // Better: if subject contains "Analyse de", extract URL or just show relevant scans.
        // Actually, let's just search by URL in the subject if it exists
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

        // 3. Appointments
        foreach ($lead->getAppointments() as $appointment) {
            $events[] = [
                'type' => 'appointment',
                'date' => $appointment->getCreatedAt(), // Or the actual date? 
                'label' => 'Rendez-vous planifié pour le ' . $appointment->getStartsAt()->format('d/m/Y H:i'),
                'icon' => 'calendar',
                'color' => 'lab-cyan'
            ];
        }

        // Sort events by date DESC
        usort($events, fn($a, $b) => $b['date'] <=> $a['date']);

        return $this->render('admin/leads/show.html.twig', [
            'lead' => $lead,
            'events' => $events,
        ]);
    }
}
