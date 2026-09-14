<?php

namespace Database\Factories;

use App\Enums\AvailabilityHoldStatus;
use App\Models\AvailabilityHold;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AvailabilityHold>
 */
class AvailabilityHoldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'holdable_type' => Venue::class,
            'holdable_id' => Venue::factory(),
            'client_id' => User::factory(),
            'session_id' => fake()->uuid(),
            'status' => AvailabilityHoldStatus::Active,
            'starts_at' => now()->addDays(14)->setTime(9, 0),
            'ends_at' => now()->addDays(14)->setTime(18, 0),
            'expires_at' => now()->addMinutes(10),
        ];
    }
}
