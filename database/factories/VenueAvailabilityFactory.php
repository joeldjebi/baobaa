<?php

namespace Database\Factories;

use App\Models\Venue;
use App\Models\VenueAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VenueAvailability>
 */
class VenueAvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venue_id' => Venue::factory(),
            'available_date' => fake()->dateTimeBetween('+1 day', '+90 days')->format('Y-m-d'),
            'starts_at' => '09:00:00',
            'ends_at' => '18:00:00',
            'status' => 'available',
        ];
    }
}
