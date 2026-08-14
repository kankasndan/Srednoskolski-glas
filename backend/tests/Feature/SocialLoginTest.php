<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

function fakeSocialiteUser(string $provider, string $id, ?string $email): void
{
    $socialiteUser = (new SocialiteUser)->map([
        'id' => $id,
        'email' => $email,
    ]);

    $driver = Mockery::mock(SocialiteProvider::class);
    $driver->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')->with($provider)->andReturn($driver);
}

it('creates a new user from a first-time google login', function () {
    fakeSocialiteUser('google', 'google-new-1', 'nova@example.com');

    $this->get('/api/auth/google/callback')
        ->assertRedirect('http://localhost:3000/auth/callback?onboarding=required');

    $user = User::query()->where('email', 'nova@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->provider)->toBe('google')
        ->and($user->provider_id)->toBe('google-new-1')
        ->and($user->password)->toBeNull()
        ->and($user->email_verified_at)->not->toBeNull();

    $this->assertAuthenticatedAs($user);
});

it('logs an existing social user back in without creating a duplicate', function () {
    $user = User::factory()->create([
        'email' => 'returning@example.com',
        'provider' => 'google',
        'provider_id' => 'google-returning-1',
        'password' => null,
        'onboarding_completed_at' => now(),
    ]);

    fakeSocialiteUser('google', 'google-returning-1', 'returning@example.com');

    $this->get('/api/auth/google/callback')
        ->assertRedirect('http://localhost:3000/auth/callback?onboarding=complete');

    expect(User::query()->where('email', 'returning@example.com')->count())->toBe(1);

    $this->assertAuthenticatedAs($user);
});

it('does not attach a new provider to an account that already uses the email', function () {
    $existing = User::factory()->create([
        'email' => 'shared@example.com',
        'provider' => 'google',
        'provider_id' => 'google-original',
        'password' => null,
    ]);

    fakeSocialiteUser('facebook', 'facebook-attacker', 'shared@example.com');

    $this->get('/api/auth/facebook/callback')
        ->assertRedirect('http://localhost:3000/login?error=email_in_use');

    $existing->refresh();

    expect($existing->provider)->toBe('google')
        ->and($existing->provider_id)->toBe('google-original')
        ->and(User::query()->where('provider_id', 'facebook-attacker')->exists())->toBeFalse();

    $this->assertGuest();
});

it('does not take over a password account that shares the social email', function () {
    $staff = User::factory()->create([
        'email' => 'admin@srednoskolskiglas.mk',
        'provider' => null,
        'provider_id' => null,
        'role' => 'super_admin',
    ]);

    fakeSocialiteUser('google', 'google-takeover', 'admin@srednoskolskiglas.mk');

    $this->get('/api/auth/google/callback')
        ->assertRedirect('http://localhost:3000/login?error=email_in_use');

    $staff->refresh();

    expect($staff->provider)->toBeNull()
        ->and($staff->provider_id)->toBeNull()
        ->and($staff->role)->toBe('super_admin')
        ->and(User::query()->count())->toBe(1);

    $this->assertGuest();
});

it('creates a user with a synthetic email when the provider sends none', function () {
    fakeSocialiteUser('facebook', 'fb-no-email', null);

    $this->get('/api/auth/facebook/callback')
        ->assertRedirect('http://localhost:3000/auth/callback?onboarding=required');

    $user = User::query()->where('provider_id', 'fb-no-email')->first();

    expect($user)->not->toBeNull()
        ->and($user->email)->toBe('facebook-fb-no-email@social.local')
        ->and($user->email_verified_at)->toBeNull();

    $this->assertAuthenticatedAs($user);
});
