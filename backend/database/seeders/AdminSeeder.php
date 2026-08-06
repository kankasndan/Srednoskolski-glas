<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Prefer ADMIN_SEED_PASSWORD in .env. Falls back to "password" only outside production.
        $password = (string) env('ADMIN_SEED_PASSWORD', '');

        if ($password === '') {
            if (app()->environment('production')) {
                Log::warning('AdminSeeder skipped: set ADMIN_SEED_PASSWORD before seeding admins in production.');

                return;
            }

            $password = 'password';
            $this->command?->warn('AdminSeeder using default password "password". Set ADMIN_SEED_PASSWORD for safer local seeds.');
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@srednoskolskiglas.mk'],
            [
                'username' => 'super_admin',
                'password' => $password,
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
                'password' => $password,
                'role' => 'moderator',
                'email_verified_at' => now(),
                'onboarding_completed_at' => now(),
            ],
        );
        $moderator->syncRoles(['moderator']);
    }
}
