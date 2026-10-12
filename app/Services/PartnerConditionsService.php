<?php

namespace App\Services;

use App\Models\CommissionRule;
use App\Models\OwnerProfile;
use App\Models\ServiceProviderProfile;
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

    /**
     * Commission applied to a service provider: its own rule, then the rule for all providers, then the global rule.
     */
    public function serviceProviderCommissionRule(ServiceProviderProfile $serviceProviderProfile): ?CommissionRule
    {
        $activeRules = $this->activeRules();

        return (clone $activeRules)->where('service_provider_profile_id', $serviceProviderProfile->id)->latest('id')->first()
            ?? (clone $activeRules)->where('scope', 'service_provider')->whereNull('service_provider_profile_id')->latest('id')->first()
            ?? (clone $activeRules)->where('scope', 'global')->latest('id')->first();
    }

    public function serviceProviderCommissionLabel(ServiceProviderProfile $serviceProviderProfile): ?string
    {
        return $this->commissionLabel($this->serviceProviderCommissionRule($serviceProviderProfile));
    }

    /**
     * Commission snapshot stored with a service deposit payment.
     *
     * @return array{rule_id: ?int, label: ?string, base_amount: int, amount: int}
     */
    public function serviceProviderCommission(ServiceProviderProfile $serviceProviderProfile, int $baseAmount): array
    {
        $rule = $this->serviceProviderCommissionRule($serviceProviderProfile);

        $amount = match (true) {
            $rule === null => 0,
            $rule->commission_type === 'fixed' => (int) $rule->fixed_amount,
            $rule->commission_type === 'hybrid' => (int) round($baseAmount * (float) $rule->percentage_rate / 100) + (int) $rule->fixed_amount,
            default => (int) round($baseAmount * (float) $rule->percentage_rate / 100),
        };

        return [
            'rule_id' => $rule?->id,
            'label' => $this->commissionLabel($rule),
            'base_amount' => $baseAmount,
            'amount' => min($amount, $baseAmount),
        ];
    }

    private function activeRules(): Builder
    {
        return CommissionRule::query()
            ->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    private function commissionRuleFor(?OwnerProfile $ownerProfile): ?CommissionRule
    {
        $activeRules = $this->activeRules();

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
