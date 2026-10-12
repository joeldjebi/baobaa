<?php

namespace Database\Factories;

use App\Enums\ProductMode;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => User::factory(),
            'product_id' => Product::factory(),
            'mode' => ProductMode::Rental,
            'quantity' => 1,
            'rental_starts_on' => now()->addDays(10)->toDateString(),
            'rental_ends_on' => now()->addDays(11)->toDateString(),
        ];
    }
}
