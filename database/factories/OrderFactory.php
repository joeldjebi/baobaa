<?php

namespace Database\Factories;

use App\Enums\DepositStatus;
use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => 'CMD-'.fake()->unique()->bothify('######??'),
            'client_id' => User::factory(),
            'service_provider_profile_id' => ServiceProviderProfile::factory()->verified(),
            'status' => OrderStatus::Paid,
            'fulfillment_method' => FulfillmentMethod::Pickup,
            'items_total' => 20000,
            'delivery_fee' => 0,
            'deposit_total' => 0,
            'total_amount' => 20000,
            'commission_amount' => 0,
            'currency' => 'XOF',
            'payment_method' => 'wave',
            'deposit_status' => DepositStatus::None,
            'paid_at' => now(),
        ];
    }
}
