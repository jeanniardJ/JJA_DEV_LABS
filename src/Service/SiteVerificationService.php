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
        if (!isset($parsedUrl['host'])) {
            return false;
        }

        // SSRF Protection: Resolve Host and check for private IPs
        $host = $parsedUrl['host'];
        $ips = gethostbynamel($host);
        if ($ips) {
            foreach ($ips as $ip) {
                if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return false; // Private IP detected
                }
            }
        }

        $baseUrl = ($parsedUrl['scheme'] ?? 'https') . '://' . $host;
        if (isset($parsedUrl['port'])) {
            $baseUrl .= ':' . $parsedUrl['port'];
        }
        
        $verificationUrl = $baseUrl . '/.well-known/jja-lab-token.txt';

        try {
            $response = $this->httpClient->request('GET', $verificationUrl, [
                'timeout' => 5,
                'headers' => [
                    'User-Agent' => 'JJA-Lab-Verifier/1.0',
                ],
            ]);

            if (200 !== $response->getStatusCode()) {
                return false;
            }

            $content = trim($response->getContent());
            // Strict comparison: content must be EXACTLY the token
            return $content === $token;
        } catch (\Exception $e) {
            return false;
        }
    }
}
