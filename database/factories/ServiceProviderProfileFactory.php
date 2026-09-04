<?php

namespace Database\Factories;

use App\Enums\VerificationStatus;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceProviderProfile>
 */
class ServiceProviderProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $businessName = fake()->company().' Services';

        return [
            'user_id' => User::factory(),
            'public_uuid' => (string) Str::uuid(),
            'business_name' => $businessName,
            'slug' => Str::slug($businessName).'-'.fake()->unique()->numberBetween(1000, 9999),
            'legal_name' => $businessName,
            'tax_identifier' => fake()->optional()->numerify('RCCM-#######'),
            'verification_status' => VerificationStatus::Pending,
            'country_code' => 'CI',
            'city' => fake()->randomElement(['Abidjan', 'Dakar', 'Cotonou']),
            'district' => fake()->citySuffix(),
            'whatsapp_phone' => fake()->phoneNumber(),
            'service_area' => 'Toute la ville et communes proches',
            'description' => fake()->paragraph(),
            'billing_preference' => 'commission',
        ];
    }
}
