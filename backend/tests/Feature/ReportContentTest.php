<?php

use App\Models\City;
use App\Models\Forum;
use App\Models\Report;
use App\Models\School;
use App\Models\Thread;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function reportForum(): Forum
{
    return Forum::query()->create([
        'name' => 'Општи дискусии',
        'slug' => 'opshti-report',
        'description' => 'General',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);
}

function reportUser(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'onboarding_completed_at' => now(),
    ], $overrides));
}

it('lets different users report the same thread once each', function () {
    $forum = reportForum();
    $author = reportUser();
    $first = reportUser();
    $second = reportUser();
    $staff = reportUser();
    $staff->assignRole('moderator');

    $thread = Thread::forceCreate([
        'title' => 'Reported thread',
        'description' => 'Body',
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
        'upvotes' => 0,
        'views' => 0,
    ]);

    $this->actingAs($first)
        ->postJson("/api/threads/{$thread->id}/report", ['reason' => 'Спам'])
        ->assertCreated();

    $this->actingAs($first)
        ->postJson("/api/threads/{$thread->id}/report", ['reason' => 'Дезинформација'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['reason']);

    $this->actingAs($second)
        ->postJson("/api/threads/{$thread->id}/report", ['reason' => 'Навредлива содржина'])
        ->assertCreated();

    expect(Report::query()->where('reportable_id', $thread->id)->count())->toBe(2)
        ->and($staff->fresh()->unreadNotifications)->toHaveCount(1)
        ->and($staff->fresh()->unreadNotifications->first()->data['count'])->toBe(2)
        ->and(Report::pendingTargetCount())->toBe(1);
});

it('hides school and grade on public profiles for guests', function () {
    $user = reportUser(['username' => 'jane_public']);

    $this->getJson("/api/u/{$user->username}")
        ->assertOk()
        ->assertJsonPath('data.user.student_data', null);

    $this->actingAs($user)
        ->getJson("/api/u/{$user->username}")
        ->assertOk();
});

it('requires a session to read a school forum', function () {
    $city = City::query()->create(['name' => 'Скопје']);
    $school = School::query()->create([
        'name' => 'Тест училиште',
        'city_id' => $city->id,
    ]);
    $forum = Forum::query()->create([
        'name' => $school->name,
        'slug' => 'test-school-forum',
        'description' => 'School',
        'type' => 'school',
        'school_id' => $school->id,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);

    $this->getJson("/api/p/{$forum->slug}")->assertUnauthorized();
    $this->getJson("/api/p/{$forum->slug}/threads")->assertUnauthorized();

    $this->actingAs(reportUser())
        ->getJson("/api/p/{$forum->slug}")
        ->assertOk();
});

it('rejects spa uploads into the forums directory', function () {
    $this->actingAs(reportUser())
        ->post('/api/media', [
            'file' => UploadedFile::fake()->create('avatar.jpg', 120, 'image/jpeg'),
            'directory' => 'forums',
        ], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['directory']);
});

it('omits provider internals from /api/me', function () {
    $user = reportUser();
    $user->forceFill([
        'provider' => 'google',
        'provider_id' => 'secret-id',
        'role' => 'user',
    ])->save();

    $payload = $this->actingAs($user)
        ->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('user.email', $user->email)
        ->json('user');

    expect($payload)->not->toHaveKeys(['provider_id', 'provider', 'role']);
});
