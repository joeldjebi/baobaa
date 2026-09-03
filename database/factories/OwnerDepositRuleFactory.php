<?php

namespace Database\Factories;

use App\Models\OwnerDepositRule;
use App\Models\OwnerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OwnerDepositRule>
 */
class OwnerDepositRuleFactory extends Factory
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
            'name' => 'Acompte '.fake()->randomElement(['standard', 'premium', 'événementiel']),
            'deposit_type' => fake()->randomElement(['percentage', 'fixed']),
            'percentage_rate' => 30,
            'fixed_amount' => null,
            'minimum_amount' => 25000,
            'maximum_amount' => null,
            'currency' => 'XOF',
            'is_active' => true,
            'starts_at' => now()->subDay(),
        ];
    }
}
