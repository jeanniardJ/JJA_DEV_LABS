<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LabStationsController extends AbstractController
{
    #[Route('/lab/stations', name: 'app_lab_stations')]
    public function index(): Response
    {
        return $this->render('lab_stations/index.html.twig', [
            'controller_name' => 'LabStationsController',
        ]);
    }
}
