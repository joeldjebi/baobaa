<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatformSettingSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'platform.default_currency' => ['value' => 'XOF', 'group' => 'platform', 'is_public' => true],
            'platform.active_countries' => ['value' => ['CI', 'SN', 'BJ', 'TG', 'CM'], 'group' => 'platform', 'is_public' => true],
            'monetization.model' => ['value' => 'commission_and_subscription', 'group' => 'monetization', 'is_public' => false],
            'payout.delay_days' => ['value' => 3, 'group' => 'payments', 'is_public' => false],
            'security.require_owner_verification' => ['value' => true, 'group' => 'security', 'is_public' => false],
        ];

        foreach ($settings as $key => $setting) {
            PlatformSetting::query()->updateOrCreate(['key' => $key], $setting);
        }
    }
}
