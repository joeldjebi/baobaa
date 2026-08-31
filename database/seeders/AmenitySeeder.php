<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            ['name' => 'Parking', 'slug' => 'parking', 'group' => 'access'],
            ['name' => 'Climatisation', 'slug' => 'climatisation', 'group' => 'comfort'],
            ['name' => 'Wi-Fi', 'slug' => 'wi-fi', 'group' => 'tech'],
            ['name' => 'Sonorisation', 'slug' => 'sonorisation', 'group' => 'tech'],
            ['name' => 'Projecteur', 'slug' => 'projecteur', 'group' => 'tech'],
            ['name' => 'Scene', 'slug' => 'scene', 'group' => 'event'],
            ['name' => 'Cuisine', 'slug' => 'cuisine', 'group' => 'catering'],
            ['name' => 'Groupe electrogene', 'slug' => 'groupe-electrogene', 'group' => 'security'],
            ['name' => 'Securite', 'slug' => 'securite', 'group' => 'security'],
            ['name' => 'Acces PMR', 'slug' => 'acces-pmr', 'group' => 'access'],
        ];

        foreach ($amenities as $index => $amenity) {
            Amenity::query()->updateOrCreate(
                ['slug' => $amenity['slug']],
                $amenity + ['sort_order' => $index + 1, 'is_active' => true],
            );
        }
    }
}
