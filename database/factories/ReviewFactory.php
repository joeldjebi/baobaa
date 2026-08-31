<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
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
            'venue_id' => Venue::factory(),
            'client_id' => User::factory(),
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->optional()->paragraph(),
            'moderation_status' => 'pending',
        ];
    }
}
