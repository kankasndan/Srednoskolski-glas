<?php

use App\Models\City;
use App\Models\Forum;
use App\Models\School;
use App\Models\StudentData;
use App\Models\User;
use App\Models\Vocation;
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

it('updates the avatar to a default image', function () {
    $user = User::factory()->create([
        'imageUrl' => '/avatars/default-2.svg',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'image_url' => '/avatars/default-1.svg',
        ])
        ->assertSuccessful()
        ->assertJsonPath('user.imageUrl', '/avatars/default-1.svg');

    expect($user->fresh()->imageUrl)->toBe('/avatars/default-1.svg');
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

it('rejects an invalid avatar path', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->putJson('/api/me', [
            'image_url' => '/evil/avatar.svg',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['image_url']);
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
