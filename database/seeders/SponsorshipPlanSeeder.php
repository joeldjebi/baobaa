<?php

namespace Database\Seeders;

use App\Models\SponsorshipPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SponsorshipPlanSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Découverte 1 jour',
                'slug' => 'decouverte-1-jour',
                'placement' => 'catalog_top',
                'price' => 500,
                'duration_days' => 1,
                'description' => 'Testez la mise en avant sur le catalogue BAOBAA pendant une journée.',
                'features' => ['Badge sponsorisé', 'Priorité dans le catalogue'],
            ],
            [
                'name' => 'Visibilité 7 jours',
                'slug' => 'visibilite-7-jours',
                'placement' => 'category_boost',
                'price' => 5000,
                'duration_days' => 7,
                'description' => 'Boost ciblé dans la catégorie de l’espace.',
                'features' => ['Mise en avant catégorie', 'Badge sponsorisé', 'Suivi des vues'],
            ],
            [
                'name' => 'Premium accueil 14 jours',
                'slug' => 'premium-accueil-14-jours',
                'placement' => 'home_featured',
                'price' => 25000,
                'duration_days' => 14,
                'description' => 'Exposition premium sur les emplacements les plus visibles.',
                'features' => ['Accueil premium', 'Catalogue prioritaire', 'Suivi des clics'],
            ],
        ];

        foreach ($plans as $index => $plan) {
            SponsorshipPlan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan + ['currency' => 'XOF', 'is_active' => true, 'sort_order' => $index + 1],
            );
        }
    }
}
