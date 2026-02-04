<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SiteVerificationService
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    public function verifyDns(string $domain, string $token): bool
    {
        $domain = parse_url($domain, PHP_URL_HOST) ?: $domain;
        
        try {
            $records = dns_get_record($domain, DNS_TXT);
            if (false === $records) {
                return false;
            }

            foreach ($records as $record) {
                if (isset($record['txt']) && str_contains($record['txt'], $token)) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    public function verifyFile(string $url, string $token): bool
    {
        $parsedUrl = parse_url($url);
        $baseUrl = ($parsedUrl['scheme'] ?? 'https') . '://' . $parsedUrl['host'];
        if (isset($parsedUrl['port'])) {
            $baseUrl .= ':' . $parsedUrl['port'];
        }
        
        $verificationUrl = $baseUrl . '/.well-known/jja-lab-token.txt';

        try {
            $response = $this->httpClient->request('GET', $verificationUrl, [
                'timeout' => 5,
            ]);

            if (200 !== $response->getStatusCode()) {
                return false;
            }

            $content = $response->getContent();
            return str_contains($content, $token);
        } catch (\Exception $e) {
            return false;
        }
    }
}
