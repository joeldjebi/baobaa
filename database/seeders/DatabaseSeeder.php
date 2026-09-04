<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $sapPassword = app()->environment('testing') ? 'password' : env('BAOBAA_SAP_PASSWORD');

        if (filled($sapPassword)) {
            User::query()->firstOrCreate([
                'email' => env('BAOBAA_SAP_EMAIL', 'sap@baobaa.local'),
            ], [
                'name' => 'SAP BAOBAA',
                'phone' => env('BAOBAA_SAP_PHONE', '+2250000000000'),
                'role' => UserRole::Sap,
                'portal_roles' => [UserRole::Sap->value],
                'status' => UserStatus::Active,
                'password' => Hash::make($sapPassword),
            ]);
        }

        $this->call([
            SapUserSeeder::class,
            VenueCategorySeeder::class,
            AmenitySeeder::class,
            EventServiceTypeSeeder::class,
            OwnerProfileSeeder::class,
            VenueSeeder::class,
            ServiceProviderSeeder::class,
            ClientSeeder::class,
            SubscriptionPlanSeeder::class,
            SponsorshipPlanSeeder::class,
            CommissionRuleSeeder::class,
            OwnerDepositRuleSeeder::class,
            PlatformSettingSeeder::class,
        ]);
    }
}
