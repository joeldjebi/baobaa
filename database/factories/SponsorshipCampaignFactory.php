<?php

namespace Database\Factories;

use App\Models\OwnerProfile;
use App\Models\SponsorshipCampaign;
use App\Models\SponsorshipPlan;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SponsorshipCampaign>
 */
class SponsorshipCampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ownerProfile = OwnerProfile::factory();
        $plan = SponsorshipPlan::factory();

        return [
            'owner_profile_id' => $ownerProfile,
            'venue_id' => Venue::factory(['owner_profile_id' => $ownerProfile]),
            'sponsorship_plan_id' => $plan,
            'name' => 'Campagne '.fake()->words(2, true),
            'goal' => fake()->randomElement(['visibility', 'booking', 'launch']),
            'placement' => fake()->randomElement(['home_featured', 'catalog_top', 'category_boost']),
            'status' => 'pending',
            'starts_on' => now()->addDay()->toDateString(),
            'ends_on' => now()->addDays(14)->toDateString(),
            'budget_amount' => fake()->numberBetween(50000, 500000),
            'daily_budget' => fake()->numberBetween(5000, 40000),
            'currency' => 'XOF',
            'target_cities' => ['Abidjan', 'Dakar'],
        ];
    }
}
