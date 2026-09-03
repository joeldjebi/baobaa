<?php

namespace Database\Seeders;

use App\Models\OwnerProfile;
use Illuminate\Database\Seeder;

class OwnerDepositRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OwnerProfile::query()
            ->orderBy('id')
            ->get(['id', 'business_name'])
            ->each(function (OwnerProfile $ownerProfile): void {
                $ownerProfile->depositRules()->updateOrCreate([
                    'name' => 'Acompte standard BAOBAA',
                ], [
                    'deposit_type' => 'percentage',
                    'percentage_rate' => 30,
                    'fixed_amount' => null,
                    'minimum_amount' => 25000,
                    'maximum_amount' => 500000,
                    'currency' => 'XOF',
                    'is_active' => true,
                    'starts_at' => now(),
                ]);
            });
    }
}
