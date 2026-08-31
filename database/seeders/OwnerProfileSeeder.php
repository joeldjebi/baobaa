<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\OwnerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            [
                'public_uuid' => '2d08df2a-8d0f-42a5-a22e-47a617a7b101',
                'email' => 'signature.events@baobaa.local',
                'business_name' => 'BAOBAA Signature Events',
                'slug' => 'baobaa-signature-events',
                'city' => 'Abidjan',
                'country_code' => 'CI',
                'billing_preference' => 'commission',
            ],
            [
                'public_uuid' => '2d08df2a-8d0f-42a5-a22e-47a617a7b102',
                'email' => 'kora.venues@baobaa.local',
                'business_name' => 'Kora Prestige Venues',
                'slug' => 'kora-prestige-venues',
                'city' => 'Dakar',
                'country_code' => 'SN',
                'billing_preference' => 'subscription',
            ],
            [
                'public_uuid' => '2d08df2a-8d0f-42a5-a22e-47a617a7b103',
                'email' => 'azalai.garden@baobaa.local',
                'business_name' => 'Azalai Garden Collection',
                'slug' => 'azalai-garden-collection',
                'city' => 'Cotonou',
                'country_code' => 'BJ',
                'billing_preference' => 'hybrid',
            ],
            [
                'public_uuid' => '2d08df2a-8d0f-42a5-a22e-47a617a7b104',
                'email' => 'pee.demo@baobaa.local',
                'business_name' => 'PEE Démo Prestige',
                'slug' => 'pee-demo-prestige',
                'city' => 'Abidjan',
                'country_code' => 'CI',
                'billing_preference' => 'hybrid',
            ],
        ];

        foreach ($profiles as $profile) {
            $user = User::query()->updateOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['business_name'],
                    'phone' => '+2250101010101',
                    'role' => UserRole::Owner,
                    'portal_roles' => [UserRole::Owner->value],
                    'status' => UserStatus::Active,
                    'password' => Hash::make('password'),
                ],
            );

            $ownerProfile = OwnerProfile::query()->updateOrCreate(
                ['public_uuid' => $profile['public_uuid']],
                [
                    'user_id' => $user->id,
                    'owner_type' => 'company',
                    'business_name' => $profile['business_name'],
                    'slug' => $profile['slug'],
                    'verification_status' => VerificationStatus::Verified,
                    'country_code' => $profile['country_code'],
                    'city' => $profile['city'],
                    'whatsapp_phone' => '+2250101010101',
                    'payout_provider' => 'mobile_money',
                    'payout_account_reference' => '+2250101010101',
                    'billing_preference' => $profile['billing_preference'],
                    'verified_at' => now(),
                ],
            );

            foreach ([
                ['name' => 'Sonorisation premium', 'description' => 'Micros, enceintes et assistance technique.', 'price' => 150000],
                ['name' => 'Service café et thé', 'description' => 'Pause chaude pour les invités.', 'price' => 50000],
                ['name' => 'Mobilier supplémentaire', 'description' => 'Tables et chaises selon configuration.', 'price' => 75000],
            ] as $index => $module) {
                $ownerProfile->moduleTemplates()->updateOrCreate(
                    ['name' => $module['name']],
                    $module + [
                        'currency' => 'XOF',
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ],
                );
            }
        }
    }
}
