<?php

use App\Models\Comment;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function voteForum(): Forum
{
    return Forum::query()->create([
        'name' => 'Спорт',
        'slug' => 'sport',
        'description' => 'Sport forum',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);
}

function voteThread(Forum $forum, User $author, int $upvotes = 0): Thread
{
    return Thread::query()->create([
        'title' => 'Test thread',
        'description' => 'Body',
        'upvotes' => $upvotes,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);
}

function voteComment(Thread $thread, User $author, int $upvotes = 0): Comment
{
    $comment = Comment::query()->create([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $author->id,
        'content' => 'A comment',
    ]);

    $comment->forceFill(['upvotes' => $upvotes])->save();

    return $comment->fresh();
}

it('requires authentication to upvote a thread', function () {
    $author = User::factory()->create();
    $thread = voteThread(voteForum(), $author);

    $this->postJson("/api/threads/{$thread->id}/upvote")
        ->assertUnauthorized();
});

it('upvotes a thread and toggles off on second request', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $thread = voteThread(voteForum(), $author, 10);

    $this->actingAs($user)
        ->postJson("/api/threads/{$thread->id}/upvote")
        ->assertSuccessful()
        ->assertJsonPath('data.upvotes', 11)
        ->assertJsonPath('data.has_voted', true);

    expect(Vote::query()->where([
        'user_id' => $user->id,
        'votable_type' => Thread::class,
        'votable_id' => $thread->id,
    ])->exists())->toBeTrue();

    $this->actingAs($user)
        ->postJson("/api/threads/{$thread->id}/upvote")
        ->assertSuccessful()
        ->assertJsonPath('data.upvotes', 10)
        ->assertJsonPath('data.has_voted', false);

    expect(Vote::query()->where([
        'user_id' => $user->id,
        'votable_type' => Thread::class,
        'votable_id' => $thread->id,
    ])->exists())->toBeFalse();
});

it('upvotes a comment and toggles off on second request', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $thread = voteThread(voteForum(), $author);
    $comment = voteComment($thread, $author, 3);

    $this->actingAs($user)
        ->postJson("/api/comments/{$comment->id}/upvote")
        ->assertSuccessful()
        ->assertJsonPath('data.upvotes', 4)
        ->assertJsonPath('data.has_voted', true);

    $this->actingAs($user)
        ->postJson("/api/comments/{$comment->id}/upvote")
        ->assertSuccessful()
        ->assertJsonPath('data.upvotes', 3)
        ->assertJsonPath('data.has_voted', false);
});

it('does not let the same user create two votes for one thread', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $thread = voteThread(voteForum(), $author, 0);

    $this->actingAs($user)->postJson("/api/threads/{$thread->id}/upvote")->assertSuccessful();
    $this->actingAs($user)->postJson("/api/threads/{$thread->id}/upvote")->assertSuccessful();
    $this->actingAs($user)->postJson("/api/threads/{$thread->id}/upvote")->assertSuccessful();

    expect(Vote::query()->where('user_id', $user->id)->count())->toBe(1);
    expect($thread->fresh()->upvotes)->toBe(1);
});

it('exposes has_voted on thread detail for the current user', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $forum = voteForum();
    $thread = voteThread($forum, $author, 5);

    Vote::query()->create([
        'user_id' => $user->id,
        'votable_type' => Thread::class,
        'votable_id' => $thread->id,
    ]);

    $this->actingAs($user)
        ->getJson("/api/p/{$forum->slug}/comments/{$thread->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.thread.has_voted', true)
        ->assertJsonPath('data.thread.upvotes', 5);
});
