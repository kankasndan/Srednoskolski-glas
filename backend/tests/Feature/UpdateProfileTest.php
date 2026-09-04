<?php

use App\Models\City;
use App\Models\Forum;
use App\Models\School;
use App\Models\StudentData;
use App\Models\User;
use App\Models\Vocation;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function profileSchool(string $cityName, string $schoolName, string $slug): array
{
    $city = City::query()->create(['name' => $cityName]);
    $school = School::query()->create([
        'name' => $schoolName,
        'city_id' => $city->id,
    ]);
    $forum = Forum::query()->create([
        'name' => $school->name,
        'slug' => $slug,
        'description' => 'School forum',
        'type' => 'school',
        'school_id' => $school->id,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
    ]);

    return [$school, $forum];
}

it('requires authentication to update the profile', function () {
    $this->putJson('/api/me', [
        'image_url' => '/avatars/default-1.svg',
    ])->assertUnauthorized();
});

it('updates the avatar to a preset animal image', function () {
    $user = User::factory()->create([
        'imageUrl' => '/avatars/default-1.svg',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'image_url' => '/avatars/buv.svg',
        ])
        ->assertSuccessful()
        ->assertJsonPath('user.imageUrl', '/avatars/buv.svg');

    expect($user->fresh()->imageUrl)->toBe('/avatars/buv.svg');
});

it('resets a removed avatar to the first default', function () {
    $user = User::factory()->create([
        'imageUrl' => 'https://ik.imagekit.io/demo/avatar.png',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'image_url' => '',
        ])
        ->assertSuccessful()
        ->assertJsonPath('user.imageUrl', '/avatars/default-1.svg');
});

it('rejects an arbitrary remote avatar url', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'image_url' => 'https://evil.example/avatar.png',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['image_url']);
});

it('accepts an imagekit avatar without a media upload record', function () {
    config()->set('media.drivers.imagekit.url_endpoint', 'https://ik.imagekit.io/demo');

    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
        'imageUrl' => '/avatars/default-1.svg',
    ]);

    $imageKitUrl = 'https://ik.imagekit.io/demo/ik-genimg-prompt-student/avatar.png?tr=w-400,h-400';

    $this->actingAs($user)
        ->putJson('/api/me', [
            'image_url' => $imageKitUrl,
        ])
        ->assertSuccessful()
        ->assertJsonPath('user.imageUrl', $imageKitUrl);

    expect($user->fresh()->imageUrl)->toBe($imageKitUrl);
});

it('does not change the username', function () {
    $user = User::factory()->create([
        'username' => 'marko_2026',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'username' => 'hacker',
            'image_url' => '/avatars/default-3.svg',
        ])
        ->assertSuccessful();

    expect($user->fresh()->username)->toBe('marko_2026');
});

it('updates school information and moves school forum membership', function () {
    $user = User::factory()->create([
        'username' => 'marko_2026',
        'onboarding_completed_at' => now(),
    ]);

    [$oldSchool, $oldForum] = profileSchool('Скопје', 'Јосип Броз Тито', 'tito-skopje');
    [$newSchool, $newForum] = profileSchool('Битола', 'Јане Сандански', 'jane-bitola');
    $oldVocation = Vocation::query()->create(['name' => 'Електротехничка струка']);
    Vocation::query()->create(['name' => 'Здравствена струка']);

    StudentData::query()->create([
        'user_id' => $user->id,
        'school_id' => $oldSchool->id,
        'vocation_id' => $oldVocation->id,
        'grade' => 2,
    ]);
    $user->forums()->attach($oldForum->id);
    $oldForum->increment('members_count');

    $this->actingAs($user)
        ->putJson('/api/me', [
            'school' => 'Јане Сандански|Битола',
            'area' => 'Здравствена струка',
            'year' => '3',
        ])
        ->assertSuccessful()
        ->assertJsonPath('user.student_data.school.name', 'Јане Сандански')
        ->assertJsonPath('user.student_data.grade', 3);

    $user->refresh();

    expect($user->forums()->pluck('forums.id')->all())
        ->toContain($newForum->id)
        ->not->toContain($oldForum->id);

    expect($oldForum->fresh()->members_count)->toBe(0);
    expect($newForum->fresh()->members_count)->toBe(1);
});

afterEach(function () {
    $this->travelBack();
});

it('allows only one school change per academic year and unlocks in september', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-15 12:00:00', 'Europe/Skopje'));

    $user = User::factory()->create([
        'username' => 'marko_school',
        'onboarding_completed_at' => now(),
    ]);

    [$firstSchool, $firstForum] = profileSchool('Скопје', 'Јосип Броз Тито', 'tito-one-change');
    [$secondSchool, $secondForum] = profileSchool('Битола', 'Јане Сандански', 'jane-one-change');
    profileSchool('Штип', 'Никола Карев', 'karev-one-change');
    $vocation = Vocation::query()->create(['name' => 'Електротехничка струка']);

    StudentData::query()->create([
        'user_id' => $user->id,
        'school_id' => $firstSchool->id,
        'vocation_id' => $vocation->id,
        'grade' => 2,
    ]);
    $user->forums()->attach($firstForum->id);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'school' => 'Јане Сандански|Битола',
            'area' => 'Електротехничка струка',
            'year' => '2',
        ])
        ->assertSuccessful()
        ->assertJsonPath('user.student_data.school.name', 'Јане Сандански')
        ->assertJsonPath('capabilities.can_change_school', false)
        ->assertJsonPath('capabilities.school_change_unlocks_at', '2027-09-01')
        ->assertJsonPath('capabilities.school_forum_id', $secondForum->id);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'school' => 'Никола Карев|Штип',
            'area' => 'Електротехничка струка',
            'year' => '2',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['school'])
        ->assertJsonPath('errors.school.0', 'Не можеш да го промениш училиштето до септември 2027.');

    expect($user->fresh()->studentData->school_id)->toBe($secondSchool->id);

    $this->travelTo(CarbonImmutable::parse('2027-09-01 00:05:00', 'Europe/Skopje'));

    $this->actingAs($user)
        ->putJson('/api/me', [
            'school' => 'Никола Карев|Штип',
            'area' => 'Електротехничка струка',
            'year' => '2',
        ])
        ->assertSuccessful()
        ->assertJsonPath('user.student_data.school.name', 'Никола Карев')
        ->assertJsonPath('capabilities.can_change_school', false);
});

