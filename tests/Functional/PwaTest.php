<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PwaTest extends WebTestCase
{
    public function testManifestIsServed(): void
    {
        $client = static::createClient();
        $client->request('GET', '/manifest.json');

        // AssetMapper might prefix the path if configured, 
        // but here we expect it at the root or correctly linked.
        // Let's first check if it's in the base.html.twig.
        $crawler = $client->request('GET', '/');
        $this->assertSelectorExists('link[rel="manifest"]');
        
        $manifestHref = $crawler->filter('link[rel="manifest"]')->attr('href');
        $client->request('GET', $manifestHref);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function testServiceWorkerIsServed(): void
    {
        $client = static::createClient();
        $client->request('GET', '/service-worker.js');

        $this->assertResponseIsSuccessful();
        // SW should be served from public/ via route
        $this->assertResponseHeaderSame('Content-Type', 'application/javascript');
    }

    public function testOfflinePage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/offline');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.text-lab-danger', '[ERREUR] : AUCUNE CONNEXION RÉSEAU DÉTECTÉE');
    }
}
