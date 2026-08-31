<?php

namespace Database\Factories;

use App\Models\Venue;
use App\Models\VenueMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VenueMedia>
 */
class VenueMediaFactory extends Factory
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
            'type' => 'image',
            'disk' => 'public',
            'path' => 'venues/'.fake()->uuid().'.jpg',
            'alt_text' => fake()->sentence(6),
            'is_primary' => false,
            'sort_order' => fake()->numberBetween(1, 10),
            'moderation_status' => 'pending',
        ];
    }
}
