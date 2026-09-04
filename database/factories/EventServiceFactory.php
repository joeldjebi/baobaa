<?php

namespace Database\Factories;

use App\Enums\VenueStatus;
use App\Models\EventService;
use App\Models\EventServiceType;
use App\Models\ServiceProviderProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventService>
 */
class EventServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_provider_profile_id' => ServiceProviderProfile::factory(),
            'event_service_type_id' => EventServiceType::factory(),
            'name' => fake()->sentence(3),
            'short_description' => fake()->sentence(10),
            'description' => fake()->paragraphs(2, true),
            'status' => VenueStatus::Draft,
            'country_code' => 'CI',
            'city' => fake()->randomElement(['Abidjan', 'Dakar', 'Cotonou']),
            'district' => fake()->citySuffix(),
            'service_area' => 'Intervention urbaine et périphérie',
            'pricing_unit' => fake()->randomElement(['event', 'day', 'hour']),
            'currency' => 'XOF',
            'starting_price' => fake()->numberBetween(50000, 1000000),
            'deposit_amount' => fake()->numberBetween(25000, 250000),
            'attributes' => [
                'Équipe incluse' => 'Oui',
                'Installation' => 'Avant événement',
            ],
        ];
    }
}
