<?php

namespace Database\Factories;

use App\Models\VenueCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VenueCategory>
 */
class VenueCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => str($name)->title()->toString(),
            'slug' => str($name)->slug()->toString(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
