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
            $site = $request->query->get('site', '');
            $severity = $request->query->get('severity', 'low');
            $count = (int) $request->query->get('count', 0);
            
            // Strict Validation
            if (filter_var($site, FILTER_VALIDATE_URL)) {
                $allowedSeverities = ['low', 'medium', 'high', 'critical'];
                $safeSeverity = in_array($severity, $allowedSeverities) ? $severity : 'low';
                
                $data['subject'] = sprintf('Analyse de %s (%d vulnérabilité%s, max: %s)', 
                    htmlspecialchars($site, ENT_QUOTES, 'UTF-8'), 
                    $count, 
                    $count > 1 ? 's' : '',
                    $safeSeverity
                );
            }
        }

        $form = $this->createForm(\App\Form\AppointmentBookingType::class, $data);
        return $this->render('website/appointments.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
