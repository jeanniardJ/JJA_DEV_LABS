<?php

namespace App\Tests\Entity;

use App\Entity\Lead;
use App\Enum\LeadStatus;
use PHPUnit\Framework\TestCase;

class LeadTest extends TestCase
{
    public function testLeadEntity(): void
    {
        $lead = new Lead();
        
        $lead->setName('Jonas');
        $lead->setEmail('jonas@example.com');
        $lead->setSubject('Demande de devis');
        $lead->setMessage('Bonjour, je souhaite un devis.');

        $this->assertEquals('Jonas', $lead->getName());
        $this->assertEquals('jonas@example.com', $lead->getEmail());
        $this->assertEquals('Demande de devis', $lead->getSubject());
        $this->assertEquals('Bonjour, je souhaite un devis.', $lead->getMessage());
        $this->assertEquals(LeadStatus::NEW, $lead->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $lead->getCreatedAt());
    }
}
