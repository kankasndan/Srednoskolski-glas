<?php

use App\Models\Comment;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function profileForum(array $overrides = []): Forum
{
    return Forum::query()->create(array_merge([
        'name' => 'Општи дискусии',
        'slug' => 'opshti-diskusii',
        'description' => 'General forum',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ], $overrides));
}

function profileThread(Forum $forum, User $author, array $overrides = []): Thread
{
    return Thread::query()->create(array_merge([
        'title' => 'Моја дискусија',
        'description' => 'Текст',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ], $overrides));
}

it('requires authentication for profile activity endpoints', function () {
    $this->getJson('/api/me/threads')->assertUnauthorized();
    $this->getJson('/api/me/comments')->assertUnauthorized();
    $this->getJson('/api/me/followed-forums')->assertUnauthorized();
});

it('returns the authenticated users threads newest first', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $forum = profileForum();

    $older = profileThread($forum, $user, ['title' => 'Older thread']);
    $older->forceFill(['created_at' => now()->subDay()])->save();

    $newer = profileThread($forum, $user, ['title' => 'Newer thread']);
    profileThread($forum, $other, ['title' => 'Someone else']);

    $this->actingAs($user)
        ->getJson('/api/me/threads')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $newer->id)
        ->assertJsonPath('data.0.title', 'Newer thread')
        ->assertJsonPath('data.0.forum.slug', 'opshti-diskusii')
        ->assertJsonPath('data.0.forum.type', 'general')
        ->assertJsonPath('data.1.id', $older->id);
});

it('returns the authenticated users comments with thread context', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $forum = profileForum([
        'name' => 'Државна матура',
        'slug' => 'drzhavna_matura',
    ]);
    $thread = profileThread($forum, $other, ['title' => 'Тема за коментар']);

    $comment = Comment::query()->create([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $user->id,
        'content' => 'Мој коментар',
        'upvotes' => 3,
    ]);

    Comment::query()->create([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $other->id,
        'content' => 'Туѓ коментар',
    ]);

    $this->actingAs($user)
        ->getJson('/api/me/comments')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $comment->id)
        ->assertJsonPath('data.0.content', 'Мој коментар')
        ->assertJsonPath('data.0.thread.id', $thread->id)
        ->assertJsonPath('data.0.thread.title', 'Тема за коментар')
        ->assertJsonPath('data.0.thread.forum.slug', 'drzhavna_matura')
        ->assertJsonPath('data.0.thread.forum.type', 'general')
        ->assertJsonPath('data.0.author.id', $user->id);
});

it('returns forums the authenticated user follows', function () {
    $user = User::factory()->create();
    $followed = profileForum([
        'name' => 'AI',
        'slug' => 'ai',
        'members_count' => 10,
    ]);
    $other = profileForum([
        'name' => 'Other',
        'slug' => 'other',
    ]);

    $user->forums()->attach($followed->id);

    $response = $this->actingAs($user)
        ->getJson('/api/me/followed-forums')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $followed->id)
        ->assertJsonPath('data.0.slug', 'ai')
        ->assertJsonPath('data.0.members_count', 10);

    $slugs = collect($response->json('data'))->pluck('slug');
    expect($slugs)->not->toContain($other->slug);
});

function publicProfileUser(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'username' => 'anon_author',
        'onboarding_completed_at' => now(),
    ], $overrides));
}

it('keeps anonymous threads on the owner activity list and counts', function () {
    $user = publicProfileUser();
    $forum = profileForum();
    profileThread($forum, $user, ['title' => 'Signed post']);
    profileThread($forum, $user, [
        'title' => 'Secret post',
        'is_anonymous' => true,
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/me/threads')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $payload = collect($response->json('data'));

    expect($payload->pluck('title')->all())
        ->toContain('Signed post')
        ->toContain('Secret post');

    expect($payload->firstWhere('title', 'Secret post')['is_anonymous'])->toBeTrue();
    expect($payload->firstWhere('title', 'Signed post')['is_anonymous'])->toBeFalse();

    $this->actingAs($user)
        ->getJson('/api/me/counts')
        ->assertOk()
        ->assertJsonPath('data.threads', 2);
});

it('hides anonymous threads from a public profile list and tab count', function () {
    $author = publicProfileUser(['username' => 'jane']);
    $forum = profileForum();
    $visible = profileThread($forum, $author, ['title' => 'Signed post']);
    profileThread($forum, $author, [
        'title' => 'Secret post',
        'is_anonymous' => true,
    ]);

    $this->getJson("/api/u/{$author->username}/threads")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visible->id)
        ->assertJsonPath('data.0.is_anonymous', false)
        ->assertJsonPath('data.0.author.username', 'jane');

    $this->getJson("/api/u/{$author->username}")
        ->assertOk()
        ->assertJsonPath('data.counts.threads', 1)
        ->assertJsonPath('data.counts.comments', 0);

    $this->actingAs($author)
        ->getJson("/api/u/{$author->username}/threads")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visible->id);
});

it('does not list comments on the owners anonymous threads on the public profile', function () {
    $author = publicProfileUser(['username' => 'marko']);
    $other = publicProfileUser(['username' => 'other_user']);
    $forum = profileForum();

    $anonymous = profileThread($forum, $author, [
        'title' => 'Secret post',
        'is_anonymous' => true,
    ]);
    $signed = profileThread($forum, $author, ['title' => 'Signed post']);
    $someoneElsesAnonymous = profileThread($forum, $other, [
        'title' => 'Someone elses secret',
        'is_anonymous' => true,
    ]);

    $ownAnonymousComment = Comment::query()->create([
        'thread_id' => $anonymous->id,
        'parent_id' => null,
        'user_id' => $author->id,
        'content' => 'I wrote this thread',
    ]);
    $signedComment = Comment::query()->create([
        'thread_id' => $signed->id,
        'parent_id' => null,
        'user_id' => $author->id,
        'content' => 'Normal comment',
    ]);
    $onOthersAnonymous = Comment::query()->create([
        'thread_id' => $someoneElsesAnonymous->id,
        'parent_id' => null,
        'user_id' => $author->id,
        'content' => 'Replying anonymously authored thread',
    ]);

    $response = $this->getJson("/api/u/{$author->username}/comments")
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)
        ->toContain($signedComment->id)
        ->toContain($onOthersAnonymous->id)
        ->not->toContain($ownAnonymousComment->id);

    $this->getJson("/api/u/{$author->username}")
        ->assertOk()
        ->assertJsonPath('data.counts.comments', 2);

    $this->actingAs($author)
        ->getJson('/api/me/comments')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});
