<?php

namespace App\Tests\Unit\Service;

use App\Repository\LeadRepository;
use App\Repository\ScanResultRepository;
use App\Service\AnalyticsService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class AnalyticsServiceTest extends TestCase
{
    public function testGetDashboardStats(): void
    {
        $leadRepo = $this->createMock(LeadRepository::class);
        $scanRepo = $this->createMock(ScanResultRepository::class);
        $cache = $this->createMock(CacheInterface::class);

        $leadRepo->method('countTotal')->willReturn(10);
        $leadRepo->method('countConverted')->willReturn(2);
        $scanRepo->method('countTotal')->willReturn(5);
        $leadRepo->method('countByDay')->willReturn([['date' => '2026-02-04', 'count' => 1]]);
        $scanRepo->method('getSeverityStats')->willReturn([['severity' => 'Critical', 'count' => 1]]);

        // Mock cache behavior
        $cache->method('get')->willReturnCallback(function($key, $callback) {
            $item = $this->createMock(ItemInterface::class);
            return $callback($item);
        });

        $service = new AnalyticsService($leadRepo, $scanRepo, $cache);
        $start = new \DateTimeImmutable('-30 days');
        $end = new \DateTimeImmutable('now');

        $stats = $service->getDashboardStats($start, $end);

        $this->assertEquals(10, $stats['kpis']['total_leads']);
        $this->assertEquals(20, $stats['kpis']['conversion_rate']); // (2/10)*100
        $this->assertEquals(5, $stats['kpis']['total_scans']);
    }
}
