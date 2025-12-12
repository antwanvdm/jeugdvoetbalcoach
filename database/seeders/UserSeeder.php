<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed an initial admin user.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Create admin user
            $admin = User::firstOrCreate(
                ['email' => 'admin@teammanager.nl'],
                [
                    'name' => 'Admin',
                    'password' => Hash::make("123456"),
                    'role' => 1,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Create regular user
            $user = User::firstOrCreate(
                ['email' => 'user@team.nl'],
                [
                    'name' => 'User',
                    'password' => Hash::make("testtest"),
                    'role' => 2,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Create user's team
            $userTeam = Team::create([
                'opponent_id' => 1,
                'invite_code' => Str::random(64),
            ]);

            $user->teams()->attach($userTeam->id, [
                'role' => 1,
                'is_default' => true,
                'joined_at' => now(),
            ]);
        });
    }
}
