<?php

namespace App\Controller\Admin;

use App\Enum\LeadStatus;
use App\Repository\LeadRepository;
use App\Repository\UserRepository;
use App\Repository\AppointmentRepository;
use App\Repository\PushSubscriptionRepository;
use App\Repository\LabStationRepository;
use App\Repository\SystemLogRepository;
use App\Service\SystemLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        Request $request, 
        LeadRepository $leadRepository,
        UserRepository $userRepository,
        AppointmentRepository $appointmentRepository,
        PushSubscriptionRepository $pushSubscriptionRepository,
        LabStationRepository $labStationRepository,
        SystemLogRepository $systemLogRepository,
        ChartBuilderInterface $chartBuilder,
        ParameterBagInterface $params
    ): Response {
        $sort = $request->query->get('sort', 'createdAt');
        $direction = $request->query->get('direction', 'DESC');

        // Fetch leads
        $leads = $leadRepository->findAllWithAppointmentsCount($sort, $direction);
        
        $groupedLeads = [];
        foreach (LeadStatus::cases() as $status) {
            $groupedLeads[$status->value] = array_filter($leads, fn($lead) => $lead->getStatus() === $status);
        }

        // Fetch real system logs
        $systemLogs = $systemLogRepository->findRecent(15);

        // Nouveaux Leads Alert
        $newLeadsCount = count($groupedLeads[LeadStatus::NEW->value] ?? []);
        if ($newLeadsCount > 0) {
            $this->addFlash('info', sprintf("ALERTE : %d nouveau(x) lead(s) en attente de traitement.", $newLeadsCount));
        }

        // Activity Chart (Last 30 days)
        $start = new \DateTimeImmutable('-30 days');
        $end = new \DateTimeImmutable('now');
        $dataByDay = $leadRepository->countByDay($start, $end);
        
        $labels = [];
        $data = [];
        $current = $start;
        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $labels[] = $current->format('d/m');
            $found = array_filter($dataByDay, fn($d) => $d['date'] === $dateStr);
            $data[] = !empty($found) ? reset($found)['count'] : 0;
            $current = $current->modify('+1 day');
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Nouveaux Leads',
                    'backgroundColor' => 'rgba(0, 198, 255, 0.1)',
                    'borderColor' => '#00c6ff',
                    'data' => $data,
                    'tension' => 0.4,
                    'fill' => true,
                    'pointRadius' => 0,
                ],
            ],
        ]);

        $chart->setOptions([
            'maintainAspectRatio' => false,
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'y' => ['display' => false, 'suggestedMin' => 0, 'suggestedMax' => max($data) + 2],
                'x' => ['display' => true, 'grid' => ['display' => false], 'ticks' => ['color' => '#444', 'font' => ['size' => 8]]],
            ],
        ]);

        // Stats
        $stats = [
            'total_users' => $userRepository->count([]),
            'total_leads' => count($leads),
            'today_appointments' => count($appointmentRepository->findByDay(new \DateTimeImmutable())),
            'active_push' => $pushSubscriptionRepository->count([]),
            'active_stations' => $labStationRepository->count([]),
            'memory_usage' => number_format(memory_get_usage() / 1024 / 1024, 2),
        ];

        return $this->render('admin/dashboard.html.twig', [
            'grouped_leads' => $groupedLeads,
            'statuses' => LeadStatus::cases(),
            'current_sort' => $sort,
            'current_direction' => $direction,
            'stats' => $stats,
            'stations' => $labStationRepository->findAllSorted(),
            'activity_chart' => $chart,
            'system_logs' => $systemLogs,
            'vapid_public_key' => $_ENV['VAPID_PUBLIC_KEY'] ?? '',
        ]);
    }

    #[Route('/export-report', name: 'admin_export_report')]
    public function exportReport(LeadRepository $leadRepository, SystemLogService $logService, \App\Service\PdfGenerator $pdfGenerator): Response
    {
        $leads = $leadRepository->findAll();
        
        $pdfContent = $pdfGenerator->generate('admin/report/leads_pdf.html.twig', [
            'leads' => $leads,
        ]);

        $logService->info("Rapport PDF des leads généré par l'administrateur.", "REPORT");

        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="jja_leads_report_' . date('Ymd') . '.pdf"'
            ]
        );
    }

    #[Route('/panic-mode', name: 'admin_panic_mode', methods: ['POST'])]
    public function panicMode(SystemLogService $logService, ParameterBagInterface $params): Response
    {
        $panicFile = $params->get('kernel.project_dir') . '/var/panic.lock';
        
        if (file_exists($panicFile)) {
            unlink($panicFile);
            $logService->system("PANIC_MODE DÉSACTIVÉ : Retour à la normale.", "SECURITY");
            $this->addFlash('success', 'Mode panique désactivé. Les services publics sont rétablis.');
        } else {
            file_put_contents($panicFile, date('Y-m-d H:i:s'));
            $logService->system("ALERTE CRITIQUE : PANIC_MODE_ACTIVÉ par l'administrateur.", "SECURITY");
            $this->addFlash('error', 'PANIC_MODE_ACTIVÉ : Les formulaires publics ont été verrouillés.');
        }
        
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/clear-logs', name: 'admin_clear_logs', methods: ['POST'])]
    public function clearLogs(EntityManagerInterface $em, SystemLogService $logService): Response
    {
        $em->createQuery('DELETE FROM App\Entity\SystemLog')->execute();
        $logService->warning("Journal du Kernel effacé par l'administrateur.", "SYSTEM");

        return $this->redirectToRoute('admin_dashboard');
    }
}
