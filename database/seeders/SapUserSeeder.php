<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SapUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'jo.djebi@gmail.com'],
            [
                'name' => 'Jo Djebi',
                'phone' => '+2250000000000',
                'role' => UserRole::Sap,
                'portal_roles' => [UserRole::Sap->value],
                'status' => UserStatus::Active,
                'password' => Hash::make('12345678'),
            ],
        );
    }
}
