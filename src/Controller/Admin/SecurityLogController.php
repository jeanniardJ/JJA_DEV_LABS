<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/security-logs')]
#[IsGranted('ROLE_ADMIN')]
class SecurityLogController extends AbstractController
{
    #[Route('', name: 'admin_security_logs')]
    public function index(Request $request): Response
    {
        $logDir = $this->getParameter('kernel.logs_dir');
        $env = $this->getParameter('kernel.environment');
        $logFile = $logDir . '/' . $env . '.log';

        $entries = [];
        $filter = $request->query->get('filter', 'all');

        if (file_exists($logFile)) {
            // Lire les 500 dernières lignes
            $lines = $this->tailFile($logFile, 500);

            foreach ($lines as $line) {
                $parsed = $this->parseLogLine($line);
                if (!$parsed) {
                    continue;
                }

                // Filtrer selon le type demandé
                if ($filter === 'security' && !$parsed['is_security']) {
                    continue;
                }
                if ($filter === 'error' && !in_array($parsed['level'], ['ERROR', 'CRITICAL', 'EMERGENCY'])) {
                    continue;
                }
                if ($filter === 'auth' && !str_contains(strtolower($parsed['message']), 'login') && !str_contains(strtolower($parsed['message']), 'logout') && !str_contains(strtolower($parsed['message']), 'auth')) {
                    continue;
                }

                $entries[] = $parsed;
            }

            // Plus récent en premier
            $entries = array_reverse($entries);
            $entries = array_slice($entries, 0, 100);
        }

        return $this->render('admin/security_logs/index.html.twig', [
            'entries' => $entries,
            'log_exists' => file_exists($logFile),
            'current_filter' => $filter,
        ]);
    }

    /** @return array{datetime: string, channel: string, level: string, message: string, is_security: bool}|null */
    private function parseLogLine(string $line): ?array
    {
        // Format Symfony: [2026-02-27T10:00:00+00:00] security.INFO: Login successful ...
        if (!preg_match('/^\[(\d{4}-\d{2}-\d{2}T[\d:+\-.]+)\]\s+(\w+)\.(\w+):\s+(.+)$/', $line, $m)) {
            return null;
        }

        $channel = $m[2];
        $level = strtoupper($m[3]);
        $message = $m[4];

        return [
            'datetime' => $m[1],
            'channel' => $channel,
            'level' => $level,
            'message' => $message,
            'is_security' => in_array($channel, ['security', 'app']),
        ];
    }

    /** @return list<string> */
    private function tailFile(string $filepath, int $lines): array
    {
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            return [];
        }

        $buffer = [];
        while (($line = fgets($handle)) !== false) {
            $buffer[] = trim($line);
            if (count($buffer) > $lines) {
                array_shift($buffer);
            }
        }
        fclose($handle);

        return $buffer;
    }
}
