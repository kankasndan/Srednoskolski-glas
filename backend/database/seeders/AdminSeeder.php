<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@srednoskolskiglas.mk'],
            [
                'username' => 'super_admin',
                'password' => 'password',
                'role' => 'super_admin',
                'email_verified_at' => now(),
                'onboarding_completed_at' => now(),
            ],
        );
        $admin->syncRoles(['super_admin']);

        $moderator = User::updateOrCreate(
            ['email' => 'moderator@srednoskolskiglas.mk'],
            [
                'username' => 'moderator_1',
                'password' => 'password',
                'role' => 'moderator',
                'forum_id'
                'email_verified_at' => now(),
                'onboarding_completed_at' => now(),
            ],
        );
        $moderator->syncRoles(['moderator']);
    }
}
