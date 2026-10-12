<?php

namespace App\Enums;

enum ProductMode: string
{
    case Rental = 'rental';
    case Sale = 'sale';

    public function label(): string
    {
        return match ($this) {
            self::Rental => 'Location',
            self::Sale => 'Achat',
        };
    }
}
