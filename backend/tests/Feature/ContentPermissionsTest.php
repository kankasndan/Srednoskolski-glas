<?php

use App\Models\City;
use App\Models\Forum;
use App\Models\School;
use App\Models\StudentData;
use App\Models\Thread;
use App\Models\User;
use App\Support\SyncUserContentPermissions;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function makeGeneralForum(string $slug = 'opshti'): Forum
{
    return Forum::query()->create([
        'name' => 'Општ форум',
        'slug' => $slug,
        'description' => 'General',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);
}

function makeSchoolWithForum(string $schoolName, string $slug): array
{
    $city = City::query()->firstOrCreate(['name' => 'Скопје']);
    $school = School::query()->create([
        'name' => $schoolName,
        'city_id' => $city->id,
    ]);
    $forum = Forum::query()->create([
        'name' => $schoolName,
        'slug' => $slug,
        'description' => 'School forum',
        'type' => 'school',
        'school_id' => $school->id,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);

    return [$school, $forum];
}

function onboardWithSchool(User $user, School $school): User
{
    $user->forceFill([
        'username' => $user->username ?: 'ucenik_'.$user->id,
        'onboarding_completed_at' => now(),
    ])->save();

    StudentData::query()->updateOrCreate(
        ['user_id' => $user->id],
        ['school_id' => $school->id, 'vocation_id' => null, 'grade' => 3],
    );

    app(SyncUserContentPermissions::class)->handle($user->fresh(['studentData.school.forum']));

    return $user->fresh(['studentData.school.forum']);
}

function onboardWithoutSchool(User $user): User
{
    $user->forceFill([
        'username' => $user->username ?: 'gost_'.$user->id,
        'onboarding_completed_at' => now(),
    ])->save();

    app(SyncUserContentPermissions::class)->handle($user->fresh());

    return $user->fresh();
}

it('forbids guests from creating threads and comments', function () {
    $author = User::factory()->create();
    $forum = makeGeneralForum();
    $thread = Thread::query()->create([
        'title' => 'Hello',
        'description' => 'Body',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);

    $this->postJson('/api/threads', [
        'forum_id' => $forum->id,
        'title' => 'Guest thread',
        'description' => 'Nope',
    ])->assertUnauthorized();

    $this->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Guest comment',
    ])->assertUnauthorized();
});

it('forbids incomplete onboarding from creating threads and comments', function () {
    $user = User::factory()->create(['onboarding_completed_at' => null]);
    $author = User::factory()->create();
    $forum = makeGeneralForum('opshti-2');
    $thread = Thread::query()->create([
        'title' => 'Hello',
        'description' => 'Body',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);

    $this->actingAs($user)
        ->postJson('/api/threads', [
            'forum_id' => $forum->id,
            'title' => 'Incomplete',
            'description' => 'Nope',
        ])
        ->assertForbidden()
        ->assertJsonPath('message', \App\Http\Middleware\EnsureOnboardingCompleted::MESSAGE);

    $this->actingAs($user)
        ->postJson("/api/threads/{$thread->id}/comments", [
            'content' => 'Incomplete comment',
        ])
        ->assertForbidden()
        ->assertJsonPath('message', \App\Http\Middleware\EnsureOnboardingCompleted::MESSAGE);
});

it('forbids incomplete onboarding from follow and vote actions', function () {
    $user = User::factory()->create(['onboarding_completed_at' => null]);
    $other = User::factory()->create([
        'username' => 'other-user',
        'onboarding_completed_at' => now(),
    ]);
    $forum = makeGeneralForum('opshti-follow');
    $thread = Thread::query()->create([
        'title' => 'Hello',
        'description' => 'Body',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $other->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);

    $message = \App\Http\Middleware\EnsureOnboardingCompleted::MESSAGE;

    $this->actingAs($user)
        ->postJson("/api/u/{$other->username}/follow")
        ->assertForbidden()
        ->assertJsonPath('message', $message);

    $this->actingAs($user)
        ->postJson("/api/p/{$forum->slug}/follow")
        ->assertForbidden()
        ->assertJsonPath('message', $message);

    $this->actingAs($user)
        ->postJson("/api/threads/{$thread->id}/follow")
        ->assertForbidden()
        ->assertJsonPath('message', $message);

    $this->actingAs($user)
        ->postJson("/api/threads/{$thread->id}/upvote")
        ->assertForbidden()
        ->assertJsonPath('message', $message);
});

it('allows school members to create in general and own school, but not other schools', function () {
    [$schoolA, $forumA] = makeSchoolWithForum('Училиште А', 'school-a');
    [, $forumB] = makeSchoolWithForum('Училиште Б', 'school-b');
    $general = makeGeneralForum('opshti-3');

    $user = onboardWithSchool(User::factory()->create(), $schoolA);

    $this->actingAs($user)
        ->postJson('/api/threads', [
            'forum_id' => $general->id,
            'title' => 'General thread',
            'description' => 'Ok',
        ])
        ->assertCreated();

    $this->actingAs($user)
        ->postJson('/api/threads', [
            'forum_id' => $forumA->id,
            'title' => 'Own school thread',
            'description' => 'Ok',
        ])
        ->assertCreated();

    $this->actingAs($user)
        ->postJson('/api/threads', [
            'forum_id' => $forumB->id,
            'title' => 'Other school thread',
            'description' => 'Nope',
        ])
        ->assertForbidden();
});

it('allows users without school to comment but not create threads', function () {
    [$schoolA, $forumA] = makeSchoolWithForum('Училиште В', 'school-c');
    $general = makeGeneralForum('opshti-4');
    $author = onboardWithSchool(User::factory()->create(), $schoolA);

    $thread = Thread::query()->create([
        'title' => 'Existing',
        'description' => 'Body',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forumA->id,
        'is_anonymous' => false,
    ]);

    $user = onboardWithoutSchool(User::factory()->create());

    expect($user->can('create comments'))->toBeTrue()
        ->and($user->can('create threads'))->toBeFalse();

    $this->actingAs($user)
        ->postJson('/api/threads', [
            'forum_id' => $general->id,
            'title' => 'Should fail',
            'description' => 'Nope',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->postJson("/api/threads/{$thread->id}/comments", [
            'content' => 'Allowed comment',
        ])
        ->assertCreated();
});

it('exposes capabilities on /api/me', function () {
    [$school, $forum] = makeSchoolWithForum('Училиште Г', 'school-d');
    $user = onboardWithSchool(User::factory()->create(), $school);

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('capabilities.can_create_comments', true)
        ->assertJsonPath('capabilities.can_create_threads', true)
        ->assertJsonPath('capabilities.school_forum_id', $forum->id);
});
