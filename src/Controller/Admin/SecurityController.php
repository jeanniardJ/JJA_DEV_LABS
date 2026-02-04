<?php

namespace App\Controller\Admin;

use KnpUniversity\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    #[Route('/admin/login', name: 'admin_login')]
    public function login(): Response
    {
        return $this->render('admin/security/login.html.twig');
    }

    #[Route('/admin/connect/google', name: 'connect_google_start')]
    public function connectGoogleStart(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect([
                'email', 'profile'
            ]);
    }

    #[Route('/admin/connect/google/check', name: 'connect_google_check')]
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
