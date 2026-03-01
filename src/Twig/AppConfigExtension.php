<?php

namespace App\Twig;

use App\Service\ConfigurationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppConfigExtension extends AbstractExtension
{
    public function __construct(
        private ConfigurationService $configService
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_config', [$this, 'getConfig']),
        ];
    }

    public function getConfig(string $key, ?string $default = null): ?string
    {
        return $this->configService->get($key, $default);
    }
}
