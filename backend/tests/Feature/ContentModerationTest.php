<?php

use App\Contracts\ContentModerator;
use App\Contracts\MediaStorage;
use App\Services\Media\MediaManager;
use App\Services\Moderation\VideoFrameSampler;
use App\Support\Moderation\ModerationDecision;
use App\Support\Moderation\ModerationVerdict;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    config()->set('moderation.enabled', true);
    config()->set('moderation.on_failure', 'reject');
    config()->set('moderation.drivers.gemini.api_key', 'test-gemini-key');
    config()->set('media.default', 's3');
    config()->set('media.drivers.s3', [
        'disk' => 's3',
        'visibility' => 'public',
    ]);

    Storage::fake('s3');

    app(MediaManager::class)->forgetDrivers();
    app()->forgetInstance(MediaStorage::class);
    app()->forgetInstance(ContentModerator::class);
});

it('uploads a file after gemini allows it', function () {
    $this->mock(ContentModerator::class, function ($mock) {
        $mock->shouldReceive('review')
            ->once()
            ->andReturn(ModerationVerdict::allow('ordinary photo'));
    });

    $media = app(MediaStorage::class)->upload(
        UploadedFile::fake()->image('holiday.jpg'),
        'uploads',
    );

    Storage::disk('s3')->assertExists($media->path);
});

it('does not store a file gemini rejects', function () {
    $this->mock(ContentModerator::class, function ($mock) {
        $mock->shouldReceive('review')
            ->once()
            ->andReturn(new ModerationVerdict(
                ModerationDecision::Reject,
                'nudity',
                ['sexual_display'],
            ));
    });

    expect(fn () => app(MediaStorage::class)->upload(
        UploadedFile::fake()->image('nsfw.jpg'),
        'uploads',
    ))->toThrow(ValidationException::class);

    expect(Storage::disk('s3')->allFiles())->toBeEmpty();
});

it('does not store a file gemini escalates', function () {
    $this->mock(ContentModerator::class, function ($mock) {
        $mock->shouldReceive('review')
            ->once()
            ->andReturn(ModerationVerdict::escalate('possible minor', ['PROHIBITED_CONTENT']));
    });

    try {
        app(MediaStorage::class)->upload(
            UploadedFile::fake()->image('flagged.jpg'),
            'uploads',
        );
        $this->fail('Expected the upload to be refused.');
    } catch (ValidationException $exception) {
        expect($exception->errors()['file'][0])->toBe('Оваа датотека не е дозволена.');
    }

    expect(Storage::disk('s3')->allFiles())->toBeEmpty();
});

it('refuses the upload when gemini is unreachable', function () {
    $this->mock(ContentModerator::class, function ($mock) {
        $mock->shouldReceive('review')->once()->andThrow(new RuntimeException('timeout'));
    });

    try {
        app(MediaStorage::class)->upload(
            UploadedFile::fake()->image('photo.jpg'),
            'uploads',
        );
        $this->fail('Expected the upload to be refused.');
    } catch (ValidationException $exception) {
        expect($exception->errors()['file'][0])->toBe('Проверката на датотеката не успеа. Обиди се повторно.');
    }

    expect(Storage::disk('s3')->allFiles())->toBeEmpty();
});

it('lets the upload through when gemini is unreachable and fail-open is configured', function () {
    config()->set('moderation.on_failure', 'allow');

    $this->mock(ContentModerator::class, function ($mock) {
        $mock->shouldReceive('review')->once()->andThrow(new RuntimeException('timeout'));
    });

    $media = app(MediaStorage::class)->upload(
        UploadedFile::fake()->image('photo.jpg'),
        'uploads',
    );

    Storage::disk('s3')->assertExists($media->path);
});

it('skips word documents that gemini cannot classify', function () {
    $this->mock(ContentModerator::class, function ($mock) {
        $mock->shouldReceive('review')->never();
    });

    $media = app(MediaStorage::class)->upload(
        UploadedFile::fake()->create('notes.docx', 20, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        'uploads',
    );

    Storage::disk('s3')->assertExists($media->path);
});

it('treats a blocked gemini prompt as an escalation', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'promptFeedback' => ['blockReason' => 'PROHIBITED_CONTENT'],
        ]),
    ]);

    $verdict = app(ContentModerator::class)->review(UploadedFile::fake()->image('blocked.jpg'));

    expect($verdict->decision)->toBe(ModerationDecision::Escalate)
        ->and($verdict->categories)->toContain('PROHIBITED_CONTENT');
});

