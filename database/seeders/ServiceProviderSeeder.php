<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VenueStatus;
use App\Enums\VerificationStatus;
use App\Models\EventServiceType;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ServiceProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->updateOrCreate([
            'email' => 'pse.demo@baobaa.local',
        ], [
            'name' => 'Prestataire Démo BAOBAA',
            'phone' => '+2250700001111',
            'role' => UserRole::ServiceProvider,
            'portal_roles' => [UserRole::Client->value, UserRole::ServiceProvider->value],
            'status' => UserStatus::Active,
            'password' => Hash::make('12345678'),
        ]);

        $profile = ServiceProviderProfile::query()->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'business_name' => 'BAOBAA Event Services',
            'legal_name' => 'BAOBAA Event Services SARL',
            'tax_identifier' => 'RCCM-CI-PSE-DEMO',
            'verification_status' => VerificationStatus::Verified,
            'country_code' => 'CI',
            'city' => 'Abidjan',
            'district' => 'Cocody',
            'whatsapp_phone' => '+2250700001111',
            'service_area' => 'Abidjan, Bassam, Yamoussoukro',
            'description' => 'Prestataire événementiel vérifié spécialisé en sonorisation, lumière, scène et captation.',
            'billing_preference' => 'commission',
            'verified_at' => now(),
        ]);

        $services = [
            [
                'type' => 'Sonorisation',
                'name' => 'Pack sonorisation conférence premium',
                'short_description' => 'Audio clair, micros HF et régie technique pour réunions et conférences.',
                'description' => 'Installation complète avec enceinte principale, micros sans fil, console numérique et technicien dédié.',
                'pricing_unit' => 'event',
                'starting_price' => 180000,
                'deposit_amount' => 60000,
                'attributes' => ['Micros HF' => '4', 'Technicien' => 'Inclus', 'Installation' => '2 heures avant'],
            ],
            [
                'type' => 'Lumière et scénographie',
                'name' => 'Ambiance lumière élégance',
                'short_description' => 'Éclairage décoratif et scénique pour mariages, cocktails et galas.',
                'description' => 'Mise en lumière de salle avec projecteurs LED, lyres, ambiance colorée et pilotage technique.',
                'pricing_unit' => 'event',
                'starting_price' => 240000,
                'deposit_amount' => 80000,
                'attributes' => ['Projecteurs LED' => '12', 'Lyres' => '4', 'Plan lumière' => 'Inclus'],
            ],
        ];

        foreach ($services as $service) {
            $type = EventServiceType::query()->where('name', $service['type'])->first();

            if (! $type) {
                continue;
            }

            $profile->services()->updateOrCreate([
                'name' => $service['name'],
            ], [
                'event_service_type_id' => $type->id,
                'short_description' => $service['short_description'],
                'description' => $service['description'],
                'status' => VenueStatus::Published,
                'country_code' => 'CI',
                'city' => 'Abidjan',
                'district' => 'Cocody',
                'service_area' => 'Grand Abidjan',
                'pricing_unit' => $service['pricing_unit'],
                'currency' => 'XOF',
                'starting_price' => $service['starting_price'],
                'deposit_amount' => $service['deposit_amount'],
                'attributes' => $service['attributes'],
                'availability_notes' => ['Réservation recommandée 72 heures avant', 'Validation après échange avec le client'],
                'published_at' => now(),
            ]);
        }
    }
}
