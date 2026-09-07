<?php

namespace Database\Seeders;

use App\Models\Forum;
use Illuminate\Database\Seeder;

/**
 * Idempotent seed for Railway / staging. No demo student accounts.
 * Forums are created only when the table is empty so counters are not reset.
 * AdminSeeder only creates staff when ADMIN_SEED_PASSWORD is set.
 */
class RailwaySeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OnboardingReferenceSeeder::class,
            RolePermissionSeeder::class,
            AdminSeeder::class,
        ]);

        if (! Forum::query()->exists()) {
            $this->call(ForumSeeder::class);
        }
    }
}
