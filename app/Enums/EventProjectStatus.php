<?php

namespace App\Enums;

enum EventProjectStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case PartiallyConfirmed = 'partially_confirmed';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
