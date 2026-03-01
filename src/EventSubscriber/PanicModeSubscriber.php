<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class PanicModeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private KernelInterface $kernel
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $panicFile = $this->kernel->getProjectDir() . '/var/panic.lock';
        
        if (file_exists($panicFile)) {
            $request = $event->getRequest();
            $path = $request->getPathInfo();

            // Bloquer les POST vers les formulaires critiques
            if ($request->isMethod('POST') && (
                str_starts_with($path, '/contact') || 
                str_starts_with($path, '/api/appointments/book') ||
                str_starts_with($path, '/api/scanner/run')
            )) {
                $event->setResponse(new JsonResponse([
                    'status' => 'error',
                    'message' => 'SYSTÈME VERROUILLÉ : Le mode maintenance critique est activé. Veuillez réessayer plus tard.'
                ], 503));
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
        ];
    }
}
