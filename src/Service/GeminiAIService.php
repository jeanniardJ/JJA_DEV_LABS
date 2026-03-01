<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiAIService
{
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ConfigurationService $configService,
        private readonly string $geminiApiKey,
        private readonly string $systemPrompt // Fallback
    ) {
    }

    public function generateResponse(string $userMessage): string
    {
        $prompt = $this->configService->get('gemini_system_prompt', $this->systemPrompt);

        $response = $this->httpClient->request('POST', self::API_URL . '?key=' . $this->geminiApiKey, [
            'json' => [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt . "\n\nMessage client : " . $userMessage]
                        ]
                    ]
                ]
            ]
        ]);

        $data = $response->toArray();

        return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Désolé, je ne peux pas générer de réponse pour le moment.';
    }
}
