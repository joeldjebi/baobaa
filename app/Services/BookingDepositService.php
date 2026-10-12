<?php

namespace App\Services;

use App\Models\OwnerDepositRule;
use App\Models\OwnerProfile;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;

class BookingDepositService
{
    public function amountFor(Venue $venue, int $totalAmount): int
    {
        $rule = $this->activeRuleFor($venue->ownerProfile);

        if (! $rule) {
            return (int) ($venue->reservation_amount ?: $totalAmount);
        }

        $amount = match ($rule->deposit_type) {
            'fixed' => (int) $rule->fixed_amount,
            default => (int) ceil($totalAmount * ((float) $rule->percentage_rate / 100)),
        };

        $amount = max($amount, (int) $rule->minimum_amount);

        if ($rule->maximum_amount) {
            $amount = min($amount, (int) $rule->maximum_amount);
        }

        return min($amount, $totalAmount);
    }

    /**
     * Plain-language explanation of how the deposit is computed for this venue.
     */
    public function describe(Venue $venue): string
    {
        $rule = $this->activeRuleFor($venue->ownerProfile);
        $format = fn (int $amount): string => number_format($amount, 0, ',', ' ').' '.$venue->currency;

        if (! $rule) {
            return $venue->reservation_amount
                ? 'Acompte fixe de '.$format((int) $venue->reservation_amount).' pour bloquer la date.'
                : 'Le montant total est demandé pour bloquer la date.';
        }

        $description = $rule->deposit_type === 'fixed'
            ? 'Acompte fixe de '.$format((int) $rule->fixed_amount)
            : 'Acompte de '.rtrim(rtrim(number_format((float) $rule->percentage_rate, 2, ',', ''), '0'), ',')."\u{00A0}% du montant total";

        if ((int) $rule->minimum_amount > 0) {
            $description .= ', minimum '.$format((int) $rule->minimum_amount);
        }

        if ($rule->maximum_amount) {
            $description .= ', plafonné à '.$format((int) $rule->maximum_amount);
        }

        return $description.'. Le solde est réglé selon la proforma validée.';
    }

    public function activeRuleFor(?OwnerProfile $ownerProfile): ?OwnerDepositRule
    {
        if (! $ownerProfile) {
            return null;
        }

        return $ownerProfile->depositRules()
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->latest('starts_at')
            ->latest('id')
            ->first();
    }
}
