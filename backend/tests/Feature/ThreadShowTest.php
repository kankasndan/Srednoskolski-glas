<?php

use App\Models\Comment;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeThreadWithComments(): array
{
    $author = User::factory()->create();
    $forum = Forum::query()->create([
        'name' => 'Спорт',
        'slug' => 'sport',
        'description' => 'Sport',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);
    $thread = Thread::query()->create([
        'title' => 'Poll thread',
        'description' => 'Body',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);

    $oldest = Comment::query()->create([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $author->id,
        'content' => 'Oldest comment',
    ]);
    $oldest->forceFill([
        'upvotes' => 1,
        'created_at' => now()->subDays(3),
        'updated_at' => now()->subDays(3),
    ])->save();

    $best = Comment::query()->create([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $author->id,
        'content' => 'Best comment',
    ]);
    $best->forceFill([
        'upvotes' => 10,
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ])->save();

    $newest = Comment::query()->create([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $author->id,
        'content' => 'Newest comment',
    ]);
    $newest->forceFill([
        'upvotes' => 2,
        'created_at' => now(),
        'updated_at' => now(),
    ])->save();

    Comment::query()->create([
        'thread_id' => $thread->id,
        'parent_id' => $best->id,
        'user_id' => $author->id,
        'content' => 'Reply under best',
    ]);

    return compact('forum', 'thread', 'oldest', 'best', 'newest');
}

it('increments views and returns nested comments', function () {
    ['forum' => $forum, 'thread' => $thread, 'best' => $best] = makeThreadWithComments();

    $response = $this->getJson("/api/p/{$forum->slug}/comments/{$thread->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.thread.id', $thread->id)
        ->assertJsonCount(3, 'data.comments')
        ->assertJsonPath('data.comments.0.id', $best->id)
        ->assertJsonCount(1, 'data.comments.0.replies');

    expect($thread->fresh()->views)->toBe(1);
});

it('sorts comments by best by default', function () {
    ['forum' => $forum, 'thread' => $thread, 'best' => $best] = makeThreadWithComments();

    $this->getJson("/api/p/{$forum->slug}/comments/{$thread->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.comments.0.id', $best->id);
});

it('sorts comments by newest', function () {
    ['forum' => $forum, 'thread' => $thread, 'newest' => $newest] = makeThreadWithComments();

    $this->getJson("/api/p/{$forum->slug}/comments/{$thread->id}?sort=newest")
        ->assertSuccessful()
        ->assertJsonPath('data.comments.0.id', $newest->id);
});

it('sorts comments by oldest', function () {
    ['forum' => $forum, 'thread' => $thread, 'oldest' => $oldest] = makeThreadWithComments();

    $this->getJson("/api/p/{$forum->slug}/comments/{$thread->id}?sort=oldest")
        ->assertSuccessful()
        ->assertJsonPath('data.comments.0.id', $oldest->id);
});
