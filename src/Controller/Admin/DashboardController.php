<?php

namespace App\Controller\Admin;

use App\Enum\LeadStatus;
use App\Repository\LeadRepository;
use App\Repository\UserRepository;
use App\Repository\AppointmentRepository;
use App\Repository\PushSubscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        Request $request, 
        LeadRepository $leadRepository,
        UserRepository $userRepository,
        AppointmentRepository $appointmentRepository,
        PushSubscriptionRepository $pushSubscriptionRepository,
        ParameterBagInterface $params
    ): Response {
        $sort = $request->query->get('sort', 'createdAt');
        $direction = $request->query->get('direction', 'DESC');

        // Fetch leads optimized with appointments count to avoid N+1
        // and grouped by status manually to keep control over sorting within groups
        $leads = $leadRepository->findAllWithAppointmentsCount($sort, $direction);
        
        $groupedLeads = [];
        foreach (LeadStatus::cases() as $status) {
            $groupedLeads[$status->value] = array_filter($leads, fn($lead) => $lead->getStatus() === $status);
        }

        // Stats for the new admin mockup
        $stats = [
            'total_users' => $userRepository->count([]),
            'total_leads' => count($leads),
            'today_appointments' => count($appointmentRepository->findByDay(new \DateTimeImmutable())),
            'active_push' => $pushSubscriptionRepository->count([]),
            'memory_usage' => number_format(memory_get_usage() / 1024 / 1024, 2),
        ];

        return $this->render('admin/dashboard.html.twig', [
            'grouped_leads' => $groupedLeads,
            'statuses' => LeadStatus::cases(),
            'current_sort' => $sort,
            'current_direction' => $direction,
            'stats' => $stats,
            'vapid_public_key' => $_ENV['VAPID_PUBLIC_KEY'] ?? '',
        ]);
    }
}
