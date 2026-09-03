<?php

namespace Database\Factories;

use App\Enums\VenueStatus;
use App\Enums\VerificationStatus;
use App\Models\OwnerProfile;
use App\Models\Venue;
use App\Models\VenueCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company().' Event Space';

        return [
            'owner_profile_id' => OwnerProfile::factory(),
            'venue_category_id' => VenueCategory::factory(),
            'name' => $name,
            'slug' => str($name)->slug()->append('-', fake()->unique()->numberBetween(1000, 9999))->toString(),
            'short_description' => fake()->sentence(12),
            'description' => fake()->paragraphs(3, true),
            'status' => VenueStatus::Draft,
            'verification_status' => VerificationStatus::Unverified,
            'booking_mode' => 'request',
            'country_code' => 'CI',
            'city' => fake()->randomElement(['Abidjan', 'Dakar', 'Cotonou', 'Lome']),
            'district' => fake()->citySuffix(),
            'min_capacity' => 10,
            'max_capacity' => fake()->numberBetween(50, 500),
            'surface_area' => fake()->numberBetween(100, 2000),
            'currency' => 'XOF',
            'starting_price' => fake()->numberBetween(50000, 1000000),
            'reservation_amount' => fake()->numberBetween(25000, 250000),
            'payment_methods' => ['baobaa_checkout', 'wave'],
        ];
    }
}
