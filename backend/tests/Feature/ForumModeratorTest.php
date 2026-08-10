<?php

use App\Models\Forum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves the forum moderator via user_id', function () {
    $moderator = User::factory()->create();

    $forum = Forum::query()->create([
        'name' => 'Општи дискусии',
        'slug' => 'opshti-diskusii',
        'description' => 'General',
        'type' => 'general',
        'user_id' => $moderator->id,
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);

    expect($forum->moderator)->not->toBeNull()
        ->and($forum->moderator->is($moderator))->toBeTrue();
});
