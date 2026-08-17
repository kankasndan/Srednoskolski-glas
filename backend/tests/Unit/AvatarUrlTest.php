<?php

use App\Support\AvatarUrl;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config()->set('media.drivers.imagekit.url_endpoint', 'https://ik.imagekit.io/demo');
    config()->set('filesystems.disks.s3.url', '');
    config()->set('avatars.defaults', [
        '/avatars/default-1.svg',
        '/avatars/default-2.svg',
    ]);
});

it('allows default avatar paths', function () {
    expect(AvatarUrl::isAllowed('/avatars/default-1.svg', null))->toBeTrue();
});

it('allows imagekit urls including generated avatars', function () {
    expect(AvatarUrl::isConfiguredMediaUrl('https://ik.imagekit.io/demo/avatars/user.png'))->toBeTrue()
        ->and(AvatarUrl::isConfiguredMediaUrl(
            'https://ik.imagekit.io/demo/ik-genimg-prompt-a%20student/avatar.jpg?tr=w-400,h-400'
        ))->toBeTrue()
        ->and(AvatarUrl::isAllowed(
            'https://ik.imagekit.io/demo/ik-genimg-prompt-portrait/avatar.png',
            null,
        ))->toBeTrue();
});

it('rejects media-host urls that are not generated and not owned', function () {
    expect(AvatarUrl::isAllowed('https://ik.imagekit.io/demo/avatars/someone-else.png', null))->toBeFalse()
        ->and(AvatarUrl::isAllowed('https://ik.imagekit.io/demo/threads/12/photo.jpg', null))->toBeFalse();
});

it('rejects urls on a different imagekit account or host', function () {
    expect(AvatarUrl::isConfiguredMediaUrl('https://ik.imagekit.io/other/avatars/user.png'))->toBeFalse()
        ->and(AvatarUrl::isConfiguredMediaUrl('https://evil.example/demo/avatars/user.png'))->toBeFalse()
        ->and(AvatarUrl::isConfiguredMediaUrl('http://ik.imagekit.io/demo/avatars/user.png'))->toBeFalse();
});
