<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Support\SyncCommentMentions;
use Illuminate\Database\Seeder;

class MentionSeeder extends Seeder
{
    /**
     * Persist @username mentions found in seeded comments.
     */
    public function run(): void
    {
        $sync = app(SyncCommentMentions::class);

        Comment::query()->each(
            fn (Comment $comment) => $sync->handle($comment),
        );
    }
}
