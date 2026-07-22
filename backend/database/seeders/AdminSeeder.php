<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@srednoskolskiglas.mk'],
            [
                'username' => 'super_admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
                'onboarding_completed_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'moderator@srednoskolskiglas.mk'],
            [
                'username' => 'moderator_1',
                'password' => Hash::make('password'),
                'role' => 'moderator',
                'forum_id'
                'email_verified_at' => now(),
                'onboarding_completed_at' => now(),
            ]
        );
    }
}