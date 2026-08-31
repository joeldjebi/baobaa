<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\PortalAccessRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortalAccessRequest>
 */
class PortalAccessRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'requested_role' => UserRole::Owner,
            'status' => 'pending',
            'applicant_type' => 'company',
            'business_name' => fake()->company(),
            'legal_name' => fake()->company(),
            'tax_identifier' => fake()->optional()->bothify('RCCM-####-??'),
            'country_code' => 'CI',
            'city' => fake()->randomElement(['Abidjan', 'Dakar', 'Cotonou']),
            'whatsapp_phone' => fake()->phoneNumber(),
            'motivation' => fake()->sentence(12),
        ];
    }
}
