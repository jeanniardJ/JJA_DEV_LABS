<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ScanResult;
use PHPUnit\Framework\TestCase;

class ScanResultTest extends TestCase
{
    public function testGetMaxSeverityReturnsNoneIfNoResults(): void
    {
        $scanResult = new ScanResult();
        $this->assertEquals('none', $scanResult->getMaxSeverity());
    }

    public function testGetMaxSeverityReturnsCorrectLevel(): void
    {
        $scanResult = new ScanResult();
        
        $scanResult->setRawOutput([
            ['info' => ['severity' => 'low']],
            ['info' => ['severity' => 'medium']],
            ['info' => ['severity' => 'low']],
        ]);
        $this->assertEquals('medium', $scanResult->getMaxSeverity());

        $scanResult->setRawOutput([
            ['info' => ['severity' => 'high']],
            ['info' => ['severity' => 'critical']],
            ['info' => ['severity' => 'low']],
        ]);
        $this->assertEquals('critical', $scanResult->getMaxSeverity());

        $scanResult->setRawOutput([
            ['info' => ['severity' => 'info']],
            ['info' => ['severity' => 'low']],
        ]);
        $this->assertEquals('low', $scanResult->getMaxSeverity());
    }
}
