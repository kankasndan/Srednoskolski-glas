<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function mentionSearchUser(string $username, bool $onboarded = true): User
{
    return User::factory()->create([
        'username' => $username,
        'onboarding_completed_at' => $onboarded ? now() : null,
    ]);
}

it('requires authentication to search users', function () {
    $this->getJson('/api/users/search?q=ana')
        ->assertUnauthorized();
});

it('returns username prefix matches and excludes the current user', function () {
    $viewer = mentionSearchUser('viewer_mk');
    mentionSearchUser('ana_k');
    mentionSearchUser('ana_m');
    mentionSearchUser('marko_p');
    mentionSearchUser('ana_incomplete', onboarded: false);

    $usernames = $this->actingAs($viewer)
        ->getJson('/api/users/search?q=ana')
        ->assertSuccessful()
        ->json('data.*.username');

    expect($usernames)->toBe(['ana_k', 'ana_m'])
        ->and($usernames)->not->toContain('viewer_mk')
        ->and($usernames)->not->toContain('ana_incomplete');
});

it('returns onboarded users when the query is empty', function () {
    $viewer = mentionSearchUser('viewer_mk');
    mentionSearchUser('ana_k');
    mentionSearchUser('marko_p');

    $usernames = $this->actingAs($viewer)
        ->getJson('/api/users/search')
        ->assertSuccessful()
        ->json('data.*.username');

    expect($usernames)->toBe(['ana_k', 'marko_p']);
});

it('ranks followed users first', function () {
    $viewer = mentionSearchUser('viewer_mk');
    mentionSearchUser('ana_k');
    $anaM = mentionSearchUser('ana_m');
    $elena = mentionSearchUser('elena_s');
    $marko = mentionSearchUser('marko_p');
    $viewer->following()->syncWithoutDetaching([$marko->id, $elena->id, $anaM->id]);

    $empty = $this->actingAs($viewer)
        ->getJson('/api/users/search')
        ->assertSuccessful()
        ->json('data.*.username');

    $prefixed = $this->actingAs($viewer)
        ->getJson('/api/users/search?q=ana')
        ->assertSuccessful()
        ->json('data.*.username');

    expect($empty)->toBe(['ana_m', 'elena_s', 'marko_p', 'ana_k'])
        ->and($prefixed)->toBe(['ana_m', 'ana_k']);
});

it('returns only username and avatar fields', function () {
    $viewer = mentionSearchUser('viewer_mk');
    mentionSearchUser('ana_k');

    $this->actingAs($viewer)
        ->getJson('/api/users/search?q=ana')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                ['id', 'username', 'imageUrl'],
            ],
        ])
        ->assertJsonMissingPath('data.0.email')
        ->assertJsonMissingPath('data.0.school');
});

it('rejects invalid username characters', function () {
    $viewer = mentionSearchUser('viewer_mk');

    $this->actingAs($viewer)
        ->getJson('/api/users/search?q=ana!')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['q']);
});
