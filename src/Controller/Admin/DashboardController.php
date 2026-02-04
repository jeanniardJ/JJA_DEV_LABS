<?php

namespace App\Controller\Admin;

use App\Enum\LeadStatus;
use App\Repository\LeadRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function index(Request $request, LeadRepository $leadRepository): Response
    {
        $sort = $request->query->get('sort', 'createdAt');
        $direction = $request->query->get('direction', 'DESC');

        // Fetch leads optimized with appointments count to avoid N+1
        // and grouped by status manually to keep control over sorting within groups
        $leads = $leadRepository->findAllWithAppointmentsCount($sort, $direction);
        
        $groupedLeads = [];
        foreach (LeadStatus::cases() as $status) {
            $groupedLeads[$status->value] = array_filter($leads, fn($lead) => $lead->getStatus() === $status);
        }

        return $this->render('admin/dashboard.html.twig', [
            'grouped_leads' => $groupedLeads,
            'statuses' => LeadStatus::cases(),
            'current_sort' => $sort,
            'current_direction' => $direction,
        ]);
    }
}
