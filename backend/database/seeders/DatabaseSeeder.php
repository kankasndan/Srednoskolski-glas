<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with manually defined test data.
     */
    public function run(): void
    {

        
        $this->call([
            OnboardingReferenceSeeder::class,
            ForumSeeder::class,
            UserSeeder::class,
            StudentDataSeeder::class,
            ForumUserSeeder::class,
            ThreadSeeder::class,
            ThreadAttachmentSeeder::class,
            CommentSeeder::class,
            RoleSeeder::class,
        ]);
    }
}
