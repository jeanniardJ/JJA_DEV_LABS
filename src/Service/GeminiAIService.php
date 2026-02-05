<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiAIService
{
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $geminiApiKey,
        private readonly string $systemPrompt
    ) {
    }

    public function generateResponse(string $prompt): string
    {
        try {
            $response = $this->httpClient->request('POST', self::API_URL . '?key=' . $this->geminiApiKey, [
                'json' => [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $this->systemPrompt . "

Contexte client :
" . $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1000,
                    ]
                ]
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                throw new \RuntimeException('Erreur lors de l\'appel à l\'API Gemini (Status: ' . $statusCode . ')');
            }

            $data = $response->toArray();
            
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? throw new \RuntimeException('Réponse IA vide ou malformée');

        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'appel à l\'API Gemini: ' . $e->getMessage(), 0, $e);
        }
    }
}
