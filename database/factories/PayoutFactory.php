<?php

namespace Database\Factories;

use App\Models\OwnerProfile;
use App\Models\Payout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payout>
 */
class PayoutFactory extends Factory
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
            'reference' => 'PO-'.fake()->unique()->numerify('########'),
            'status' => 'pending',
            'gross_amount' => 100000,
            'commission_amount' => 10000,
            'net_amount' => 90000,
            'currency' => 'XOF',
            'scheduled_on' => now()->addDays(3)->toDateString(),
        ];
    }
}
