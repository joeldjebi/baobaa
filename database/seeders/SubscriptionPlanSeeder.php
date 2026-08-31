<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'price' => 0,
                'active_venues_limit' => 1,
                'reduced_commission_rate' => null,
                'visibility_boost_level' => 0,
                'features' => ['1 espace actif', 'Commission standard'],
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 25000,
                'active_venues_limit' => 5,
                'reduced_commission_rate' => 7,
                'visibility_boost_level' => 1,
                'features' => ['5 espaces actifs', 'Commission reduite', 'Support prioritaire'],
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'price' => 75000,
                'active_venues_limit' => null,
                'reduced_commission_rate' => 5,
                'visibility_boost_level' => 2,
                'features' => ['Espaces illimites', 'Mise en avant', 'Commission premium'],
            ],
        ];

        foreach ($plans as $index => $plan) {
            SubscriptionPlan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan + ['currency' => 'XOF', 'billing_period' => 'monthly', 'is_active' => true, 'sort_order' => $index + 1],
            );
        }
    }
}
