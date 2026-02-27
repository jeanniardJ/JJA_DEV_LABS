<?php

namespace App\Service;

use App\Repository\AppointmentRepository;

class AppointmentService
{
    public function __construct(
        private AppointmentRepository $appointmentRepository
    ) {}

    /** @return list<array{start: string, end: string, datetime: string}> */
    public function getAvailableSlots(\DateTimeImmutable $day): array
    {
        $existingAppointments = $this->appointmentRepository->findByDay($day);
        
        // Optimize lookup by indexing booked times
        $bookedSlots = [];
        foreach ($existingAppointments as $appointment) {
            $bookedSlots[$appointment->getStartsAt()->getTimestamp()] = true;
        }
        
        $slots = [];
        $startTime = $day->setTime(9, 0, 0);
        $endTime = $day->setTime(18, 0, 0);
        
        $current = $startTime;
        while ($current < $endTime) {
            // Check availability using timestamp for precise comparison
            if (!isset($bookedSlots[$current->getTimestamp()])) {
                $slots[] = [
                    'start' => $current->format('H:i'),
                    'end' => $current->modify('+15 minutes')->format('H:i'),
                    'datetime' => $current->format(\DateTimeInterface::ATOM)
                ];
            }
            
            $current = $current->modify('+15 minutes');
        }
        
        return $slots;
    }

    // Removed isBooked as it is no longer used
}
