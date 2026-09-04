<?php

namespace Database\Factories;

use App\Models\EventServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EventServiceType>
 */
class EventServiceTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Sonorisation', 'Lumière', 'Photographie', 'Vidéo', 'Podium', 'Mobilier']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'icon' => 'sparkles',
            'description' => fake()->sentence(12),
            'required_fields' => ['Zone d’intervention', 'Équipe incluse', 'Délai d’installation'],
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
