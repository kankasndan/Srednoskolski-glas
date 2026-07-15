<?php

use App\Contracts\MediaStorage;
use App\Services\Media\ImageKitStorage;
use App\Services\Media\MediaManager;
use App\Services\Media\S3Storage;
use App\Support\Media\StoredMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config()->set('media.default', 'imagekit');
    config()->set('media.drivers.imagekit', [
        'public_key' => 'public_test',
        'private_key' => 'private_test',
        'url_endpoint' => 'https://ik.imagekit.io/demo',
        'upload_endpoint' => 'https://upload.imagekit.io/api/v1/files/upload',
        'api_endpoint' => 'https://api.imagekit.io/v1',
        'use_unique_file_name' => true,
    ]);
    config()->set('media.drivers.s3', [
        'disk' => 's3',
        'visibility' => 'public',
    ]);
});

it('resolves the imagekit driver as the default', function () {
    expect(app(MediaStorage::class))->toBeInstanceOf(ImageKitStorage::class);
    expect(app(MediaManager::class)->driver())->toBeInstanceOf(ImageKitStorage::class);
});

it('resolves the s3 driver on demand', function () {
    expect(app(MediaManager::class)->driver('s3'))->toBeInstanceOf(S3Storage::class);
});

it('uploads a file to imagekit and normalizes the response', function () {
    Http::fake([
        'upload.imagekit.io/*' => Http::response([
            'fileId' => 'abc123',
            'name' => 'photo.jpg',
            'url' => 'https://ik.imagekit.io/demo/uploads/photo.jpg',
            'filePath' => '/uploads/photo.jpg',
            'size' => 2048,
            'fileType' => 'image',
        ], 200),
    ]);

    $media = app(MediaManager::class)->driver('imagekit')->upload(
        UploadedFile::fake()->create('photo.jpg', 10, 'image/jpeg'),
        'uploads',
    );

    expect($media)->toBeInstanceOf(StoredMedia::class)
        ->and($media->provider)->toBe('imagekit')
        ->and($media->id)->toBe('abc123')
        ->and($media->url)->toBe('https://ik.imagekit.io/demo/uploads/photo.jpg')
        ->and($media->type)->toBe('image');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://upload.imagekit.io/api/v1/files/upload'
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('private_test:'));
    });
});

it('deletes an imagekit file by id', function () {
    Http::fake([
        'api.imagekit.io/*' => Http::response(null, 204),
    ]);

    $deleted = app(MediaManager::class)->driver('imagekit')->delete('abc123');

    expect($deleted)->toBeTrue();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/files/abc123'));
});

it('builds an imagekit url with transformations', function () {
    $url = app(MediaManager::class)->driver('imagekit')->url('/uploads/photo.jpg', [
        'transformations' => ['w' => 300, 'h' => 200],
    ]);

    expect($url)->toBe('https://ik.imagekit.io/demo/uploads/photo.jpg?tr=w-300,h-200');
});

it('uploads a file to s3 through the filesystem disk', function () {
    Storage::fake('s3');

    $media = app(MediaManager::class)->driver('s3')->upload(
        UploadedFile::fake()->create('doc.pdf', 120, 'application/pdf'),
        'documents',
    );

    expect($media->provider)->toBe('s3')
        ->and($media->type)->toBe('file');

    Storage::disk('s3')->assertExists($media->path);

    expect(app(MediaManager::class)->driver('s3')->delete($media))->toBeTrue();
    Storage::disk('s3')->assertMissing($media->path);
});