it('does not consume the school change when only vocation is updated', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-15 12:00:00', 'Europe/Skopje'));

    $user = User::factory()->create([
        'username' => 'marko_vocation',
        'onboarding_completed_at' => now(),
    ]);

    [$school, $forum] = profileSchool('Скопје', 'Јосип Броз Тито', 'tito-vocation');
    profileSchool('Битола', 'Јане Сандански', 'jane-vocation');
    $oldVocation = Vocation::query()->create(['name' => 'Електротехничка струка']);
    Vocation::query()->create(['name' => 'Здравствена струка']);

    StudentData::query()->create([
        'user_id' => $user->id,
        'school_id' => $school->id,
        'vocation_id' => $oldVocation->id,
        'grade' => 2,
    ]);
    $user->forums()->attach($forum->id);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'school' => 'Јосип Броз Тито|Скопје',
            'area' => 'Здравствена струка',
            'year' => '2',
        ])
        ->assertSuccessful()
        ->assertJsonPath('user.student_data.vocation.name', 'Здравствена струка')
        ->assertJsonPath('capabilities.can_change_school', true);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'school' => 'Јане Сандански|Битола',
            'area' => 'Здравствена струка',
            'year' => '2',
        ])
        ->assertSuccessful()
        ->assertJsonPath('capabilities.can_change_school', false);
});

it('does not consume the school-change slot on first school assignment', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-15 12:00:00', 'Europe/Skopje'));

    $user = User::factory()->create([
        'username' => 'marko_first',
        'onboarding_completed_at' => now(),
    ]);

    profileSchool('Скопје', 'Јосип Броз Тито', 'tito-first');
    profileSchool('Битола', 'Јане Сандански', 'jane-first');
    Vocation::query()->create(['name' => 'Електротехничка струка']);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'school' => 'Јосип Броз Тито|Скопје',
            'area' => 'Електротехничка струка',
            'year' => '2',
        ])
        ->assertSuccessful()
        ->assertJsonPath('capabilities.can_change_school', true);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'school' => 'Јане Сандански|Битола',
            'area' => 'Електротехничка струка',
            'year' => '2',
        ])
        ->assertSuccessful()
        ->assertJsonPath('capabilities.can_change_school', false);
});

it('rejects going to a lower or skipped school year', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-15 12:00:00', 'Europe/Skopje'));

    $user = User::factory()->create([
        'username' => 'marko_grade',
        'onboarding_completed_at' => now(),
    ]);

    [$school, $forum] = profileSchool('Скопје', 'Јосип Броз Тито', 'tito-grade');
    $vocation = Vocation::query()->create(['name' => 'Електротехничка струка']);

    StudentData::query()->create([
        'user_id' => $user->id,
        'school_id' => $school->id,
        'vocation_id' => $vocation->id,
        'grade' => 2,
    ]);
    $user->forums()->attach($forum->id);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'school' => 'Јосип Броз Тито|Скопје',
            'area' => 'Електротехничка струка',
            'year' => '1',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['year']);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'school' => 'Јосип Броз Тито|Скопје',
            'area' => 'Електротехничка струка',
            'year' => '4',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['year']);

    expect($user->fresh()->studentData->grade)->toBe(2);
});

it('allows keeping the year and only one promotion per academic year', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-15 12:00:00', 'Europe/Skopje'));

    $user = User::factory()->create([
        'username' => 'marko_promote',
        'onboarding_completed_at' => now(),
    ]);

    [$school, $forum] = profileSchool('Скопје', 'Јосип Броз Тито', 'tito-promote');
    $vocation = Vocation::query()->create(['name' => 'Електротехничка струка']);

    StudentData::query()->create([
        'user_id' => $user->id,
        'school_id' => $school->id,
        'vocation_id' => $vocation->id,
        'grade' => 2,
    ]);
    $user->forums()->attach($forum->id);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'school' => 'Јосип Броз Тито|Скопје',
            'area' => 'Електротехничка струка',
            'year' => '2',
        ])
        ->assertSuccessful()
        ->assertJsonPath('user.student_data.grade', 2)
        ->assertJsonPath('capabilities.min_grade', 2)
        ->assertJsonPath('capabilities.max_grade', 3);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'school' => 'Јосип Броз Тито|Скопје',
            'area' => 'Електротехничка струка',
            'year' => '3',
        ])
        ->assertSuccessful()
        ->assertJsonPath('user.student_data.grade', 3)
        ->assertJsonPath('capabilities.min_grade', 3)
        ->assertJsonPath('capabilities.max_grade', 3);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'school' => 'Јосип Броз Тито|Скопје',
            'area' => 'Електротехничка струка',
            'year' => '4',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['year']);
});
