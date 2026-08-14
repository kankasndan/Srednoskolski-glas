<?php

use App\Models\City;
use App\Models\Forum;
use App\Models\School;
use App\Models\Thread;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function newestForum(string $name, string $slug, ?int $schoolId = null): Forum
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

function newestThread(Forum $forum, User $author, string $title, ?CarbonInterface $createdAt = null): Thread
{
    $thread = Thread::forceCreate([
        'title' => $title,
        'description' => 'Body',
        'upvotes' => 0,
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

it('returns newest general threads for guests and excludes unfollowed schools', function () {
    $author = User::factory()->create();
    $general = newestForum('Спорт', 'sport');
    $city = City::query()->create(['name' => 'Скопје']);
    $school = School::query()->create(['name' => 'Тест', 'city_id' => $city->id]);
    $schoolForum = newestForum('Тест', 'test-school', $school->id);

    newestThread($schoolForum, $author, 'School only', now()->subMinute());
    newestThread($general, $author, 'General new', now());

    $this->getJson('/api/newest')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'General new');
});

it('returns pure newest general threads when the user follows no forums', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $a = newestForum('А', 'a');
    $b = newestForum('Б', 'b');

    newestThread($a, $author, 'Older A', now()->subHour());
    newestThread($b, $author, 'Newer B', now());

    $this->actingAs($user)
        ->getJson('/api/newest')
        ->assertSuccessful()
        ->assertJsonPath('data.0.title', 'Newer B')
        ->assertJsonPath('data.1.title', 'Older A');
});

it('includes followed school forums and excludes unfollowed schools', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $general = newestForum('Матура', 'matura');

    $city = City::query()->create(['name' => 'Скопје']);
    $schoolA = School::query()->create(['name' => 'Училиште А', 'city_id' => $city->id]);
    $schoolB = School::query()->create(['name' => 'Училиште Б', 'city_id' => $city->id]);
    $followedSchool = newestForum('Училиште А', 'school-a', $schoolA->id);
    $otherSchool = newestForum('Училиште Б', 'school-b', $schoolB->id);

    $user->forums()->attach($followedSchool->id);

    newestThread($otherSchool, $author, 'Other school', now());
    newestThread($followedSchool, $author, 'Followed school', now()->subMinute());
    newestThread($general, $author, 'General', now()->subMinutes(2));

    $titles = $this->actingAs($user)
        ->getJson('/api/newest')
        ->assertSuccessful()
        ->json('data.*.title');

    expect($titles)->toContain('Followed school')
        ->and($titles)->toContain('General')
        ->and($titles)->not->toContain('Other school');
});

it('mixes focused followed forums ahead of other general forums', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $sport = newestForum('Спорт', 'sport-mix');
    $matura = newestForum('Матура', 'matura-mix');

    $user->forums()->attach($sport->id);

    // Other-general is newer, but focused slots should still lead the mix.
    newestThread($matura, $author, 'Other 1', now());
    newestThread($sport, $author, 'Focused 1', now()->subSeconds(10));
    newestThread($sport, $author, 'Focused 2', now()->subSeconds(20));
    newestThread($matura, $author, 'Other 2', now()->subSeconds(30));

    $titles = $this->actingAs($user)
        ->getJson('/api/newest')
        ->assertSuccessful()
        ->json('data.*.title');

    // Pattern focused, focused, other → Focused 1, Focused 2, Other 1, …
    expect($titles[0])->toBe('Focused 1')
        ->and($titles[1])->toBe('Focused 2')
        ->and($titles[2])->toBe('Other 1');
});
