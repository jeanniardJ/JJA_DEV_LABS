<?php

namespace App\Tests\Unit\Service;

use App\Service\GeminiAIService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiAIServiceTest extends TestCase
{
    public function testGenerateResponseReturnsContent(): void
    {
        $mockResponseData = [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'Brouillon de réponse généré par IA.']
                        ]
                    ]
                ]
            ]
        ];

        $mockResponse = new MockResponse(json_encode($mockResponseData), [
            'http_code' => 200,
            'response_headers' => ['Content-Type' => 'application/json']
        ]);

        $httpClient = new MockHttpClient($mockResponse);
        $service = new GeminiAIService($httpClient, 'fake_key', 'System Prompt Test');

        $result = $service->generateResponse('Prompt de test');

        $this->assertEquals('Brouillon de réponse généré par IA.', $result);
        $this->assertEquals('POST', $mockResponse->getRequestMethod());
        $this->assertStringContainsString('fake_key', $mockResponse->getRequestUrl());
    }

    public function testGenerateResponseHandlesError(): void
    {
        $mockResponse = new MockResponse('Error', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResponse);
        $service = new GeminiAIService($httpClient, 'fake_key', 'System Prompt Test');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Erreur lors de l\'appel à l\'API Gemini');

        $service->generateResponse('Prompt de test');
    }
}
