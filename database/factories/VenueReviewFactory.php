<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VenueReview>
 */
class VenueReviewFactory extends Factory
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
            'client_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'rating' => fake()->numberBetween(4, 5),
            'title' => fake()->sentence(4),
            'comment' => fake()->paragraph(),
            'status' => 'pending',
        ];
    }
}
