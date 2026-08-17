<?php

use App\Models\City;
use App\Models\FeedHide;
use App\Models\Forum;
use App\Models\School;
use App\Models\StudentData;
use App\Models\Thread;
use App\Models\ThreadView;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeForum(string $name, string $slug, ?int $schoolId = null): Forum
{
    return Forum::query()->create([
        'name' => $name,
        'slug' => $slug,
        'description' => "{$name} description",
        'type' => $schoolId ? 'school' : 'general',
        'school_id' => $schoolId,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);
}

function makeThread(Forum $forum, User $author, string $title, int $upvotes, ?CarbonInterface $createdAt = null): Thread
{
    $thread = Thread::forceCreate([
        'title' => $title,
        'description' => 'Body',
        'upvotes' => $upvotes,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);

    if ($createdAt !== null) {
        $thread->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->saveQuietly();
    }

    return $thread->fresh();
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

it('falls back to site-wide hot ranking when the user follows no forums', function () {
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

it('mixes followed-forum threads with other trending threads', function () {
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
        ->and($titles)->toContain('Site viral');
});

it('demotes previously viewed threads', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();

    $followed = makeForum('Спорт', 'sport');
    $other = makeForum('Забава', 'zabava');

    $user->forums()->attach($followed->id);

    makeThread($followed, $author, 'Followed unseen', 20);
    $viewed = makeThread($other, $author, 'Viewed elsewhere', 20);
    makeThread($other, $author, 'Unseen elsewhere', 20);

    ThreadView::query()->create([
        'user_id' => $user->id,
        'thread_id' => $viewed->id,
        'last_viewed_at' => now(),
    ]);

    $titles = $this->actingAs($user)
        ->getJson('/api/feed')
        ->assertSuccessful()
        ->json('data.*.title');

    expect($titles)->toContain('Viewed elsewhere')
        ->and(array_search('Viewed elsewhere', $titles, true))
        ->toBeGreaterThan(array_search('Followed unseen', $titles, true));
});

it('boosts school forum threads for cold-start users', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();

    $city = City::query()->create(['name' => 'Скопје']);
    $school = School::query()->create(['name' => 'Тест гимназија', 'city_id' => $city->id]);
    $schoolForum = makeForum('Тест гимназија', 'test_gimnazija', $school->id);
    $other = makeForum('Технологија', 'tehnologija');

    StudentData::query()->create([
        'user_id' => $user->id,
        'school_id' => $school->id,
        'vocation_id' => null,
        'grade' => 3,
    ]);

    makeThread($schoolForum, $author, 'School post', 3);
    makeThread($other, $author, 'Other post', 3);

    $titles = $this->actingAs($user)
        ->getJson('/api/feed')
        ->assertSuccessful()
        ->json('data.*.title');

    expect($titles[0])->toBe('School post');
});

it('excludes hidden threads from the feed', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $forum = makeForum('Спорт', 'sport');

    $hidden = makeThread($forum, $author, 'Hidden', 50);
    makeThread($forum, $author, 'Visible', 10);

    FeedHide::query()->create([
        'user_id' => $user->id,
        'thread_id' => $hidden->id,
    ]);

    $titles = $this->actingAs($user)
        ->getJson('/api/feed')
        ->assertSuccessful()
        ->json('data.*.title');

    expect($titles)->not->toContain('Hidden')
        ->and($titles)->toContain('Visible');
});

it('ranks fresher threads above equally voted older ones', function () {
    $author = User::factory()->create();
    $forum = makeForum('Спорт', 'sport');

    makeThread($forum, $author, 'Old viral', 20, now()->subDays(10));
    makeThread($forum, $author, 'Fresh mild', 8, now()->subHour());

    $titles = $this->getJson('/api/feed')
        ->assertSuccessful()
        ->json('data.*.title');

    expect($titles[0])->toBe('Fresh mild');
});

it('excludes threads older than the trending candidate window', function () {
    $author = User::factory()->create();
    $forum = makeForum('Спорт', 'sport');

    makeThread($forum, $author, 'Too old', 999, now()->subDays(45));
    makeThread($forum, $author, 'In window', 1, now()->subDays(2));

    $titles = $this->getJson('/api/feed')
        ->assertSuccessful()
        ->json('data.*.title');

    expect($titles)->toContain('In window')
        ->and($titles)->not->toContain('Too old');
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

it('hides a thread from the feed via the hide endpoint', function () {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);
    $author = User::factory()->create();
    $forum = makeForum('Спорт', 'sport');
    $thread = makeThread($forum, $author, 'Hide me', 10);

    $this->actingAs($user)
        ->postJson("/api/threads/{$thread->id}/hide")
        ->assertSuccessful()
        ->assertJsonPath('data.hidden', true);

    $titles = $this->actingAs($user)
        ->getJson('/api/feed')
        ->json('data.*.title');

    expect($titles)->not->toContain('Hide me');
});

it('reports a thread and removes it from the reporter feed', function () {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);
    $author = User::factory()->create();
    $forum = makeForum('Спорт', 'sport');
    $thread = makeThread($forum, $author, 'Report me', 10);

    $this->actingAs($user)
        ->postJson("/api/threads/{$thread->id}/report", [
            'reason' => 'Спам',
        ])
        ->assertCreated();

    $titles = $this->actingAs($user)
        ->getJson('/api/feed')
        ->json('data.*.title');

    expect($titles)->not->toContain('Report me');
});

it('rebuilds the trending feed when fresh=1', function () {
    $author = User::factory()->create();
    $forum = makeForum('Општи дискусии', 'opshti_diskusii');
    makeThread($forum, $author, 'First', 1);

    $this->getJson('/api/feed')->assertSuccessful();

    makeThread($forum, $author, 'Second', 1);

    expect($this->getJson('/api/feed')->json('data.*.title'))->not->toContain('Second');
    expect($this->getJson('/api/feed?fresh=1')->json('data.*.title'))->toContain('Second');
});
