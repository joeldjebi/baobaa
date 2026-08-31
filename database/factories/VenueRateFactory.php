<?php

namespace Database\Factories;

use App\Models\Venue;
use App\Models\VenueRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VenueRate>
 */
class VenueRateFactory extends Factory
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
            'name' => 'Forfait journee',
            'rate_type' => 'day',
            'price' => fake()->numberBetween(50000, 1000000),
            'currency' => 'XOF',
            'min_hours' => 4,
            'max_hours' => 12,
            'min_guests' => 1,
            'max_guests' => 300,
            'is_default' => true,
            'is_active' => true,
        ];
    }
}
