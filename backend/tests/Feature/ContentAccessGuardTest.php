<?php

use App\Models\Comment;
use App\Models\Forum;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Thread;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->forum = Forum::query()->create([
        'name' => 'Општо',
        'slug' => 'guard-opshto',
        'description' => 'General',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);

    $this->author = User::factory()->create(['onboarding_completed_at' => now()]);
    $this->voter = User::factory()->create(['onboarding_completed_at' => now()]);
});

function guardThread(Forum $forum, User $author): Thread
{
    return Thread::forceCreate([
        'title' => 'Guarded',
        'description' => 'Body',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);
}

function guardComment(Thread $thread, User $author): Comment
{
    return Comment::forceCreate([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $author->id,
        'content' => 'A comment',
    ]);
}

it('still allows voting on live content', function () {
    $thread = guardThread($this->forum, $this->author);
    $comment = guardComment($thread, $this->author);

    $this->actingAs($this->voter)
        ->postJson("/api/threads/{$thread->id}/upvote")
        ->assertSuccessful()
        ->assertJsonPath('data.has_voted', true);

    $this->actingAs($this->voter)
        ->postJson("/api/comments/{$comment->id}/upvote")
        ->assertSuccessful()
        ->assertJsonPath('data.has_voted', true);
});

it('rejects voting on a comment whose thread was deleted', function () {
    $thread = guardThread($this->forum, $this->author);
    $comment = guardComment($thread, $this->author);

    $thread->delete();

    $this->actingAs($this->voter)
        ->postJson("/api/comments/{$comment->id}/upvote")
        ->assertNotFound();
});

it('rejects reporting a comment whose thread was deleted', function () {
    $thread = guardThread($this->forum, $this->author);
    $comment = guardComment($thread, $this->author);

    $thread->delete();

    $this->actingAs($this->voter)
        ->postJson("/api/comments/{$comment->id}/report", ['reason' => 'spam'])
        ->assertNotFound();
});

it('rejects poll votes when the thread was deleted', function () {
    $thread = guardThread($this->forum, $this->author);

    $poll = Poll::query()->create([
        'thread_id' => $thread->id,
        'question' => 'Кој предмет е најтежок?',
        'ends_at' => now()->addDay(),
    ]);

    $option = PollOption::query()->create([
        'poll_id' => $poll->id,
        'label' => 'Математика',
        'position' => 1,
    ]);

    $thread->delete();

    $this->actingAs($this->voter)
        ->postJson("/api/polls/{$poll->id}/vote", ['poll_option_id' => $option->id])
        ->assertNotFound();
});
