<?php

namespace App\Tests\MessageHandler\Scanner;

use App\Entity\ScanResult;
use App\Message\Scanner\TriggerScanMessage;
use App\MessageHandler\Scanner\TriggerScanHandler;
use App\Repository\ScanResultRepository;
use App\Service\NucleiScanner;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TriggerScanHandlerTest extends TestCase
{
    public function testHandlerSuccess(): void
    {
        $scanId = 'test-scan-123';
        $url = 'https://example.com';
        
        $nucleiScanner = $this->createMock(NucleiScanner::class);
        $nucleiScanner->expects($this->once())
            ->method('scan')
            ->with($url)
            ->willReturn([['id' => 'finding']]);
            
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $scanResultRepository = $this->createMock(ScanResultRepository::class);
        
        $scanResult = new ScanResult();
        $scanResult->setScanId($scanId);
        $scanResult->setUrl($url);
        
        $scanResultRepository->method('findOneBy')->willReturn($scanResult);
        
        $handler = new TriggerScanHandler($nucleiScanner, $entityManager, $scanResultRepository);
        $message = new TriggerScanMessage($scanId, $url);
        
        $handler($message);
        
        $this->assertEquals('completed', $scanResult->getStatus());
        $this->assertNotEmpty($scanResult->getRawOutput());
        $this->assertNull($scanResult->getErrorMessage());
    }

    public function testHandlerFailure(): void
    {
        $scanId = 'test-scan-failed';
        $url = 'https://example.com';
        
        $nucleiScanner = $this->createMock(NucleiScanner::class);
        $nucleiScanner->method('scan')->willThrowException(new \Exception('Scan timed out'));
            
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $scanResultRepository = $this->createMock(ScanResultRepository::class);
        
        $scanResult = new ScanResult();
        $scanResultRepository->method('findOneBy')->willReturn($scanResult);
        
        $handler = new TriggerScanHandler($nucleiScanner, $entityManager, $scanResultRepository);
        $message = new TriggerScanMessage($scanId, $url);
        
        $handler($message);
        
        $this->assertEquals('failed', $scanResult->getStatus());
        $this->assertEquals('Scan timed out', $scanResult->getErrorMessage());
    }
}
