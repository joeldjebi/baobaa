<?php

namespace Database\Seeders;

use App\Models\ManagedLocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ManagedLocationSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            'CI' => [
                'name' => 'Côte d’Ivoire',
                'cities' => [
                    'Abidjan' => ['Cocody', 'Plateau', 'Marcory', 'Treichville', 'Yopougon', 'Bingerville'],
                    'Bouaké' => ['Belleville', 'Commerce', 'Nimbo'],
                    'Yamoussoukro' => ['Habitat', 'Morofé', 'Quartier millionnaire'],
                ],
            ],
            'SN' => [
                'name' => 'Sénégal',
                'cities' => [
                    'Dakar' => ['Almadies', 'Plateau Dakar', 'Mermoz', 'Sacré-Cœur'],
                ],
            ],
            'BJ' => [
                'name' => 'Bénin',
                'cities' => [
                    'Cotonou' => ['Fidjrossè', 'Akpakpa', 'Cadjèhoun'],
                ],
            ],
            'TG' => [
                'name' => 'Togo',
                'cities' => [
                    'Lomé' => ['Agoè', 'Bè', 'Tokoin'],
                ],
            ],
            'CM' => [
                'name' => 'Cameroun',
                'cities' => [
                    'Douala' => ['Bonapriso', 'Akwa', 'Bonamoussadi'],
                    'Yaoundé' => ['Bastos', 'Mvog-Ada', 'Odza'],
                ],
            ],
        ];

        $sortOrder = 1;

        foreach ($countries as $code => $countryPayload) {
            $country = ManagedLocation::query()->updateOrCreate([
                'type' => ManagedLocation::TypeCountry,
                'code' => $code,
            ], [
                'parent_id' => null,
                'name' => $countryPayload['name'],
                'slug' => Str::slug($countryPayload['name']),
                'country_code' => $code,
                'is_active' => true,
                'sort_order' => $sortOrder++,
            ]);

            foreach ($countryPayload['cities'] as $cityName => $districts) {
                $city = ManagedLocation::query()->updateOrCreate([
                    'type' => ManagedLocation::TypeCity,
                    'parent_id' => $country->id,
                    'slug' => Str::slug($cityName),
                ], [
                    'name' => $cityName,
                    'code' => null,
                    'country_code' => $code,
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]);

                foreach ($districts as $districtName) {
                    ManagedLocation::query()->updateOrCreate([
                        'type' => ManagedLocation::TypeDistrict,
                        'parent_id' => $city->id,
                        'slug' => Str::slug($districtName),
                    ], [
                        'name' => $districtName,
                        'code' => null,
                        'country_code' => $code,
                        'is_active' => true,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }
        }
    }
}
