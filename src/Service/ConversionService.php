<?php

namespace App\Service;

use App\Entity\ScanResult;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConversionService
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    public function getHookMessage(ScanResult $scanResult): string
    {
        $severity = $scanResult->getMaxSeverity();

        return match ($severity) {
            'critical', 'high' => $this->translator->trans('conversion.hook.critical'),
            'medium', 'low' => $this->translator->trans('conversion.hook.medium'),
            default => $this->translator->trans('conversion.hook.safe'),
        };
    }
}
