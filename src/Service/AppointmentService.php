<?php

namespace App\Service;

use App\Repository\AppointmentRepository;
use App\Repository\AppointmentAvailabilityRepository;

class AppointmentService
{
    public function __construct(
        private AppointmentRepository $appointmentRepository,
        private AppointmentAvailabilityRepository $availabilityRepository
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
        
        $dayOfWeek = (int) $day->format('w');
        $availabilities = $this->availabilityRepository->findByDayOfWeek($dayOfWeek);

        if (empty($availabilities)) {
            // Default behavior if no availability is defined for this day
            // You can choose to return empty or a default range.
            // Let's keep it safe: no configuration = no slots.
            return [];
        }
        
        $slots = [];
        
        foreach ($availabilities as $availability) {
            $current = $day->setTime(
                (int) $availability->getStartTime()->format('H'),
                (int) $availability->getStartTime()->format('i'),
                0
            );
            $endTime = $day->setTime(
                (int) $availability->getEndTime()->format('H'),
                (int) $availability->getEndTime()->format('i'),
                0
            );
            $duration = $availability->getSlotDuration();

            while ($current < $endTime) {
                if (!isset($bookedSlots[$current->getTimestamp()])) {
                    $slots[] = [
                        'start' => $current->format('H:i'),
                        'end' => $current->modify('+' . $duration . ' minutes')->format('H:i'),
                        'datetime' => $current->format(\DateTimeInterface::ATOM)
                    ];
                }
                
                $current = $current->modify('+' . $duration . ' minutes');
            }
        }
        
        // Sort slots by time just in case of multiple overlapping availabilities
        usort($slots, fn($a, $b) => $a['datetime'] <=> $b['datetime']);
        
        return $slots;
    }

    // Removed isBooked as it is no longer used
}
