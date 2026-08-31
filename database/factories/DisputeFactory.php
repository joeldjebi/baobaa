<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dispute>
 */
class DisputeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'opened_by' => User::factory(),
            'type' => fake()->randomElement(['payment', 'availability', 'venue_quality']),
            'status' => 'open',
            'description' => fake()->paragraph(),
            'currency' => 'XOF',
        ];
    }
}
