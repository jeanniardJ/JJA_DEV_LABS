<?php

namespace App\Service;

use App\Repository\LeadRepository;
use App\Repository\ScanResultRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class AnalyticsService
{
    public function __construct(
        private readonly LeadRepository $leadRepository,
        private readonly ScanResultRepository $scanResultRepository,
        private readonly CacheInterface $cache
    ) {
    }

    public function getDashboardStats(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $cacheKey = sprintf('dashboard_stats_%s_%s', $start->format('Ymd'), $end->format('Ymd'));

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($start, $end) {
            $item->expiresAfter(3600); // 1 hour

            $totalLeads = $this->leadRepository->countTotal($start, $end);
            $convertedLeads = $this->leadRepository->countConverted($start, $end);
            $totalScans = $this->scanResultRepository->countTotal($start, $end);
            
            $conversionRate = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;

            return [
                'kpis' => [
                    'total_leads' => $totalLeads,
                    'total_scans' => $totalScans,
                    'conversion_rate' => round($conversionRate, 1),
                ],
                'leads_by_day' => $this->leadRepository->countByDay($start, $end),
                'severity_stats' => $this->scanResultRepository->getSeverityStats($start, $end),
            ];
        });
    }
}
