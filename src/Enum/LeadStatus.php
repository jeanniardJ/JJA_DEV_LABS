<?php

namespace App\Enum;

enum LeadStatus: string
{
    case NEW = 'Nouveau';
    case APPOINTMENT = 'RDV';
    case IN_PROGRESS = 'En cours';
    case WON = 'Gagné';
    case LOST = 'Perdu';

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'Nouveau',
            self::APPOINTMENT => 'RDV',
            self::IN_PROGRESS => 'En cours',
            self::WON => 'Gagné',
            self::LOST => 'Perdu',
        };
    }
}
