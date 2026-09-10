<?php

namespace Database\Factories;

use App\Models\EventProject;
use App\Models\EventProjectMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventProjectMessage>
 */
class EventProjectMessageFactory extends Factory
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
            'provider_type' => 'service_provider_profile',
            'provider_id' => null,
            'sender_id' => User::factory(),
            'recipient_id' => User::factory(),
            'message' => fake()->sentence(12),
            'proposed_amount' => null,
            'currency' => 'XOF',
        ];
    }
}
