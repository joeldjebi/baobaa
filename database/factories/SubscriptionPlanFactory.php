<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => str($name)->title()->toString(),
            'slug' => str($name)->slug()->toString(),
            'price' => fake()->numberBetween(5000, 100000),
            'currency' => 'XOF',
            'billing_period' => 'monthly',
            'active_venues_limit' => fake()->numberBetween(1, 10),
            'features' => ['publication', 'dashboard'],
            'is_active' => true,
        ];
    }
}
