<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiAIService
{
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    private const SYSTEM_PROMPT = "Tu es l'assistant IA de Jonas Jeanniard, expert en développement Symfony et cybersécurité. Ta mission est d'aider Jonas à répondre à ses leads de manière technique, professionnelle et concise. Réponds toujours en français, avec un ton empathique mais expert. Ne propose pas de code inutile, concentre-toi sur la valeur métier et la sécurité.";

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $geminiApiKey
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
                                ['text' => self::SYSTEM_PROMPT . "

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
