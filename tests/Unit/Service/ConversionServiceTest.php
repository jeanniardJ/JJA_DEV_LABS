<?php

namespace App\Tests\Unit\Service;

use App\Entity\ScanResult;
use App\Service\ConversionService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConversionServiceTest extends TestCase
{
    private ConversionService $conversionService;
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->conversionService = new ConversionService($this->translator);
    }

    public function testGetHookMessageForCritical(): void
    {
        $scanResult = $this->createMock(ScanResult::class);
        $scanResult->method('getMaxSeverity')->willReturn('critical');

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('conversion.hook.critical')
            ->willReturn('Sécurisez votre site d\'urgence');
        
        $message = $this->conversionService->getHookMessage($scanResult);
        $this->assertEquals('Sécurisez votre site d\'urgence', $message);
    }

    public function testGetHookMessageForHigh(): void
    {
        $scanResult = $this->createMock(ScanResult::class);
        $scanResult->method('getMaxSeverity')->willReturn('high');

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('conversion.hook.critical')
            ->willReturn('Sécurisez votre site d\'urgence');
        
        $message = $this->conversionService->getHookMessage($scanResult);
        $this->assertEquals('Sécurisez votre site d\'urgence', $message);
    }

    public function testGetHookMessageForMedium(): void
    {
        $scanResult = $this->createMock(ScanResult::class);
        $scanResult->method('getMaxSeverity')->willReturn('medium');

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('conversion.hook.medium')
            ->willReturn('Optimisez votre sécurité');
        
        $message = $this->conversionService->getHookMessage($scanResult);
        $this->assertEquals('Optimisez votre sécurité', $message);
    }

    public function testGetHookMessageForLow(): void
    {
        $scanResult = $this->createMock(ScanResult::class);
        $scanResult->method('getMaxSeverity')->willReturn('low');

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('conversion.hook.medium')
            ->willReturn('Optimisez votre sécurité');
        
        $message = $this->conversionService->getHookMessage($scanResult);
        $this->assertEquals('Optimisez votre sécurité', $message);
    }

    public function testGetHookMessageForNone(): void
    {
        $scanResult = $this->createMock(ScanResult::class);
        $scanResult->method('getMaxSeverity')->willReturn('none');

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('conversion.hook.safe')
            ->willReturn('Votre site semble sécurisé, restons-en là ?');
        
        $message = $this->conversionService->getHookMessage($scanResult);
        $this->assertEquals('Votre site semble sécurisé, restons-en là ?', $message);
    }
}
