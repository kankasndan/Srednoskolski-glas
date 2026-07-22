<?php

namespace Database\Seeders;

use App\Models\Forum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ForumCountSeeder extends Seeder
{
    /**
     * Refresh denormalized threads_count / members_count after content seeders.
     */
    public function run(): void
    {
        Forum::query()->each(function (Forum $forum): void {
            $forum->threads_count = $forum->threads()->count();
            $forum->members_count = DB::table('forum_user')
                ->where('forum_id', $forum->id)
                ->count();
            $forum->save();
        });
    }
}
