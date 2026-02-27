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

        $host = $parsedUrl['host'];

        // Allow self-verification on localhost/127.0.0.1 for testing, 
        // but keep SSRF protection for other private ranges.
        $isLocal = in_array($host, ['127.0.0.1', 'localhost', '0.0.0.0'], true);

        // SSRF Protection: Resolve Host and check for private IPs (except localhost if allowed)
        $ips = gethostbynamel($host);
        if ($ips && !$isLocal) {
            foreach ($ips as $ip) {
                if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return false; // Private IP detected (e.g. 192.168.x.x)
                }
            }
        }

        $baseUrl = ($parsedUrl['scheme'] ?? 'https') . '://' . $host;
        if (isset($parsedUrl['port'])) {
            $baseUrl .= ':' . $parsedUrl['port'];
        }
        
        $verificationPath = '/.well-known/jja-lab-token.txt';
        $verificationUrl = $baseUrl . $verificationPath;

        // Special case for local testing: check file system directly to avoid PHP built-in server deadlock
        if ($isLocal) {
            $localFilePath = dirname(__DIR__, 2) . '/public' . $verificationPath;
            if (file_exists($localFilePath)) {
                $content = trim(file_get_contents($localFilePath));
                return $content === $token;
            }
        }

        try {
            $options = [
                'timeout' => 5,
                'headers' => [
                    'User-Agent' => 'JJA-Lab-Verifier/1.0',
                ],
            ];

            // Disable SSL peer verification for local testing
            if ($isLocal) {
                $options['verify_peer'] = false;
                $options['verify_host'] = false;
            }

            $response = $this->httpClient->request('GET', $verificationUrl, $options);

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
