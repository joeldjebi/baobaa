<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    /**
     * Seed the default categories of event equipment offered to rent or sell.
     */
    public function run(): void
    {
        $categories = [
            'Mobilier (chaises, tables)',
            'Sonorisation',
            'Éclairage',
            'Décoration',
            'Vaisselle et art de la table',
            'Tentes et chapiteaux',
            'Scène et podium',
            'Vidéo et écrans',
            'Linge et nappage',
            'Groupes électrogènes',
        ];

        foreach ($categories as $index => $name) {
            ProductCategory::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true, 'sort_order' => $index + 1],
            );
        }
    }
}
