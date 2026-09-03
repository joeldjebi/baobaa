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
