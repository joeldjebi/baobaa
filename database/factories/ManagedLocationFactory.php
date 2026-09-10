<?php

namespace Database\Factories;

use App\Models\ManagedLocation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ManagedLocation>
 */
class ManagedLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'parent_id' => null,
            'type' => ManagedLocation::TypeCity,
            'name' => $name,
            'slug' => Str::slug($name),
            'code' => null,
            'country_code' => 'CI',
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }

    public function country(): static
    {
        return $this->state(fn (): array => [
            'type' => ManagedLocation::TypeCountry,
            'name' => 'Côte d’Ivoire',
            'slug' => 'cote-divoire',
            'code' => 'CI',
            'country_code' => 'CI',
        ]);
    }

    public function city(): static
    {
        return $this->state(fn (): array => [
            'type' => ManagedLocation::TypeCity,
            'code' => null,
        ]);
    }

    public function district(): static
    {
        return $this->state(fn (): array => [
            'type' => ManagedLocation::TypeDistrict,
            'code' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
