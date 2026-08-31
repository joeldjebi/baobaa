<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
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
            'payer_id' => User::factory(),
            'reference' => 'PAY-'.fake()->unique()->numerify('########'),
            'provider' => fake()->randomElement(['wave', 'orange_money', 'card']),
            'payment_method' => fake()->randomElement(['mobile_money', 'card']),
            'status' => PaymentStatus::Initiated,
            'amount' => fake()->numberBetween(50000, 300000),
            'currency' => 'XOF',
        ];
    }
}
