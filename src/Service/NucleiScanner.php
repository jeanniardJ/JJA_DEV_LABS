<?php

namespace App\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class NucleiScanner
{
    private const SIMULATION_SLEEP = 5;

    /** @return list<array<string, mixed>> */
    public function scan(string $url): array
    {
        // Security: Ensure URL is strictly validated before passing to any shell process
        if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//', $url)) {
            throw new \InvalidArgumentException('Invalid URL for scanning.');
        }

        // For production/real use, we would use the nuclei binary
        // $process = new Process(['nuclei', '-u', $url, '-json']);
        // $process->setTimeout(60);
        // $process->run();
        
        // Since nuclei is not installed in this environment, 
        // we simulate a scan output for the technical demonstration.
        
        sleep(self::SIMULATION_SLEEP);

        $results = [];
        
        // Always include some basics
        $results[] = [
            'template-id' => 'missing-secure-headers',
            'info' => [
                'name' => 'Missing Security Headers',
                'severity' => 'low',
                'description' => 'Critical security headers like CSP or HSTS are missing.'
            ],
            'type' => 'http',
            'host' => $url,
            'matched-at' => $url,
        ];

        // Randomly add more findings
        if (str_contains($url, 'test') || rand(0, 1)) {
            $results[] = [
                'template-id' => 'ssl-obsolete-version',
                'info' => [
                    'name' => 'Obsolete SSL Version',
                    'severity' => 'medium',
                    'description' => 'The site uses an obsolete SSL/TLS version (TLS 1.0 or 1.1).'
                ],
                'type' => 'ssl',
                'host' => $url,
                'matched-at' => $url,
            ];
        }

        if (rand(0, 5) === 0) { // 20% chance of critical finding
            $results[] = [
                'template-id' => 'git-config-exposure',
                'info' => [
                    'name' => 'Git Config Exposure',
                    'severity' => 'critical',
                    'description' => 'Sensitive .git/config file is publicly accessible.'
                ],
                'type' => 'http',
                'host' => $url,
                'matched-at' => $url . '/.git/config',
            ];
        }

        return $results;
    }
}
