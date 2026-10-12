<?php

namespace Database\Factories;

use App\Enums\VenueStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceProviderProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_provider_profile_id' => ServiceProviderProfile::factory()->verified(),
            'product_category_id' => ProductCategory::factory(),
            'name' => fake()->words(3, true),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => VenueStatus::Published,
            'is_rentable' => true,
            'is_sellable' => false,
            'rental_price_per_day' => fake()->numberBetween(1000, 50000),
            'sale_price' => null,
            'deposit_amount' => 0,
            'stock_quantity' => 10,
            'currency' => 'XOF',
            'city' => 'Abidjan',
            'published_at' => now(),
        ];
    }
}
