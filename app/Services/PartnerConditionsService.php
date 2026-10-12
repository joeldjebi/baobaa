<?php

namespace App\Services;

use App\Models\CommissionRule;
use App\Models\OwnerProfile;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Builder;

/**
 * Commission and subscription conditions shown to venue owners, computed from the rules managed by the SAP.
 */
class PartnerConditionsService
{
    /**
     * @return array{commission: ?string, plans: array<int, array{name: string, price: string, venues: string, commission: ?string, features: array<int, string>}>}
     */
    public function summary(?OwnerProfile $ownerProfile = null): array
    {
        return [
            'commission' => $this->commissionLabel($this->commissionRuleFor($ownerProfile)),
            'plans' => SubscriptionPlan::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('price')
                ->get()
                ->map(fn (SubscriptionPlan $plan): array => [
                    'name' => $plan->name,
                    'price' => (int) $plan->price === 0
                        ? 'Gratuit'
                        : number_format((int) $plan->price, 0, ',', ' ').' '.$plan->currency.' / '.($plan->billing_period === 'yearly' ? 'an' : 'mois'),
                    'venues' => $plan->active_venues_limit
                        ? $plan->active_venues_limit.' espace'.($plan->active_venues_limit > 1 ? 's' : '').' actif'.($plan->active_venues_limit > 1 ? 's' : '')
                        : 'Espaces illimités',
                    'commission' => $plan->reduced_commission_rate !== null
                        ? 'Commission réduite à '.$this->formatRate((float) $plan->reduced_commission_rate).' %'
                        : null,
                    'features' => array_values($plan->features ?? []),
                ])
                ->all(),
        ];
    }

    private function commissionRuleFor(?OwnerProfile $ownerProfile): ?CommissionRule
    {
        $activeRules = CommissionRule::query()
            ->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));

        if ($ownerProfile) {
            $ownerRule = (clone $activeRules)->where('owner_profile_id', $ownerProfile->id)->latest('id')->first();

            if ($ownerRule) {
                return $ownerRule;
            }
        }

        return (clone $activeRules)->where('scope', 'global')->latest('id')->first();
    }

    private function commissionLabel(?CommissionRule $rule): ?string
    {
        if (! $rule) {
            return null;
        }

        return $rule->commission_type === 'fixed'
            ? number_format((int) $rule->fixed_amount, 0, ',', ' ').' '.$rule->currency.' par réservation confirmée'
            : $this->formatRate((float) $rule->percentage_rate).' % du montant de chaque réservation confirmée';
    }

    private function formatRate(float $rate): string
    {
        return rtrim(rtrim(number_format($rate, 2, ',', ''), '0'), ',');
    }
}
