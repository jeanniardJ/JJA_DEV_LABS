<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppointmentPageTest extends WebTestCase
{
    public function testAppointmentPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/appointments');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Prendre rendez-vous');
        
        // Verify Calendar presence
        $this->assertSelectorExists('#calendar');
        
        // Verify Form presence
        $this->assertSelectorExists('form#booking-form');
        $this->assertSelectorExists('input[name="name"]');
        $this->assertSelectorExists('input[name="email"]');
        $this->assertSelectorExists('input[name="datetime"]');
        $this->assertSelectorExists('input[name="_token"]'); // CSRF Token
    }
}