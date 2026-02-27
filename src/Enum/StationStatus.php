<?php

namespace App\Enum;

enum StationStatus: string
{
    case NOMINAL = 'NOMINAL';
    case WARNING = 'WARNING';
    case RUNNING = 'RUNNING';
    case IDLE = 'IDLE';
    case CRITICAL = 'CRITICAL';
}
