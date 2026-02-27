<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class SecurityLoggingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Target('security.logger')]
        private LoggerInterface $securityLogger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $this->securityLogger->info(sprintf('Login successful for user: %s', $user->getUserIdentifier()));
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $this->securityLogger->warning(sprintf('Login failed: %s', $event->getException()->getMessage()));
    }

    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();
        if ($user) {
            $this->securityLogger->info(sprintf('User logged out: %s', $user->getUserIdentifier()));
        }
    }
}
