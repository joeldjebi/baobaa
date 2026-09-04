<?php

namespace App\Enums;

enum EventProjectItemStatus: string
{
    case Draft = 'draft';
    case Negotiating = 'negotiating';
    case AwaitingClientConfirmation = 'awaiting_client_confirmation';
    case AwaitingProviderConfirmation = 'awaiting_provider_confirmation';
    case AwaitingPayment = 'awaiting_payment';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
}
