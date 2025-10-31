<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed an initial admin user.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@teammanager.nl'],
            [
                'name' => 'Admin',
                'password' => Hash::make("123456"),
                'role' => 1,
                'team_name' => '-',
                'maps_location' => '-',
                'logo' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        User::firstOrCreate(
            ['email' => 'user@team.nl'],
            [
                'name' => 'User',
                'password' => Hash::make("testtest"),
                'role' => 2,
                'team_name' => '-',
                'maps_location' => '-',
                'logo' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
