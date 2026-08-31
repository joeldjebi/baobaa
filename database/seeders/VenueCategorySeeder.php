<?php

namespace Database\Seeders;

use App\Models\VenueCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VenueCategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Expériences', 'slug' => 'experiences', 'icon' => 'sparkles'],
            ['name' => 'Salles', 'slug' => 'salles', 'icon' => 'building'],
            ['name' => 'Conférences', 'slug' => 'conferences', 'icon' => 'presentation'],
            ['name' => 'Mariages', 'slug' => 'mariages', 'icon' => 'heart'],
            ['name' => 'Concerts', 'slug' => 'concerts', 'icon' => 'music'],
            ['name' => 'Rooftops', 'slug' => 'rooftops', 'icon' => 'sun'],
            ['name' => 'Jardins', 'slug' => 'jardins', 'icon' => 'leaf'],
            ['name' => 'Hôtels', 'slug' => 'hotels', 'icon' => 'hotel'],
            ['name' => 'Restaurants', 'slug' => 'restaurants', 'icon' => 'utensils'],
            ['name' => 'Décoration', 'slug' => 'decoration', 'icon' => 'palette'],
        ];

        foreach ($categories as $index => $category) {
            VenueCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
