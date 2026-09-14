<?php

namespace App\Enums;

enum AvailabilityHoldStatus: string
{
    case Active = 'active';
    case Converted = 'converted';
    case Expired = 'expired';
    case Released = 'released';
}
