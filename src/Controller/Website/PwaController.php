<?php

namespace App\Controller\Website;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PwaController extends AbstractController
{
    #[Route('/offline', name: 'app_offline')]
    public function offline(): Response
    {
        return $this->render('pwa/offline.html.twig');
    }

    #[Route('/service-worker.js', name: 'app_sw')]
    public function serviceWorker(): Response
    {
        $swPath = $this->getParameter('kernel.project_dir').'/public/service-worker.js';
        $response = new Response(file_get_contents($swPath));
        $response->headers->set('Content-Type', 'application/javascript');

        return $response;
    }
}
