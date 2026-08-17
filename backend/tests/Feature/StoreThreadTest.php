<?php

use App\Models\City;
use App\Models\Forum;
use App\Models\Poll;
use App\Models\School;
use App\Models\StudentData;
use App\Models\Thread;
use App\Models\User;
use App\Support\SyncUserContentPermissions;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

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
            'fileId' => 'file_abc',
            'name' => 'photo.jpg',
            'url' => 'https://ik.imagekit.io/demo/threads/1/photo.jpg',
            'filePath' => '/threads/1/photo.jpg',
            'size' => 2048,
            'fileType' => 'image',
        ], 200),
    ]);
});

function makeStoreThreadForum(): Forum
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
        'threads_count' => 0,
    ]);
}

/** Starting a thread needs "create threads", which only school members get. */
function storeThreadAuthor(): User
{
    $city = City::query()->firstOrCreate(['name' => 'Скопје']);
    $school = School::query()->create([
        'name' => 'Георги Димитров',
        'city_id' => $city->id,
    ]);

    $user = User::factory()->create(['onboarding_completed_at' => now()]);

    StudentData::query()->create([
        'user_id' => $user->id,
        'school_id' => $school->id,
        'vocation_id' => null,
        'grade' => 3,
    ]);

    $user = $user->fresh(['studentData.school.forum']);
    app(SyncUserContentPermissions::class)->handle($user);

    return $user->fresh(['studentData.school.forum']);
}

it('requires authentication to create a thread', function () {
    $forum = makeStoreThreadForum();

    $this->post('/api/threads', [
        'forum_id' => $forum->id,
        'title' => 'Test thread title',
    ])->assertUnauthorized();
});

it('creates a thread with optional description, link, and poll', function () {
    $user = storeThreadAuthor();
    $forum = makeStoreThreadForum();

    $response = $this->actingAs($user)->post('/api/threads', [
        'forum_id' => $forum->id,
        'title' => 'Нова дискусија за матура',
        'description' => '',
        'is_anonymous' => false,
        'link' => 'https://example.com/docs',
        'poll' => [
            'question' => 'Кога ќе полагаш?',
            'options' => ['Јуни', 'Август', 'Не знам'],
            'duration_days' => 7,
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Нова дискусија за матура')
        ->assertJsonPath('data.poll.question', 'Кога ќе полагаш?')
        ->assertJsonCount(3, 'data.poll.options')
        ->assertJsonPath('data.attachments.0.type', 'link');

    $endsAt = Poll::query()->first()->ends_at;

    expect(Thread::query()->count())->toBe(1)
        ->and(Poll::query()->count())->toBe(1)
        ->and($forum->fresh()->threads_count)->toBe(1)
        ->and($endsAt->isAfter(now()->addDays(6)))->toBeTrue()
        ->and($endsAt->isBefore(now()->addDays(8)))->toBeTrue();
});

it('rejects poll duration longer than one month', function () {
    $user = storeThreadAuthor();
    $forum = makeStoreThreadForum();

    $this->actingAs($user)->post('/api/threads', [
        'forum_id' => $forum->id,
        'title' => 'Анкета премногу долга',
        'poll' => [
            'question' => 'Прашање?',
            'options' => ['А', 'Б'],
            'duration_days' => 31,
        ],
    ])->assertUnprocessable();
});

it('uploads files to imagekit and stores attachments', function () {
    $user = storeThreadAuthor();
    $forum = makeStoreThreadForum();

    $response = $this->actingAs($user)->post('/api/threads', [
        'forum_id' => $forum->id,
        'title' => 'Дискусија со слика',
        'files' => [
            UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg'),
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.attachments.0.type', 'image')
        ->assertJsonPath('data.attachments.0.url', 'https://ik.imagekit.io/demo/threads/1/photo.jpg');

    $attachment = Thread::query()->first()->threadAttachment()->first();

    expect($attachment->file_id)->toBe('file_abc')
        ->and($attachment->provider)->toBe('imagekit');
});

it('rejects combining a document with a poll', function () {
    $user = storeThreadAuthor();
    $forum = makeStoreThreadForum();

    $this->actingAs($user)->post('/api/threads', [
        'forum_id' => $forum->id,
        'title' => 'Лоша комбинација',
        'files' => [
            UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        ],
        'poll' => [
            'question' => 'Прашање?',
            'options' => ['А', 'Б'],
            'duration_days' => 3,
        ],
    ])->assertUnprocessable();
});

it('normalizes pasted youtube embed html into a valid link url', function () {
    $user = storeThreadAuthor();
    $forum = makeStoreThreadForum();

    $iframe = '<iframe width="560" height="315" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="YouTube video" frameborder="0" allowfullscreen></iframe>';

    $response = $this->actingAs($user)->post('/api/threads', [
        'forum_id' => $forum->id,
        'title' => 'Дискусија со вграден линк',
        'link' => $iframe,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.attachments.0.type', 'link')
        ->assertJsonPath('data.attachments.0.url', 'https://www.youtube.com/embed/dQw4w9WgXcQ');
});

it('allows combining images with a video attachment', function () {
    $user = storeThreadAuthor();
    $forum = makeStoreThreadForum();

    $response = $this->actingAs($user)->post('/api/threads', [
        'forum_id' => $forum->id,
        'title' => 'Слики и видео заедно',
        'files' => [
            UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg'),
            UploadedFile::fake()->create('clip.mp4', 200, 'video/mp4'),
        ],
    ]);

    $response->assertCreated()
        ->assertJsonCount(2, 'data.attachments');
});

it('rejects combining a link with uploaded media', function () {
    $user = storeThreadAuthor();
    $forum = makeStoreThreadForum();

    $this->actingAs($user)->post('/api/threads', [
        'forum_id' => $forum->id,
        'title' => 'Линк со слика',
        'link' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
        'files' => [
            UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg'),
        ],
    ])->assertUnprocessable();
});
