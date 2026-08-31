<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\OwnerProfile;
use App\Models\OwnerSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OwnerSubscription>
 */
class OwnerSubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_profile_id' => OwnerProfile::factory(),
            'subscription_plan_id' => SubscriptionPlan::factory(),
            'status' => SubscriptionStatus::Active,
            'amount' => 25000,
            'currency' => 'XOF',
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addMonth()->toDateString(),
            'auto_renews' => false,
        ];
    }
}
