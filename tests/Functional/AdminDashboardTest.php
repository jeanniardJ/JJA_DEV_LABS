<?php

namespace App\Tests\Functional;

use App\Entity\Lead;
use App\Enum\LeadStatus;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminDashboardTest extends WebTestCase
{
    public function testDashboardRequiresAdminRole(): void
    {
        $client = static::createClient([], ['REMOTE_ADDR' => '127.0.0.1']);
        
        // No user logged in
        $client->request('GET', '/admin/dashboard');
        $this->assertResponseRedirects('/admin/login');
    }

    public function testDashboardShowsLeads(): void
    {
        $client = static::createClient([], ['REMOTE_ADDR' => '127.0.0.1']);
        $container = $client->getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');

        // Clear leads and appointments to have a clean state
        $entityManager->createQuery('DELETE FROM App\Entity\Appointment')->execute();
        $entityManager->createQuery('DELETE FROM App\Entity\Lead')->execute();

        // Create a test admin user if not exists
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@test.com']);
        if (!$user) {
            $user = new User();
            $user->setEmail('admin@test.com');
            $user->setRoles(['ROLE_ADMIN']);
            $entityManager->persist($user);
        }

        // Create a test lead
        $lead = new Lead();
        $lead->setName('Test Lead');
        $lead->setEmail('lead@test.com');
        $lead->setSubject('Testing Kanban');
        $lead->setMessage('Hello');
        $lead->setStatus(LeadStatus::NEW);
        $entityManager->persist($lead);
        
        $entityManager->flush();

        $client->loginUser($user, 'admin');
        $client->request('GET', '/admin/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Nouveau');
        $this->assertSelectorTextContains('.kanban-card h3', 'Test Lead');
    }

    public function testUpdateLeadStatusApi(): void
    {
        $client = static::createClient([], ['REMOTE_ADDR' => '127.0.0.1']);
        $container = $client->getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@test.com']);
        $lead = $entityManager->getRepository(Lead::class)->findOneBy(['email' => 'lead@test.com']);

        $client->loginUser($user, 'admin');
        
        $client->request('PATCH', '/api/admin/leads/' . $lead->getId() . '/status', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['status' => 'RDV']));

        $this->assertResponseIsSuccessful();
        
        // Refresh lead from DB
        $entityManager->refresh($lead);
        $this->assertEquals(LeadStatus::APPOINTMENT, $lead->getStatus());
    }
}
