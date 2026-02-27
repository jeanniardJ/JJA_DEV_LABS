<?php

namespace App\Tests\Service;

use App\Service\SiteVerificationService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class SiteVerificationServiceTest extends KernelTestCase
{
    public function testVerifyFileSuccess(): void
    {
        $token = 'jja-lab-12345';
        $mockResponse = new MockResponse($token, ['http_code' => 200]);
        $client = new MockHttpClient($mockResponse);
        
        $service = new SiteVerificationService($client);
        $this->assertTrue($service->verifyFile('https://example.com', $token));
    }

    public function testVerifyFileFailsOnPartialMatch(): void
    {
        $token = 'jja-lab-12345';
        // Content contains token but also garbage
        $mockResponse = new MockResponse('Some content ' . $token, ['http_code' => 200]);
        $client = new MockHttpClient($mockResponse);
        
        $service = new SiteVerificationService($client);
        $this->assertFalse($service->verifyFile('https://example.com', $token));
    }

    public function testVerifyFileFailsOnPrivateIp(): void
    {
        // This test requires mocking DNS resolution which is hard in PHP without extensions.
        // We will skip testing the actual gethostbynamel here but trust the logic.
        // Instead, we can test with localhost IP which filter_var catches.
        
        $mockResponse = new MockResponse('token', ['http_code' => 200]);
        $client = new MockHttpClient($mockResponse);
        
        $service = new SiteVerificationService($client);
        
        // Use a domain that likely resolves to localhost or create a condition where gethostbynamel returns 127.0.0.1
        // Since we can't easily mock global functions, we rely on filter_var logic in code review.
        // However, we can try verifyFile with 'http://127.0.0.1' directly if gethostbynamel handles it.
        // gethostbynamel('127.0.0.1') returns ['127.0.0.1']
        
        $this->assertFalse($service->verifyFile('http://127.0.0.1', 'token'));
        $this->assertFalse($service->verifyFile('http://localhost', 'token'));
    }
}
