<?php

namespace App\Tests\Functional\Admin;

use App\Entity\Lead;
use App\Entity\User;
use App\Enum\LeadStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AiAssistantControllerTest extends WebTestCase
{
    public function testGenerateDraftRequiresAdmin(): void
    {
        $client = static::createClient();
        $client->request('POST', '/admin/ai/generate/1');
        $this->assertResponseRedirects('/admin/login');
    }

    public function testGenerateDraftSuccess(): void
    {
        $client = static::createClient([], ['REMOTE_ADDR' => '127.0.0.1']);
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');

        // Setup test data
        $admin = $em->getRepository(User::class)->findOneBy(['email' => 'admin@test.com']) ?? new User();
        if (!$admin->getId()) {
            $admin->setEmail('admin@test.com');
            $admin->setRoles(['ROLE_ADMIN']);
            $em->persist($admin);
        }

        $lead = new Lead();
        $lead->setName('John Doe');
        $lead->setEmail('john@example.com');
        $lead->setSubject('Demande d\'info');
        $lead->setMessage('Pouvez-vous m\'aider ?');
        $lead->setStatus(LeadStatus::NEW);
        $em->persist($lead);
        $em->flush();

        $client->loginUser($admin, 'admin');
        
        // Mock GeminiAIService
        $geminiMock = $this->createMock(\App\Service\GeminiAIService::class);
        $geminiMock->method('generateResponse')->willReturn('Brouillon mocké');
        $container->set(\App\Service\GeminiAIService::class, $geminiMock);

        $client->request('POST', '/admin/ai/generate/' . $lead->getId());

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('draft', $data);
    }
}
