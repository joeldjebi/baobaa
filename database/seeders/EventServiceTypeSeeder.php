<?php

namespace Database\Seeders;

use App\Models\EventServiceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Sonorisation',
                'icon' => 'mic-2',
                'description' => 'Systèmes audio, micros, consoles et techniciens pour conférences, concerts et cérémonies.',
                'required_fields' => ['Puissance sonore', 'Nombre de micros', 'Technicien inclus', 'Délai d’installation'],
            ],
            [
                'name' => 'Lumière et scénographie',
                'icon' => 'sparkles',
                'description' => 'Éclairage décoratif, architectural et scénique pour créer une ambiance événementielle premium.',
                'required_fields' => ['Type d’éclairage', 'Technicien lumière', 'Plan de scène', 'Puissance électrique'],
            ],
            [
                'name' => 'Podium et scène',
                'icon' => 'layout-panel-top',
                'description' => 'Podiums, scènes modulables, structures et habillages pour événements professionnels.',
                'required_fields' => ['Dimensions', 'Hauteur', 'Charge supportée', 'Montage inclus'],
            ],
            [
                'name' => 'Photo et vidéo',
                'icon' => 'camera',
                'description' => 'Captation photo, vidéo, streaming et couverture média des événements.',
                'required_fields' => ['Nombre d’opérateurs', 'Livrables', 'Délai de livraison', 'Matériel inclus'],
            ],
            [
                'name' => 'Mobilier événementiel',
                'icon' => 'armchair',
                'description' => 'Location de chaises, tables, salons, bars mobiles et mobilier décoratif.',
                'required_fields' => ['Quantité disponible', 'Livraison', 'Installation', 'Caution éventuelle'],
            ],
        ];

        foreach ($types as $index => $type) {
            EventServiceType::query()->updateOrCreate([
                'slug' => Str::slug($type['name']),
            ], [
                ...$type,
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
