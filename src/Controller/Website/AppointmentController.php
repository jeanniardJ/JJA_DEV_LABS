<?php

namespace App\Controller\Website;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\Request;
// ... existing imports

class AppointmentController extends AbstractController
{
    #[Route('/appointments', name: 'app_website_appointments')]
    public function index(Request $request): Response
    {
        $data = [];
        if ($request->query->get('context') === 'scan') {
            $site = htmlspecialchars($request->query->get('site', ''), ENT_QUOTES, 'UTF-8');
            $severity = htmlspecialchars($request->query->get('severity', ''), ENT_QUOTES, 'UTF-8');
            $count = (int) $request->query->get('count', 0);
            
            $data['subject'] = sprintf('Analyse de %s (%s vulnérabilités, max: %s)', $site, $count, $severity);
        }

        $form = $this->createForm(\App\Form\AppointmentBookingType::class, $data);
        return $this->render('website/appointments.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
