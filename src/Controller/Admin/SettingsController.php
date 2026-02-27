<?php

namespace App\Controller\Admin;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/settings')]
#[IsGranted('ROLE_ADMIN')]
class SettingsController extends AbstractController
{
    #[Route('', name: 'admin_settings')]
    public function index(KernelInterface $kernel, Connection $connection): Response
    {
        $phpInfo = [
            'version' => PHP_VERSION,
            'os' => PHP_OS,
            'sapi' => PHP_SAPI,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'extensions' => get_loaded_extensions(),
        ];

        $symfonyInfo = [
            'version' => \Symfony\Component\HttpKernel\Kernel::VERSION,
            'environment' => $kernel->getEnvironment(),
            'debug' => $kernel->isDebug(),
            'project_dir' => $kernel->getProjectDir(),
            'cache_dir' => $kernel->getCacheDir(),
            'log_dir' => $kernel->getLogDir(),
        ];

        $services = [
            [
                'name' => 'Base de données (MySQL)',
                'status' => $this->checkDatabase($connection),
                'icon' => 'database',
            ],
            [
                'name' => 'Google OAuth2',
                'status' => !empty($_ENV['GOOGLE_CLIENT_ID']),
                'icon' => 'key-round',
            ],
            [
                'name' => 'Gemini AI',
                'status' => !empty($_ENV['GEMINI_API_KEY']),
                'icon' => 'brain',
            ],
            [
                'name' => 'VAPID (Push Notifications)',
                'status' => !empty($_ENV['VAPID_PUBLIC_KEY']),
                'icon' => 'bell',
            ],
            [
                'name' => 'Cloudflare Turnstile',
                'status' => !empty($_ENV['TURNSTILE_KEY']),
                'icon' => 'shield-check',
            ],
            [
                'name' => 'Mailer (SMTP)',
                'status' => !empty($_ENV['MAILER_DSN']) && $_ENV['MAILER_DSN'] !== 'null://null',
                'icon' => 'mail',
            ],
        ];

        return $this->render('admin/settings/index.html.twig', [
            'php_info' => $phpInfo,
            'symfony_info' => $symfonyInfo,
            'services' => $services,
        ]);
    }

    private function checkDatabase(Connection $connection): bool
    {
        try {
            $connection->executeQuery('SELECT 1');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
