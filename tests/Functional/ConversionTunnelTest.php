<?php

namespace App\Tests\Functional;

use App\Entity\ScanResult;
use App\Repository\ScanResultRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConversionTunnelTest extends WebTestCase
{
    public function testConversionApiReturnsHtmlWhenScanCompleted(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        
        $scanId = 'test-scan-' . uniqid();
        $scanResult = new ScanResult();
        $scanResult->setScanId($scanId);
        $scanResult->setUrl('https://example.com');
        $scanResult->setStatus('completed');
        $scanResult->setRawOutput([
            ['info' => ['severity' => 'critical']]
        ]);

        $entityManager = $container->get('doctrine.orm.entity_manager');
        $entityManager->persist($scanResult);
        $entityManager->flush();

        $client->request('GET', '/api/scanner/conversion/' . $scanId);
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h4', 'Sécurisez votre site d\'urgence');
        $this->assertSelectorTextContains('p', 'https://example.com');
    }

    public function testAppointmentFormPreFilledFromQuery(): void
    {
        $client = static::createClient();
        $client->request('GET', '/appointments?context=scan&site=example.com&severity=critical&count=5');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="subject"]');
        
        // Check if value is pre-filled in the HTML
        $crawler = $client->getCrawler();
        $value = $crawler->filter('input[name="subject"]')->attr('value');
        $this->assertStringContainsString('Analyse de example.com', $value);
        $this->assertStringContainsString('critical', $value);
    }
}
