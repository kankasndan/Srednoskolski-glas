<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(OnboardingReferenceSeeder::class);
        $this->call([RoleSeeder::class,]);

        User::factory()->create([
            'username' => 'test_user',
            'email' => 'test@example.com',
            'onboarding_completed_at' => now(),
        ]);
    }
}
