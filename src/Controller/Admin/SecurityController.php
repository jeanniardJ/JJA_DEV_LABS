<?php

namespace App\Controller\Admin;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    private ClientRegistry $clientRegistry;

    public function __construct(ClientRegistry $clientRegistry)
    {
        $this->clientRegistry = $clientRegistry;
    }

    #[Route('/login', name: 'admin_login')]
    public function login(): Response
    {
        return $this->render('admin/security/login.html.twig');
    }

    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectGoogleStart(): Response
    {
        return $this->clientRegistry
            ->getClient('google')
            ->redirect([
                'email', 'profile'
            ]);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectGoogleCheck(): void
    {
        // This code is never executed!
    }

    #[Route('/admin/logout', name: 'admin_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
