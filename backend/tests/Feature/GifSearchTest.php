<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.giphy.key', 'test-giphy-key');
    Http::preventStrayRequests();
});

function fakeGiphyResponse(): array
{
    return [
        'data' => [
            [
                'id' => 'abc123',
                'title' => 'Happy cat',
                'images' => [
                    'fixed_width' => [
                        'url' => 'https://media.giphy.com/media/abc123/200w.gif',
                    ],
                ],
            ],
            [
                'id' => 'skip-me',
                'title' => 'Missing url',
                'images' => [],
            ],
        ],
    ];
}

it('requires authentication to search gifs', function () {
    $this->getJson('/api/gifs')->assertUnauthorized();
});

it('rejects users who have not finished onboarding', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => null,
    ]);

    $this->actingAs($user)
        ->getJson('/api/gifs')
        ->assertForbidden();
});

it('returns trending gifs when the query is empty', function () {
    Http::fake([
        'api.giphy.com/v1/gifs/trending*' => Http::response(fakeGiphyResponse()),
    ]);

    $this->actingAs(User::factory()->create())
        ->getJson('/api/gifs')
        ->assertOk()
        ->assertJsonPath('data.0.id', 'abc123')
        ->assertJsonPath('data.0.url', 'https://media.giphy.com/media/abc123/200w.gif')
        ->assertJsonPath('data.0.title', 'Happy cat')
        ->assertJsonCount(1, 'data')
        ->assertJsonMissing(['api_key' => 'test-giphy-key']);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.giphy.com/v1/gifs/trending')
            && $request['api_key'] === 'test-giphy-key'
            && $request['rating'] === 'g'
            && ! isset($request['q']);
    });
});

it('searches gifs when a query is provided', function () {
    Http::fake([
        'api.giphy.com/v1/gifs/search*' => Http::response(fakeGiphyResponse()),
    ]);

    $this->actingAs(User::factory()->create())
        ->getJson('/api/gifs?q=cats')
        ->assertOk()
        ->assertJsonPath('data.0.id', 'abc123');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.giphy.com/v1/gifs/search')
            && $request['q'] === 'cats';
    });
});

it('returns 502 when giphy is unavailable', function () {
    Http::fake([
        'api.giphy.com/*' => Http::response(['message' => 'nope'], 500),
    ]);

    $this->actingAs(User::factory()->create())
        ->getJson('/api/gifs')
        ->assertStatus(502)
        ->assertJsonPath('message', 'Не успеа вчитувањето на GIF-овите.');
});
