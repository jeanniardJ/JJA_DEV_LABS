<?php

namespace App\Tests\Controller\Api;

use App\Entity\Lead;
use App\Repository\LeadRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppointmentControllerTest extends WebTestCase
{
    public function testGetAvailableSlots(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/appointments/slots?date=2026-02-10');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testBookAppointment(): void
    {
        $client = static::createClient();
        
        // 1. Get CSRF token
        $crawler = $client->request('GET', '/appointments');
        $token = $crawler->filter('input[name="_token"]')->attr('value');
        
        $uniqueTime = time() . rand(1000, 9999);
        $day = rand(1, 28);
        $hour = rand(9, 17);
        $minute = [0, 15, 30, 45][rand(0, 3)];
        $datetime = sprintf('2030-01-%02dT%02d:%02d:00+00:00', $day, $hour, $minute);
        $email = 'jonas-' . $uniqueTime . '@test.com';

        $client->request('POST', '/api/appointments/book', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'datetime' => $datetime,
            'name' => 'Jonas Test',
            'email' => $email,
            '_token' => $token
        ]));

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('success', $data['status']);

        // Clear EntityManager to force reload from DB
        static::getContainer()->get('doctrine')->getManager()->clear();

        // Verify Lead Creation
        $leadRepository = static::getContainer()->get(LeadRepository::class);
        $lead = $leadRepository->findOneBy(['email' => $email]);
        
        $this->assertNotNull($lead, 'Lead should have been created');
        $this->assertEquals('Jonas Test', $lead->getName());
        $this->assertEquals('Prise de rendez-vous', $lead->getSubject());
        
        // Verify Appointment Link
        // Since we cleared EM, lazy loading should fetch the appointments from DB
        $this->assertCount(1, $lead->getAppointments());
        $this->assertEquals($datetime, $lead->getAppointments()[0]->getStartsAt()->format(\DateTimeInterface::ATOM));
    }

    public function testBookAppointmentConflict(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/appointments');
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $uniqueTime = time() . rand(1000, 9999);
        // Use a random year 3000+ to ensure no conflict with previous runs
        $year = rand(3000, 3999);
        $datetime = sprintf('%d-02-10T%d:00:00+00:00', $year, rand(9, 17));

        // First booking
        $client->request('POST', '/api/appointments/book', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'datetime' => $datetime,
            'name' => 'Jonas First',
            'email' => 'first-' . $uniqueTime . '@test.com',
            '_token' => $token
        ]));
        $this->assertResponseIsSuccessful();

        // Second booking (conflict)
        $client->request('POST', '/api/appointments/book', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'datetime' => $datetime,
            'name' => 'Jonas Second',
            'email' => 'second-' . $uniqueTime . '@test.com',
            '_token' => $token
        ]));
        
        $this->assertEquals(409, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Slot already booked', $data['error']);
    }
}
