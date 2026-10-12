<?php

namespace App\Enums;

enum PriceUnit: string
{
    case Event = 'event';
    case Hour = 'hour';
    case HalfDay = 'half_day';
    case Day = 'day';

    public function label(): string
    {
        return match ($this) {
            self::Event => 'par événement',
            self::Hour => 'par heure',
            self::HalfDay => 'par demi-journée',
            self::Day => 'par journée',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $unit): array => [$unit->value => ucfirst($unit->label())])
            ->all();
    }
}
