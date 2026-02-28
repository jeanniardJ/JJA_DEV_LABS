<?php

namespace App\Security;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

use Symfony\Component\HttpFoundation\IpUtils;

#[AsEventListener(event: 'kernel.request', priority: 10)]
class IpWhitelistListener
{
    /** @param list<string> $allowedIps */
    public function __construct(
        #[Autowire('%env(csv:ADMIN_ALLOWED_IPS)%')]
        private array $allowedIps
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (str_starts_with($path, '/admin')) {
            $clientIp = $request->getClientIp();
            if ($clientIp && !IpUtils::checkIp($clientIp, $this->allowedIps)) {
                throw new NotFoundHttpException();
            }
        }
    }
}