it('treats a withheld gemini answer as an escalation', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'finishReason' => 'IMAGE_PROHIBITED_CONTENT',
            ]],
        ]),
    ]);

    $verdict = app(ContentModerator::class)->review(UploadedFile::fake()->image('blocked.jpg'));

    expect($verdict->decision)->toBe(ModerationDecision::Escalate)
        ->and($verdict->categories)->toContain('IMAGE_PROHIBITED_CONTENT');
});

it('parses an allow verdict from gemini json', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'finishReason' => 'STOP',
                'content' => [
                    'parts' => [[
                        'text' => json_encode([
                            'decision' => 'allow',
                            'reason' => 'school sports photo',
                            'categories' => [],
                        ]),
                    ]],
                ],
            ]],
        ]),
    ]);

    $verdict = app(ContentModerator::class)->review(UploadedFile::fake()->image('sport.jpg'));

    expect($verdict->isAllowed())->toBeTrue()
        ->and($verdict->reason)->toBe('school sports photo');
});

it('parses a reject verdict from gemini json', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'finishReason' => 'STOP',
                'content' => [
                    'parts' => [[
                        'text' => json_encode([
                            'decision' => 'reject',
                            'reason' => 'explicit nudity',
                            'categories' => ['sexual_display'],
                        ]),
                    ]],
                ],
            ]],
        ]),
    ]);

    $verdict = app(ContentModerator::class)->review(UploadedFile::fake()->image('nsfw.jpg'));

    expect($verdict->decision)->toBe(ModerationDecision::Reject)
        ->and($verdict->categories)->toBe(['sexual_display']);
});

it('screens videos from sampled stills instead of uploading the clip to gemini', function () {
    $this->mock(VideoFrameSampler::class, function ($mock) {
        $mock->shouldReceive('sample')
            ->once()
            ->andReturn([UploadedFile::fake()->image('frame.jpg', 8, 8)->getContent()]);
    });

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'finishReason' => 'STOP',
                'content' => [
                    'parts' => [[
                        'text' => json_encode([
                            'decision' => 'allow',
                            'reason' => 'ordinary video stills',
                            'categories' => [],
                        ]),
                    ]],
                ],
            ]],
        ]),
    ]);

    $verdict = app(ContentModerator::class)->review(
        UploadedFile::fake()->create('clip.mp4', 50, 'video/mp4'),
    );

    expect($verdict->isAllowed())->toBeTrue();

    Http::assertNotSent(fn (Request $request) => str_contains($request->url(), '/upload/v1beta/files'));

    Http::assertSent(function (Request $request) {
        if (! str_contains($request->url(), 'generateContent')) {
            return false;
        }

        $data = $request->data();

        return data_get($data, 'contents.0.parts.1.inlineData.mimeType') === 'image/jpeg'
            && data_get($data, 'contents.0.parts.0.text') !== null;
    });
});

it('sends videos through the gemini files api when stills cannot be sampled', function () {
    $this->mock(VideoFrameSampler::class, function ($mock) {
        $mock->shouldReceive('sample')->once()->andReturn([]);
    });
    Http::fake(function (Request $request) {
        $url = $request->url();

        if (str_contains($url, '/upload/v1beta/files')) {
            return Http::response(['file' => new stdClass], 200, [
                'X-Goog-Upload-URL' => 'https://generativelanguage.googleapis.com/upload/session',
            ]);
        }

        if (str_contains($url, '/upload/session')) {
            return Http::response([
                'file' => [
                    'name' => 'files/abc',
                    'uri' => 'https://generativelanguage.googleapis.com/files/abc',
                ],
            ]);
        }

        if (str_contains($url, '/v1beta/files/abc')) {
            return Http::response(['state' => 'ACTIVE']);
        }

        return Http::response([
            'candidates' => [[
                'finishReason' => 'STOP',
                'content' => [
                    'parts' => [[
                        'text' => json_encode([
                            'decision' => 'allow',
                            'reason' => 'ordinary video',
                            'categories' => [],
                        ]),
                    ]],
                ],
            ]],
        ]);
    });

    $verdict = app(ContentModerator::class)->review(
        UploadedFile::fake()->create('clip.mp4', 50, 'video/mp4'),
    );

    expect($verdict->isAllowed())->toBeTrue();

    Http::assertSent(fn (Request $request) => str_contains($request->url(), '/upload/v1beta/files'));

    Http::assertSent(function (Request $request) {
        if (! str_contains($request->url(), 'generateContent')) {
            return false;
        }

        $data = $request->data();

        return data_get($data, 'contents.0.parts.0.fileData.fileUri') === 'https://generativelanguage.googleapis.com/files/abc'
            && data_get($data, 'contents.0.parts.0.inlineData') === null;
    });
});
