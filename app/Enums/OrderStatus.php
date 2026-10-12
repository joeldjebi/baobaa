<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Paid = 'paid';
    case Preparing = 'preparing';
    case Delivered = 'delivered';
    case Returned = 'returned';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Payée',
            self::Preparing => 'En préparation',
            self::Delivered => 'Livrée ou retirée',
            self::Returned => 'Matériel retourné',
            self::Completed => 'Terminée',
            self::Cancelled => 'Annulée',
        };
    }

    /**
     * Statuses in which rented quantities are still out of the provider stock.
     *
     * @return array<int, self>
     */
    public static function reservingStock(): array
    {
        return [self::Paid, self::Preparing, self::Delivered];
    }
}
