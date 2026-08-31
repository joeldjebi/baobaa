<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\OwnerProfile;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $venue = Venue::factory();

        return [
            'client_id' => User::factory(),
            'owner_profile_id' => OwnerProfile::factory(),
            'venue_id' => $venue,
            'reference' => 'BKB-'.fake()->unique()->numerify('########'),
            'status' => BookingStatus::PendingPayment,
            'booking_mode' => 'request',
            'event_type' => fake()->randomElement(['conference', 'wedding', 'concert', 'seminar']),
            'event_date' => fake()->dateTimeBetween('+7 days', '+120 days')->format('Y-m-d'),
            'starts_at' => '10:00:00',
            'ends_at' => '18:00:00',
            'guests_count' => fake()->numberBetween(20, 250),
            'currency' => 'XOF',
            'total_amount' => fake()->numberBetween(100000, 1500000),
            'reservation_amount' => fake()->numberBetween(50000, 300000),
        ];
    }
}
