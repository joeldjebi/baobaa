<?php

namespace App\Enums;

enum ProformaInvoiceStatus: string
{
    case Sent = 'sent';
    case AcceptedByClient = 'accepted_by_client';
    case AcceptedByOwner = 'accepted_by_owner';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
}
