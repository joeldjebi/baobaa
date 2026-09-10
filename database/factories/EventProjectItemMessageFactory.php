<?php

namespace Database\Factories;

use App\Models\EventProjectItem;
use App\Models\EventProjectItemMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventProjectItemMessage>
 */
class EventProjectItemMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_project_item_id' => EventProjectItem::factory(),
            'sender_id' => User::factory(),
            'recipient_id' => User::factory(),
            'message' => fake()->sentence(12),
            'proposed_amount' => null,
            'currency' => 'XOF',
        ];
    }
}
