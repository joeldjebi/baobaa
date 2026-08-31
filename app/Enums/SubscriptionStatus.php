<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case PendingPayment = 'pending_payment';
    case Expired = 'expired';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';
}
