<?php

use App\Models\City;
use App\Models\Forum;
use App\Models\School;
use App\Models\User;
use App\Models\Vocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('follows the school forum when a student completes onboarding', function () {
    $user = User::factory()->create([
        'username' => null,
        'onboarding_completed_at' => null,
    ]);

    $city = City::query()->create(['name' => 'Скопје']);
    $school = School::query()->create([
        'name' => 'Георги Димитров',
        'city_id' => $city->id,
    ]);
    $forum = Forum::query()->create([
        'name' => $school->name,
        'slug' => 'georgi-dimitrov-skopje',
        'description' => 'School forum',
        'type' => 'school',
        'school_id' => $school->id,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
    ]);
    Vocation::query()->create(['name' => 'Електротехничка струка']);

    $this->actingAs($user)
        ->putJson('/api/onboarding', [
            'username' => 'nov_ucenik',
            'is_student' => true,
            'school' => 'Георги Димитров|Скопје',
            'area' => 'Електротехничка струка',
            'year' => 'Трета',
        ])
        ->assertSuccessful();

    expect($user->fresh()->forums()->pluck('forums.id')->all())
        ->toContain($forum->id);

    expect($forum->fresh()->members_count)->toBe(1);
});

it('does not duplicate membership when onboarding is submitted again', function () {
    $user = User::factory()->create([
        'username' => 'existing_user',
        'onboarding_completed_at' => null,
    ]);

    $city = City::query()->create(['name' => 'Битола']);
    $school = School::query()->create([
        'name' => 'Јане Сандански',
        'city_id' => $city->id,
    ]);
    $forum = Forum::query()->create([
        'name' => $school->name,
        'slug' => 'jane-sandanski-bitola',
        'description' => 'School forum',
        'type' => 'school',
        'school_id' => $school->id,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
    ]);
    Vocation::query()->create(['name' => 'Здравствена струка']);

    $payload = [
        'username' => 'existing_user',
        'is_student' => true,
        'school' => 'Јане Сандански|Битола',
        'area' => 'Здравствена струка',
        'year' => 'Втора',
    ];

    $this->actingAs($user)->putJson('/api/onboarding', $payload)->assertSuccessful();
    $this->actingAs($user)->putJson('/api/onboarding', $payload)->assertSuccessful();

    expect($user->fresh()->forums()->where('forums.id', $forum->id)->count())->toBe(1);
    expect($forum->fresh()->members_count)->toBe(1);
});
