<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\OwnerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OwnerProfile>
 */
class OwnerProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => UserRole::Owner]),
            'public_uuid' => (string) Str::uuid(),
            'owner_type' => fake()->randomElement(['individual', 'company', 'organization']),
            'business_name' => fake()->company(),
            'legal_name' => fake()->optional()->company(),
            'verification_status' => VerificationStatus::Pending,
            'country_code' => 'CI',
            'city' => fake()->randomElement(['Abidjan', 'Dakar', 'Cotonou', 'Lome']),
            'whatsapp_phone' => fake()->phoneNumber(),
        ];
    }
}
