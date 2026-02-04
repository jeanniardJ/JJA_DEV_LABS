<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ScannerControllerTest extends WebTestCase
{
    public function testSubmitValidUrl(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/scanner/submit', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['url' => 'https://example.com']));

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('scan_id', $data);
    }

    public function testSubmitInvalidUrl(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/scanner/submit', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['url' => 'not-a-url']));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testStatusPolling(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/scanner/submit', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['url' => 'https://example.com']));

        $data = json_decode($client->getResponse()->getContent(), true);
        $scanId = $data['scan_id'];

        $client->request('GET', '/api/scanner/status/'.$scanId);
        $this->assertResponseIsSuccessful();
        $statusData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('percentage', $statusData);
        $this->assertArrayHasKey('current_step', $statusData);
    }

    public function testVerifyEndpointFailsWithoutToken(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/scanner/verify/invalid-id', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['method' => 'file']));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testVerifyEndpointRateLimiter(): void
    {
        $client = static::createClient();
        for ($i = 0; $i < 6; $i++) {
            $client->request('POST', '/api/scanner/verify/some-id', [], [], [
                'CONTENT_TYPE' => 'application/json'
            ], json_encode(['method' => 'file']));
        }

        $this->assertResponseStatusCodeSame(429);
    }

    public function testDownloadEndpointNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/scanner/download/non-existent');
        $this->assertResponseStatusCodeSame(404);
    }
}
