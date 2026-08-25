<?php

use App\Models\Sanction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('includes a 7-day ban notice on /api/me until it is acknowledged', function () {
    $user = User::factory()->create();
    $sanction = Sanction::factory()->create(['user_id' => $user->id]);

    $first = $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice.id', $sanction->id)
        ->assertJsonPath('sanction_notice.type', '7-day')
        ->assertJsonPath('active_ban.id', $sanction->id)
        ->assertJsonPath('active_ban.type', '7-day');

    expect($first->json('sanction_notice.expires_at'))->not->toBeNull();

    $this->actingAs($user)
        ->postJson("/api/me/sanctions/{$sanction->id}/acknowledge")
        ->assertNoContent();

    $acknowledged = $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice', null)
        ->assertJsonPath('active_ban.id', $sanction->id)
        ->assertJsonPath('active_ban.type', '7-day');

    expect($acknowledged->json('active_ban.expires_at'))->not->toBeNull();
});

it('includes a permanent ban notice on /api/me', function () {
    $user = User::factory()->create();
    $sanction = Sanction::factory()->permanent()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice.id', $sanction->id)
        ->assertJsonPath('sanction_notice.type', 'permanent_ban')
        ->assertJsonPath('active_ban.id', $sanction->id)
        ->assertJsonPath('active_ban.type', 'permanent_ban')
        ->assertJsonPath('active_ban.expires_at', null);
});

it('tells the user the ban ended after a timed ban expires', function () {
    $user = User::factory()->create();
    $sanction = Sanction::factory()->expired()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice.id', $sanction->id)
        ->assertJsonPath('sanction_notice.type', 'ban_ended')
        ->assertJsonPath('active_ban', null);

    $this->actingAs($user)
        ->postJson("/api/me/sanctions/{$sanction->id}/acknowledge")
        ->assertNoContent();

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice', null);
});

it('shows ban_ended after an acknowledged timed ban expires', function () {
    $user = User::factory()->create();
    $sanction = Sanction::factory()->create([
        'user_id' => $user->id,
        'acknowledged_at' => now()->subDays(3),
        'expires_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice.id', $sanction->id)
        ->assertJsonPath('sanction_notice.type', 'ban_ended')
        ->assertJsonPath('active_ban', null);
});

it('shows ban_ended after staff remove an active ban', function () {
    $user = User::factory()->create();
    $sanction = Sanction::factory()->permanent()->create(['user_id' => $user->id]);
    $sanction->update(['revoked_at' => now()]);
    $sanction->delete();

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice.id', $sanction->id)
        ->assertJsonPath('sanction_notice.type', 'ban_ended');

    $this->actingAs($user)
        ->postJson("/api/me/sanctions/{$sanction->id}/acknowledge")
        ->assertNoContent();

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice', null);
});

it('lets a banned user acknowledge the notice', function () {
    $user = User::factory()->create();
    $sanction = Sanction::factory()->permanent()->create(['user_id' => $user->id]);

    expect($user->isBanned())->toBeTrue();

    $this->actingAs($user)
        ->postJson("/api/me/sanctions/{$sanction->id}/acknowledge")
        ->assertNoContent();
});

it('does not let a user acknowledge someone elses sanction', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $sanction = Sanction::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user)
        ->postJson("/api/me/sanctions/{$sanction->id}/acknowledge")
        ->assertNotFound();
});

it('includes an unacknowledged warning when the user is not banned', function () {
    $user = User::factory()->create();
    $warning = Sanction::factory()->warning()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice.id', $warning->id)
        ->assertJsonPath('sanction_notice.type', 'warning');
});

it('includes expires_at on a custom-duration ban notice', function () {
    $user = User::factory()->create();
    $sanction = Sanction::factory()->custom(14)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice.id', $sanction->id)
        ->assertJsonPath('sanction_notice.type', 'custom')
        ->assertJsonPath('active_ban.id', $sanction->id)
        ->assertJsonPath('active_ban.type', 'custom');

    expect($response->json('sanction_notice.expires_at'))->not->toBeNull()
        ->and($response->json('active_ban.expires_at'))->not->toBeNull();
});
