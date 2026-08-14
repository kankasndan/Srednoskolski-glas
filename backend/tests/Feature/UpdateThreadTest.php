<?php

use App\Models\Forum;
use App\Models\Thread;
use App\Models\ThreadAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

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

    Http::fake([
        'upload.imagekit.io/*' => Http::response([
            'fileId' => 'file_new',
            'name' => 'photo.jpg',
            'url' => 'https://ik.imagekit.io/demo/threads/1/photo.jpg',
            'filePath' => '/threads/1/photo.jpg',
            'size' => 2048,
            'fileType' => 'image',
        ], 200),
        'api.imagekit.io/*' => Http::response([], 204),
    ]);
});

function updateThreadForum(): Forum
{
    return Forum::query()->create([
        'name' => 'Државна матура',
        'slug' => 'drzhavna_matura',
        'description' => 'Matura forum',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 1,
    ]);
}

function updateThread(Forum $forum, User $author): Thread
{
    return Thread::forceCreate([
        'title' => 'Original title here',
        'description' => 'Original body',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);
}

it('requires authentication to update a thread', function () {
    $author = User::factory()->create();
    $thread = updateThread(updateThreadForum(), $author);

    $this->putJson("/api/threads/{$thread->id}", [
        'title' => 'Updated title here',
        'description' => 'Updated body',
    ])->assertUnauthorized();
});

it('allows the author to update title and description and sets edited_at', function () {
    $author = User::factory()->create();
    $thread = updateThread(updateThreadForum(), $author);

    $response = $this->actingAs($author)->putJson("/api/threads/{$thread->id}", [
        'title' => 'Updated title here',
        'description' => 'Updated body',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Updated title here')
        ->assertJsonPath('data.description', 'Updated body');

    expect($response->json('data.edited_at'))->not()->toBeNull();
});

it('forbids non-authors from updating a thread', function () {
    $author = User::factory()->create();
    $other = User::factory()->create();
    $thread = updateThread(updateThreadForum(), $author);

    $this->actingAs($other)->putJson("/api/threads/{$thread->id}", [
        'title' => 'Hijacked title here',
        'description' => 'Nope',
    ])->assertForbidden();
});

it('adds and removes attachments on update via multipart post', function () {
    $author = User::factory()->create();
    $thread = updateThread(updateThreadForum(), $author);

    $existing = ThreadAttachment::query()->create([
        'thread_id' => $thread->id,
        'url' => 'https://ik.imagekit.io/demo/old.jpg',
        'slug' => 'image',
        'provider' => 'imagekit',
        'file_id' => 'file_old',
    ]);

    $response = $this->actingAs($author)->post("/api/threads/{$thread->id}", [
        'title' => 'Updated with media',
        'description' => 'Body',
        'remove_attachment_ids' => [$existing->id],
        'files' => [
            UploadedFile::fake()->image('new.jpg'),
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Updated with media')
        ->assertJsonCount(1, 'data.attachments')
        ->assertJsonPath('data.attachments.0.type', 'image');

    expect(ThreadAttachment::query()->find($existing->id))->toBeNull()
        ->and($thread->fresh()->threadAttachment)->toHaveCount(1);
});

it('rejects removing an attachment that does not belong to the thread', function () {
    $author = User::factory()->create();
    $forum = updateThreadForum();
    $thread = updateThread($forum, $author);
    $other = updateThread($forum, $author);

    $foreign = ThreadAttachment::query()->create([
        'thread_id' => $other->id,
        'url' => 'https://ik.imagekit.io/demo/other.jpg',
        'slug' => 'image',
        'provider' => 'imagekit',
        'file_id' => 'file_other',
    ]);

    $this->actingAs($author)->post("/api/threads/{$thread->id}", [
        'title' => 'Still original title',
        'remove_attachment_ids' => [$foreign->id],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['remove_attachment_ids']);
});
