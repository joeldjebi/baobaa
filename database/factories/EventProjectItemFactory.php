<?php

namespace Database\Factories;

use App\Enums\EventProjectItemStatus;
use App\Models\EventProject;
use App\Models\EventProjectItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventProjectItem>
 */
class EventProjectItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_project_id' => EventProject::factory(),
            'item_type' => 'venue_booking',
            'provider_type' => 'owner_profile',
            'provider_id' => null,
            'status' => EventProjectItemStatus::Negotiating,
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(10),
            'currency' => 'XOF',
            'quoted_amount' => fake()->numberBetween(100000, 1500000),
            'deposit_amount' => fake()->numberBetween(50000, 300000),
        ];
    }
}
