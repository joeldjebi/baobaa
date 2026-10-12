<?php

namespace App\Enums;

enum DepositStatus: string
{
    case None = 'none';
    case Held = 'held';
    case Released = 'released';
    case Retained = 'retained';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Sans caution',
            self::Held => 'Caution bloquée',
            self::Released => 'Caution restituée',
            self::Retained => 'Caution retenue',
        };
    }
}
