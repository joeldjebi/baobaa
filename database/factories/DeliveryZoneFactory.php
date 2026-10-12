<?php

namespace Database\Factories;

use App\Models\DeliveryZone;
use App\Models\ServiceProviderProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryZone>
 */
class DeliveryZoneFactory extends Factory
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
            'city' => 'Abidjan',
            'fee' => 5000,
            'currency' => 'XOF',
            'is_active' => true,
        ];
    }
}
