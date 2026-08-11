<?php

use App\Models\City;
use App\Models\Forum;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeGeneralForum(array $overrides = []): Forum
{
    return Forum::query()->create(array_merge([
        'name' => 'Државна матура',
        'slug' => 'drzhavna_matura',
        'description' => 'General forum',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ], $overrides));
}

function makeSchoolForum(): Forum
{
    $city = City::query()->create(['name' => 'Скопје']);
    $school = School::query()->create([
        'name' => 'Георги Димитров',
        'city_id' => $city->id,
    ]);

    return Forum::query()->create([
        'name' => $school->name,
        'slug' => 'georgi-dimitrov-skopje',
        'description' => 'School forum',
        'type' => 'school',
        'school_id' => $school->id,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 1,
        'threads_count' => 0,
    ]);
}

it('requires authentication to follow a forum', function () {
    $forum = makeGeneralForum();

    $this->postJson("/api/p/{$forum->slug}/follow")->assertUnauthorized();
});

it('requires authentication to unfollow a forum', function () {
    $forum = makeGeneralForum();

    $this->deleteJson("/api/p/{$forum->slug}/follow")->assertUnauthorized();
});

it('allows following a general forum', function () {
    $user = User::factory()->create();
    $forum = makeGeneralForum();

    $this->actingAs($user)
        ->postJson("/api/p/{$forum->slug}/follow")
        ->assertSuccessful()
        ->assertJsonPath('data.is_following', true)
        ->assertJsonPath('data.members_count', 1);

    expect($user->fresh()->forums()->pluck('forums.id')->all())->toContain($forum->id);
    expect($forum->fresh()->members_count)->toBe(1);
});

it('does not double-count members when following twice', function () {
    $user = User::factory()->create();
    $forum = makeGeneralForum();

    $this->actingAs($user)->postJson("/api/p/{$forum->slug}/follow")->assertSuccessful();
    $this->actingAs($user)
        ->postJson("/api/p/{$forum->slug}/follow")
        ->assertSuccessful()
        ->assertJsonPath('data.is_following', true)
        ->assertJsonPath('data.members_count', 1);

    expect($user->forums()->where('forums.id', $forum->id)->count())->toBe(1);
    expect($forum->fresh()->members_count)->toBe(1);
});

it('allows unfollowing a general forum', function () {
    $user = User::factory()->create();
    $forum = makeGeneralForum(['members_count' => 1]);
    $user->forums()->attach($forum->id);

    $this->actingAs($user)
        ->deleteJson("/api/p/{$forum->slug}/follow")
        ->assertSuccessful()
        ->assertJsonPath('data.is_following', false)
        ->assertJsonPath('data.members_count', 0);

    expect($user->fresh()->forums()->pluck('forums.id')->all())->not->toContain($forum->id);
    expect($forum->fresh()->members_count)->toBe(0);
});

it('allows following another school forum', function () {
    $user = User::factory()->create();
    $forum = makeSchoolForum();

    $this->actingAs($user)
        ->postJson("/api/p/{$forum->slug}/follow")
        ->assertSuccessful()
        ->assertJsonPath('data.is_following', true)
        ->assertJsonPath('data.members_count', 2);

    expect($user->fresh()->forums()->pluck('forums.id')->all())->toContain($forum->id);
});

it('allows unfollowing another school forum', function () {
    $user = User::factory()->create();
    $forum = makeSchoolForum();
    $user->forums()->attach($forum->id);

    $this->actingAs($user)
        ->deleteJson("/api/p/{$forum->slug}/follow")
        ->assertSuccessful()
        ->assertJsonPath('data.is_following', false);

    expect($user->fresh()->forums()->pluck('forums.id')->all())->not->toContain($forum->id);
});

it('rejects unfollowing the user own school forum', function () {
    $user = User::factory()->create([
        'username' => 'ucenik_own',
        'onboarding_completed_at' => now(),
    ]);
    $forum = makeSchoolForum();

    \App\Models\StudentData::query()->create([
        'user_id' => $user->id,
        'school_id' => $forum->school_id,
        'vocation_id' => null,
        'grade' => 3,
    ]);
    $user->forums()->attach($forum->id);

    $this->actingAs($user->fresh(['studentData.school.forum']))
        ->deleteJson("/api/p/{$forum->slug}/follow")
        ->assertUnprocessable();

    expect($user->fresh()->forums()->pluck('forums.id')->all())->toContain($forum->id);
});

it('includes is_following on forum detail when authenticated', function () {
    $user = User::factory()->create();
    $forum = makeGeneralForum();
    $user->forums()->attach($forum->id);

    $this->actingAs($user)
        ->getJson("/api/p/{$forum->slug}")
        ->assertSuccessful()
        ->assertJsonPath('data.forum.is_following', true);
});

it('omits is_following on forum detail for guests', function () {
    $forum = makeGeneralForum();

    $this->getJson("/api/p/{$forum->slug}")
        ->assertSuccessful()
        ->assertJsonMissingPath('data.forum.is_following');
});
