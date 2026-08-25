<?php

use App\Contracts\ContentModerator;
use App\Contracts\MediaStorage;
use App\Models\MediaUpload;
use App\Models\User;
use App\Services\Media\MediaManager;
use App\Support\Moderation\ModerationVerdict;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('moderation.enabled', false);
    config()->set('moderation.drivers.gemini.api_key', 'test-gemini-key');
    config()->set('media.default', 's3');
    config()->set('media.drivers.s3', [
        'disk' => 's3',
        'visibility' => 'public',
    ]);

    Storage::fake('s3');

    app(MediaManager::class)->forgetDrivers();
    app()->forgetInstance(MediaStorage::class);
});

function fakeGeneratedPng(): string
{
    return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
}

function fakeGeminiAvatarResponse(): void
{
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [[
                        'inlineData' => [
                            'mimeType' => 'image/png',
                            'data' => base64_encode(fakeGeneratedPng()),
                        ],
                    ]],
                ],
            ]],
        ]),
    ]);
}

it('generates an avatar from the onboarding photo and stores it on the user', function () {
    fakeGeminiAvatarResponse();

    $user = User::factory()->create([
        'imageUrl' => '/avatars/default-1.svg',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->post('/api/onboarding/avatar', [
            'file' => UploadedFile::fake()->image('me.jpg', 40, 40),
        ])
        ->assertCreated()
        ->assertJsonPath('user.id', $user->id);

    $user->refresh();

    expect($user->imageUrl)->not->toBe('/avatars/default-1.svg')
        ->and($user->imageUrl)->not->toBeEmpty();

    expect(MediaUpload::query()->where('user_id', $user->id)->where('directory', 'avatars')->count())->toBe(1);

    Http::assertSent(function ($request) {
        $body = $request->body();

        return str_contains($request->url(), 'generateContent')
            && str_contains($body, 'Modern streetwear sticker-art')
            && ! str_contains($body, '"imageConfig"');
    });
});

it('does not keep the original photo as a media upload', function () {
    fakeGeminiAvatarResponse();

    $user = User::factory()->create(['onboarding_completed_at' => now()]);

    $this->actingAs($user)
        ->post('/api/onboarding/avatar', [
            'file' => UploadedFile::fake()->image('selfie.jpg', 40, 40),
        ])
        ->assertCreated();

    expect(MediaUpload::query()->where('user_id', $user->id)->count())->toBe(1);
});

it('requires authentication', function () {
    $this->post('/api/onboarding/avatar', [
        'file' => UploadedFile::fake()->image('me.jpg'),
    ])->assertUnauthorized();
});

it('requires completed onboarding', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => null,
    ]);

    $this->actingAs($user)
        ->post('/api/onboarding/avatar', [
            'file' => UploadedFile::fake()->image('me.jpg'),
        ])
        ->assertForbidden();
});

it('rejects a non-image file', function () {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);

    $this->actingAs($user)
        ->post('/api/onboarding/avatar', [
            'file' => UploadedFile::fake()->create('notes.pdf', 20, 'application/pdf'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});

it('refuses the photo when gemini moderation rejects it', function () {
    config()->set('moderation.enabled', true);

    $this->mock(ContentModerator::class, function ($mock) {
        $mock->shouldReceive('review')
            ->once()
            ->andReturn(ModerationVerdict::escalate('possible minor', ['PROHIBITED_CONTENT']));
    });

    $user = User::factory()->create(['onboarding_completed_at' => now()]);

    $original = $user->imageUrl;

    $this->actingAs($user)
        ->post('/api/onboarding/avatar', [
            'file' => UploadedFile::fake()->image('me.jpg', 40, 40),
        ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.file.0', 'Оваа датотека не е дозволена.');

    expect($user->fresh()->imageUrl)->toBe($original);
    expect(MediaUpload::query()->where('user_id', $user->id)->count())->toBe(0);
});

it('tells the user when photo moderation is overloaded', function () {
    config()->set('moderation.enabled', true);

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'error' => [
                'code' => 503,
                'status' => 'UNAVAILABLE',
                'message' => 'This model is currently experiencing high demand.',
            ],
        ], 503),
    ]);

    $user = User::factory()->create(['onboarding_completed_at' => now()]);

    $this->actingAs($user)
        ->post('/api/onboarding/avatar', [
            'file' => UploadedFile::fake()->image('me.jpg', 40, 40),
        ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.file.0', 'Gemini е зафатен. Почекај малку и обиди се повторно.');
});

it('returns a user-facing error when gemini returns no image', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [['text' => 'I cannot generate that.']],
                ],
            ]],
        ]),
    ]);

    $user = User::factory()->create(['onboarding_completed_at' => now()]);

    $this->actingAs($user)
        ->post('/api/onboarding/avatar', [
            'file' => UploadedFile::fake()->image('me.jpg', 40, 40),
        ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.file.0', 'Не успеавме да го создадеме аватарот. Обиди се повторно.');
});

it('explains when gemini image quota is not available', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'error' => [
                'code' => 429,
                'message' => 'Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.5-flash-preview-image',
                'status' => 'RESOURCE_EXHAUSTED',
            ],
        ], 429),
    ]);

    $user = User::factory()->create(['onboarding_completed_at' => now()]);

    $this->actingAs($user)
        ->post('/api/onboarding/avatar', [
            'file' => UploadedFile::fake()->image('me.jpg', 40, 40),
        ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.file.0', 'Генерирањето аватари бара платена Gemini квота за слики. Провери billing за проектот на API клучот.');
});

it('explains when gemini_image_model cannot draw images', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'error' => [
                'code' => 400,
                'message' => 'This model only supports text output.',
                'status' => 'INVALID_ARGUMENT',
            ],
        ], 400),
    ]);

    $user = User::factory()->create(['onboarding_completed_at' => now()]);

    $this->actingAs($user)
        ->post('/api/onboarding/avatar', [
            'file' => UploadedFile::fake()->image('me.jpg', 40, 40),
        ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.file.0', 'GEMINI_IMAGE_MODEL мора да биде модел што црта слики (на пр. gemini-2.5-flash-image), не flash-lite.');
});
