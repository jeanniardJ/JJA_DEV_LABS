<?php

namespace App\Enum;

enum LeadStatus: string
{
    case NEW = 'Nouveau';
    case APPOINTMENT_PENDING = 'RDV en attente';
    case APPOINTMENT_CONFIRMED = 'RDV confirmé';
    case APPOINTMENT_REFUSED = 'RDV refusé';
    case IN_PROGRESS = 'En cours';
    case WON = 'Gagné';
    case LOST = 'Perdu';

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'Nouveau',
            self::APPOINTMENT_PENDING => 'RDV à confirmer',
            self::APPOINTMENT_CONFIRMED => 'RDV confirmé',
            self::APPOINTMENT_REFUSED => 'RDV refusé',
            self::IN_PROGRESS => 'Audit en cours',
            self::WON => 'Projet gagné',
            self::LOST => 'Perdu',
        };
    }
}
