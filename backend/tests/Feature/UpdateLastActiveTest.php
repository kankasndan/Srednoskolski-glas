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
    // Whole seconds only: the column has no sub-second precision to compare against.
    $recent = now()->subMinute()->startOfSecond();
    $user = User::factory()->create(['last_active_at' => $recent]);

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful();

    expect($user->fresh()->last_active_at->equalTo($recent))->toBeTrue();
});
