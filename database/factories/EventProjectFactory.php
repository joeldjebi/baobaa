<?php

namespace Database\Factories;

use App\Enums\EventProjectStatus;
use App\Models\EventProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventProject>
 */
class EventProjectFactory extends Factory
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
            'reference' => 'EVT-'.fake()->unique()->numerify('########'),
            'name' => fake()->sentence(4),
            'status' => EventProjectStatus::Active,
            'event_type' => fake()->randomElement(['conference', 'mariage', 'concert', 'seminaire']),
            'event_date' => fake()->dateTimeBetween('+7 days', '+180 days')->format('Y-m-d'),
            'country_code' => 'CI',
            'city' => fake()->randomElement(['Abidjan', 'Dakar', 'Cotonou']),
            'district' => fake()->citySuffix(),
            'currency' => 'XOF',
            'estimated_total_amount' => fake()->numberBetween(100000, 2000000),
            'confirmed_total_amount' => 0,
        ];
    }
}
