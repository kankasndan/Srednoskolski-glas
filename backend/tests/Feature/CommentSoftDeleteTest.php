<?php

use App\Models\Comment;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stamps deleted_by when a thread is soft deleted', function () {
    $deleter = User::factory()->create();
    $author = User::factory()->create();

    $forum = Forum::create([
        'name' => 'Test forum',
        'slug' => 'test-forum',
        'description' => 'Test forum description',
        'type' => 'general',
    ]);

    $thread = Thread::create([
        'title' => 'Test thread',
        'description' => 'Test thread description',
        'user_id' => $author->id,
        'forum_id' => $forum->id,
    ]);

    $this->actingAs($deleter);

    $thread->delete();

    expect($thread->fresh()->deleted_at)->not()->toBeNull()
        ->and($thread->fresh()->deleted_by)->toBe($deleter->id);
});

it('stamps deleted_by when a comment is soft deleted', function () {
    $deleter = User::factory()->create();
    $author = User::factory()->create();

    $forum = Forum::create([
        'name' => 'Test forum',
        'slug' => 'test-forum',
        'description' => 'Test forum description',
        'type' => 'general',
    ]);

    $thread = Thread::create([
        'title' => 'Test thread',
        'description' => 'Test thread description',
        'user_id' => $author->id,
        'forum_id' => $forum->id,
    ]);

    $comment = Comment::create([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $author->id,
        'content' => 'Test comment',
    ]);

    $this->actingAs($deleter);

    $comment->delete();

    expect($comment->fresh()->deleted_at)->not()->toBeNull()
        ->and($comment->fresh()->deleted_by)->toBe($deleter->id);
});
