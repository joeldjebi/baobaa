<?php

namespace Database\Factories;

use App\Enums\ProductMode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'mode' => ProductMode::Rental,
            'quantity' => 1,
            'unit_price' => 10000,
            'rental_days' => 2,
            'rental_starts_on' => now()->addDays(10)->toDateString(),
            'rental_ends_on' => now()->addDays(11)->toDateString(),
            'line_total' => 20000,
            'deposit_amount' => 0,
        ];
    }
}
