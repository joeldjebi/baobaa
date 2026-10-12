<?php

namespace Database\Factories;

use App\Models\EventService;
use App\Models\EventServiceMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventServiceMedia>
 */
class EventServiceMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_service_id' => EventService::factory(),
            'disk' => 'public',
            'path' => 'event-services/'.fake()->uuid().'.jpg',
            'alt_text' => fake()->sentence(4),
            'is_primary' => false,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
