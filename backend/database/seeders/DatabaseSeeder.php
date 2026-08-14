<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with manually defined test data.
     */
    public function run(): void
    {
        Model::unguard();

        $this->call([
            // Reference data
            OnboardingReferenceSeeder::class,
            ForumSeeder::class,
            RolePermissionSeeder::class,

            // Users (regular + admin/moderator with Spatie roles)
            UserSeeder::class,
            AdminSeeder::class,
            StudentDataSeeder::class,
            ForumUserSeeder::class,

            // Content
            ThreadSeeder::class,
            ThreadAttachmentSeeder::class,
            CommentSeeder::class,
            MentionSeeder::class,

            // Optional bulk load for feed/performance testing (1000 threads + extra users):
            // php artisan db:seed --class=LargeThreadSeeder

            // Moderation
            ReportSeeder::class,
            SanctionSeeder::class,
            AppealSeeder::class,

            // Denormalized counters last
            ForumCountSeeder::class,
        ]);

        Model::reguard();
    }
}
