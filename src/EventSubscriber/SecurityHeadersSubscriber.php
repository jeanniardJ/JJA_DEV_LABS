<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();

        // Security Headers
        // Note: Using true for the third parameter of headers->set() ensures we replace any existing header
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' data: https://challenges.cloudflare.com https://cdn.jsdelivr.net; frame-src 'self' https://challenges.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' data: https://fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self' https://challenges.cloudflare.com; worker-src 'self' blob:;", true);
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains', true);
        $response->headers->set('X-Content-Type-Options', 'nosniff', true);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', true);
        $response->headers->set('X-XSS-Protection', '1; mode=block', true);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', true);
        // Remove the unrecognized ones if they still cause errors, but the specification says to set to empty list () to disable
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()', true);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // On utilise une priorité très basse (-100) pour s'exécuter APRES les autres
            // et ainsi pouvoir écraser leurs headers avec le paramètre 'true'
            KernelEvents::RESPONSE => ['onKernelResponse', -100],
        ];
    }
}
