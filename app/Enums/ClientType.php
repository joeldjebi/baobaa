<?php

namespace App\Enums;

enum ClientType: string
{
    case Individual = 'individual';
    case Company = 'company';
    case Association = 'association';
    case Institution = 'institution';
    case EventAgency = 'event_agency';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Particulier',
            self::Company => 'Entreprise',
            self::Association => 'Association ou ONG',
            self::Institution => 'Institution publique',
            self::EventAgency => 'Agence événementielle',
        };
    }

    public function requiresOrganizationName(): bool
    {
        return $this !== self::Individual;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
