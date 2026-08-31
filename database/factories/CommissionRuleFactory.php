<?php

namespace Database\Factories;

use App\Models\CommissionRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommissionRule>
 */
class CommissionRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Commission standard',
            'scope' => 'global',
            'commission_type' => 'percentage',
            'percentage_rate' => 10,
            'currency' => 'XOF',
            'is_active' => true,
            'starts_at' => now(),
        ];
    }
}
