<?php

namespace App\Enums;

enum FulfillmentMethod: string
{
    case Pickup = 'pickup';
    case Delivery = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::Pickup => 'Retrait chez le prestataire',
            self::Delivery => 'Livraison',
        };
    }
}
