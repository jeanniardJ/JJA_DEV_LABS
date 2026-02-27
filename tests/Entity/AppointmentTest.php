<?php

namespace App\Tests\Entity;

use App\Entity\Appointment;
use App\Entity\Lead;
use PHPUnit\Framework\TestCase;

class AppointmentTest extends TestCase
{
    public function testAppointmentEntity(): void
    {
        $appointment = new Appointment();
        $lead = new Lead();
        
        $startsAt = new \DateTimeImmutable('2026-02-10 10:00:00');
        $endsAt = new \DateTimeImmutable('2026-02-10 10:15:00');
        
        $appointment->setStartsAt($startsAt);
        $appointment->setEndsAt($endsAt);
        $appointment->setStatus('confirmed');
        $appointment->setLead($lead);

        $this->assertEquals($startsAt, $appointment->getStartsAt());
        $this->assertEquals($endsAt, $appointment->getEndsAt());
        $this->assertEquals('confirmed', $appointment->getStatus());
        $this->assertSame($lead, $appointment->getLead());
    }
}
