<?php

use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeThreadForFollowTests(): Thread
{
    $author = User::factory()->create();
    $forum = Forum::query()->create([
        'name' => 'Општ форум',
        'slug' => 'opst-forum-follow-'.uniqid(),
        'description' => 'General forum',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);

    return Thread::forceCreate([
        'title' => 'Тест дискусија',
        'description' => 'Содржина',
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'upvotes' => 0,
        'views' => 0,
        'is_anonymous' => false,
    ]);
}

it('requires authentication to follow a thread', function () {
    $thread = makeThreadForFollowTests();

    $this->postJson("/api/threads/{$thread->id}/follow")->assertUnauthorized();
});

it('requires authentication to unfollow a thread', function () {
    $thread = makeThreadForFollowTests();

    $this->deleteJson("/api/threads/{$thread->id}/follow")->assertUnauthorized();
});

it('allows following a thread', function () {
    $user = User::factory()->create();
    $thread = makeThreadForFollowTests();

    $this->actingAs($user)
        ->postJson("/api/threads/{$thread->id}/follow")
        ->assertSuccessful()
        ->assertJsonPath('data.is_following', true);

    expect($user->fresh()->followedThreads()->pluck('threads.id')->all())->toContain($thread->id);
});

it('does not duplicate follows', function () {
    $user = User::factory()->create();
    $thread = makeThreadForFollowTests();

    $this->actingAs($user)->postJson("/api/threads/{$thread->id}/follow")->assertSuccessful();
    $this->actingAs($user)->postJson("/api/threads/{$thread->id}/follow")->assertSuccessful();

    expect($user->followedThreads()->count())->toBe(1);
});

it('allows unfollowing a thread', function () {
    $user = User::factory()->create();
    $thread = makeThreadForFollowTests();
    $user->followedThreads()->attach($thread->id);

    $this->actingAs($user)
        ->deleteJson("/api/threads/{$thread->id}/follow")
        ->assertSuccessful()
        ->assertJsonPath('data.is_following', false);

    expect($user->fresh()->followedThreads()->pluck('threads.id')->all())->not->toContain($thread->id);
});

it('includes is_following on thread detail for authenticated users', function () {
    $user = User::factory()->create();
    $thread = makeThreadForFollowTests();
    $user->followedThreads()->attach($thread->id);

    $this->actingAs($user)
        ->getJson("/api/p/{$thread->forum->slug}/comments/{$thread->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.thread.is_following', true);
});

it('lists followed threads on the profile endpoint', function () {
    $user = User::factory()->create();
    $thread = makeThreadForFollowTests();
    $user->followedThreads()->attach($thread->id);

    $this->actingAs($user)
        ->getJson('/api/me/followed-threads')
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $thread->id)
        ->assertJsonPath('data.0.is_following', true);
});
