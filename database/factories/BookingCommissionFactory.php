<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCommission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCommission>
 */
class BookingCommissionFactory extends Factory
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
            'commission_type' => 'percentage',
            'percentage_rate' => 10,
            'base_amount' => 100000,
            'commission_amount' => 10000,
            'currency' => 'XOF',
            'snapshot' => ['rule' => 'standard'],
        ];
    }
}
