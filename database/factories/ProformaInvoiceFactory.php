<?php

namespace Database\Factories;

use App\Enums\ProformaInvoiceStatus;
use App\Models\Booking;
use App\Models\ProformaInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProformaInvoice>
 */
class ProformaInvoiceFactory extends Factory
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
            'reference' => 'PRO-'.fake()->unique()->numerify('########'),
            'status' => ProformaInvoiceStatus::Sent,
            'currency' => 'XOF',
            'subtotal_amount' => 500000,
            'deposit_amount' => 150000,
            'service_fee_amount' => 0,
            'total_amount' => 500000,
        ];
    }
}
