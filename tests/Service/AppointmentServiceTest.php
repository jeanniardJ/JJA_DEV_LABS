<?php

namespace App\Tests\Service;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Service\AppointmentService;
use PHPUnit\Framework\TestCase;

class AppointmentServiceTest extends TestCase
{
    public function testGetAvailableSlots(): void
    {
        $appointmentRepository = $this->createMock(AppointmentRepository::class);
        
        $day = new \DateTimeImmutable('2026-02-10');
        
        // Mock existing appointments
        $existingAppointment = new Appointment();
        $existingAppointment->setStartsAt($day->setTime(10, 0, 0));
        $existingAppointment->setEndsAt($day->setTime(10, 15, 0));
        
        $appointmentRepository->method('findByDay')->willReturn([$existingAppointment]);
        
        $service = new AppointmentService($appointmentRepository);
        $slots = $service->getAvailableSlots($day);
        
        // Between 9h and 18h, there are 9 hours * 4 slots/hour = 36 slots.
        // One is taken, so 35 should be available.
        $this->assertCount(35, $slots);
        
        // Check that 10:00 is NOT in slots
        foreach ($slots as $slot) {
            $this->assertNotEquals('10:00', $slot['start']);
        }
        
        // Check that 09:00 IS in slots
        $this->assertEquals('09:00', $slots[0]['start']);
    }
}
