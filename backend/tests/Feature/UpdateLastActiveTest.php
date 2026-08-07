<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('updates last_active_at for authenticated api requests', function () {
    $user = User::factory()->create(['last_active_at' => null]);

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful();

    expect($user->fresh()->last_active_at)->not->toBeNull();
});

it('does not thrash last_active_at within two minutes', function () {
    $recent = now()->subMinute();
    $user = User::factory()->create(['last_active_at' => $recent]);

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful();

    expect($user->fresh()->last_active_at->equalTo($recent))->toBeTrue();
});
