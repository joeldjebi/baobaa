<?php

namespace Database\Factories;

use App\Models\SponsorshipPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SponsorshipPlan>
 */
class SponsorshipPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Boost '.fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'placement' => fake()->randomElement(['home_featured', 'catalog_top', 'category_boost']),
            'price' => fake()->randomElement([500, 2500, 5000, 15000]),
            'currency' => 'XOF',
            'duration_days' => fake()->randomElement([1, 3, 7, 14]),
            'description' => fake()->sentence(),
            'features' => ['Mise en avant catalogue', 'Badge sponsorisé'],
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
