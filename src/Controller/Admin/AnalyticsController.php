<?php

namespace App\Controller\Admin;

use App\Service\AnalyticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/admin/analytics')]
#[IsGranted('ROLE_ADMIN')]
class AnalyticsController extends AbstractController
{
    #[Route('', name: 'admin_analytics_index')]
    public function index(Request $request, AnalyticsService $analyticsService, ChartBuilderInterface $chartBuilder): Response
    {
        $period = $request->query->get('period', '30days');
        
        $end = new \DateTimeImmutable('now');
        $start = match ($period) {
            '7days' => $end->modify('-7 days'),
            'this_month' => $end->modify('first day of this month'),
            default => $end->modify('-30 days'),
        };

        $stats = $analyticsService->getDashboardStats($start, $end);

        // Activity Chart
        $activityChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        
        $labels = array_column($stats['leads_by_day'], 'date');
        $data = array_column($stats['leads_by_day'], 'count');

        $activityChart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Nouveaux Leads',
                    'backgroundColor' => 'rgba(0, 255, 136, 0.1)', // lab-terminal avec opacité
                    'borderColor' => '#00ff88', // lab-terminal
                    'data' => $data,
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
        ]);

        $activityChart->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['stepSize' => 1, 'color' => '#64748b'], // lab-muted
                    'grid' => ['color' => 'rgba(26, 35, 50, 0.8)'] // lab-border
                ],
                'x' => [
                    'ticks' => ['color' => '#64748b'], // lab-muted
                    'grid' => ['display' => false]
                ]
            ],
            'plugins' => [
                'legend' => ['display' => false]
            ]
        ]);

        // Severity Chart
        $severityChart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $severityLabels = array_column($stats['severity_stats'], 'severity');
        $severityData = array_column($stats['severity_stats'], 'count');
        
        $colors = [
            'critical' => '#ff3366', // lab-danger
            'high' => '#ffb700',     // lab-warning
            'medium' => '#00c6ff',   // lab-primary
            'low' => '#00ff88',      // lab-terminal
            'info' => '#4df4ff'      // lab-cyan
        ];
        
        $backgroundColors = array_map(fn($s) => $colors[strtolower($s)] ?? '#666', $severityLabels);

        $severityChart->setData([
            'labels' => array_map(fn($s) => strtoupper((string)$s), $severityLabels),
            'datasets' => [
                [
                    'data' => $severityData,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 0,
                ],
            ],
        ]);

        $severityChart->setOptions([
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => ['color' => '#999', 'boxWidth' => 10, 'font' => ['size' => 10]]
                ]
            ],
            'cutout' => '70%'
        ]);

        return $this->render('admin/analytics/index.html.twig', [
            'stats' => $stats,
            'activity_chart' => $activityChart,
            'severity_chart' => $severityChart,
            'current_period' => $period,
        ]);
    }
}
