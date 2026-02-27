<?php

namespace App\Tests\Controller\Website;

use App\Repository\LeadRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase
{
    public function testContactPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        $this->assertResponseIsSuccessful();
    }

    public function testSubmitContactForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');
        
        $form = $crawler->selectButton('EXÉCUTER_ENVOI')->form();
        $form['contact[name]'] = 'Jonas';
        $form['contact[email]'] = 'jonas@example.com';
        $form['contact[subject]'] = 'Test Subject';
        $form['contact[message]'] = 'Ceci est un message de test suffisamment long.';
        
        $values = $form->getPhpValues();
        $values['contact']['turnstile'] = 'dummy-token';
        
        $client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());
        
        $this->assertResponseRedirects('/contact');
        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-success', 'Merci pour votre message !');
        
        $leadRepository = static::getContainer()->get(LeadRepository::class);
        $lead = $leadRepository->findOneBy(['email' => 'jonas@example.com']);
        
        $this->assertNotNull($lead);
        $this->assertEquals('Jonas', $lead->getName());
        $this->assertEquals(\App\Enum\LeadStatus::NEW, $lead->getStatus());
    }
}
