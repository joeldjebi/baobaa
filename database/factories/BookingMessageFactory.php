<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingMessage>
 */
class BookingMessageFactory extends Factory
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
            'sender_id' => User::factory(),
            'recipient_id' => User::factory(),
            'message' => fake()->sentence(14),
            'proposed_amount' => fake()->optional()->numberBetween(50000, 500000),
            'currency' => 'XOF',
        ];
    }
}
