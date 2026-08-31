<?php

namespace Database\Seeders;

use App\Models\CommissionRule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommissionRuleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CommissionRule::query()->updateOrCreate([
            'name' => 'Commission standard BAOBAA',
            'scope' => 'global',
        ], [
            'commission_type' => 'percentage',
            'percentage_rate' => 10,
            'fixed_amount' => null,
            'currency' => 'XOF',
            'is_active' => true,
            'starts_at' => now(),
        ]);
    }
}
