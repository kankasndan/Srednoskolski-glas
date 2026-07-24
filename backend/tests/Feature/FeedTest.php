<?php

use App\Models\Forum;
use App\Models\Thread;
use App\Models\ThreadView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeForum(string $name, string $slug): Forum
{
    return Forum::query()->create([
        'name' => $name,
        'slug' => $slug,
        'description' => "{$name} description",
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);
}

function makeThread(Forum $forum, User $author, string $title, int $upvotes): Thread
{
    return Thread::query()->create([
        'title' => $title,
        'description' => 'Body',
        'upvotes' => $upvotes,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);
}

it('returns a paginated site-wide feed for guests', function () {
    $author = User::factory()->create();
    $forum = makeForum('Општи дискусии', 'opshti_diskusii');

    foreach (range(1, 6) as $i) {
        makeThread($forum, $author, "Thread {$i}", $i);
    }

    $response = $this->getJson('/api/feed');

    $response->assertSuccessful()
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.total', 6)
        ->assertJsonCount(5, 'data');

    expect($response->json('data.0.title'))->toBe('Thread 6');
});

it('falls back to site-wide trending when the user follows no forums', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $forum = makeForum('Спорт', 'sport');

    makeThread($forum, $author, 'Low', 1);
    makeThread($forum, $author, 'High', 99);

    $this->actingAs($user)
        ->getJson('/api/feed')
        ->assertSuccessful()
        ->assertJsonPath('data.0.title', 'High');
});

it('boosts followed-forum threads but still includes other trending threads', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();

    $followed = makeForum('Државна матура', 'drzhavna_matura');
    $other = makeForum('Технологија', 'tehnologija');

    $user->forums()->attach($followed->id);

    makeThread($followed, $author, 'Followed mild', 5);
    makeThread($other, $author, 'Site viral', 100);
    makeThread($other, $author, 'Site quiet', 1);

    $titles = $this->actingAs($user)
        ->getJson('/api/feed')
        ->assertSuccessful()
        ->json('data.*.title');

    expect($titles)->toContain('Followed mild')
        ->and($titles)->toContain('Site viral')
        ->and($titles[0])->toBe('Site viral');
});

it('boosts previously viewed threads from unfollowed forums', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();

    $followed = makeForum('Спорт', 'sport');
    $other = makeForum('Забава', 'zabava');

    $user->forums()->attach($followed->id);

    makeThread($followed, $author, 'Followed', 2);
    $viewed = makeThread($other, $author, 'Viewed elsewhere', 2);
    makeThread($other, $author, 'Unseen elsewhere', 2);

    ThreadView::query()->create([
        'user_id' => $user->id,
        'thread_id' => $viewed->id,
        'last_viewed_at' => now(),
    ]);

    $titles = $this->actingAs($user)
        ->getJson('/api/feed')
        ->assertSuccessful()
        ->json('data.*.title');

    expect($titles[0])->toBeIn(['Followed', 'Viewed elsewhere'])
        ->and($titles)->toContain('Unseen elsewhere');
});

it('records a thread view when an authenticated user opens a thread', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $forum = makeForum('Спорт', 'sport');
    $thread = makeThread($forum, $author, 'Opened thread', 3);

    $this->actingAs($user)
        ->getJson("/api/p/{$forum->slug}/comments/{$thread->id}")
        ->assertSuccessful();

    expect(ThreadView::query()->where([
        'user_id' => $user->id,
        'thread_id' => $thread->id,
    ])->exists())->toBeTrue();

    expect($thread->fresh()->views)->toBe(1);
});
