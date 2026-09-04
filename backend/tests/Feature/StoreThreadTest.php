<?php

use App\Contracts\ContentModerator;
use App\Models\City;
use App\Models\Forum;
use App\Models\Poll;
use App\Models\School;
use App\Models\StudentData;
use App\Models\Thread;
use App\Models\User;
use App\Models\Vote;
use App\Support\Moderation\ModerationDecision;
use App\Support\Moderation\ModerationVerdict;
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
        ->assertJsonPath('data.upvotes', 1)
        ->assertJsonPath('data.has_voted', true)
        ->assertJsonPath('data.poll.question', 'Кога ќе полагаш?')
        ->assertJsonCount(3, 'data.poll.options')
        ->assertJsonPath('data.attachments.0.type', 'link');

    $thread = Thread::query()->first();
    $endsAt = Poll::query()->first()->ends_at;

    expect(Thread::query()->count())->toBe(1)
        ->and(Poll::query()->count())->toBe(1)
        ->and($forum->fresh()->threads_count)->toBe(1)
        ->and($endsAt->isAfter(now()->addDays(6)))->toBeTrue()
        ->and($endsAt->isBefore(now()->addDays(8)))->toBeTrue()
        ->and(Vote::query()->where([
            'user_id' => $user->id,
            'votable_type' => $thread->getMorphClass(),
            'votable_id' => $thread->id,
        ])->exists())->toBeTrue();
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

it('does not create a thread when gemini rejects the title', function () {
    config()->set('moderation.enabled', true);

    $this->mock(ContentModerator::class, function ($mock) {
        $mock->shouldReceive('reviewText')
            ->once()
            ->andReturn(new ModerationVerdict(
                ModerationDecision::Reject,
                'hate speech',
                ['hate_speech'],
                ['title'],
            ));
        $mock->shouldReceive('review')->never();
    });

    $user = storeThreadAuthor();
    $forum = makeStoreThreadForum();

    $this->actingAs($user)->post('/api/threads', [
        'forum_id' => $forum->id,
        'title' => 'Hate filled title here',
        'description' => 'Otherwise ordinary body',
    ])->assertUnprocessable()
        ->assertJsonPath('errors.title.0', 'Овој текст не е дозволен. Отстрани навредливи зборови или говор на омраза.');

    expect(Thread::query()->count())->toBe(0);
});

it('does not create a thread when gemini rejects poll text', function () {
    config()->set('moderation.enabled', true);

    $this->mock(ContentModerator::class, function ($mock) {
        $mock->shouldReceive('reviewText')
            ->once()
            ->withArgs(function (string $text): bool {
                return str_contains($text, 'Title:')
                    && str_contains($text, 'Poll question:')
                    && str_contains($text, 'Poll option 1:');
            })
            ->andReturn(new ModerationVerdict(
                ModerationDecision::Reject,
                'profanity in poll option',
                ['profanity'],
                ['poll'],
            ));
        $mock->shouldReceive('review')->never();
    });

    $user = storeThreadAuthor();
    $forum = makeStoreThreadForum();

    $this->actingAs($user)->post('/api/threads', [
        'forum_id' => $forum->id,
        'title' => 'Анкета за матура',
        'poll' => [
            'question' => 'Кој е најдобар?',
            'options' => ['Слава', 'Навреда'],
            'duration_days' => 7,
        ],
    ])->assertUnprocessable()
        ->assertJsonPath('errors.poll.0', 'Овој текст не е дозволен. Отстрани навредливи зборови или говор на омраза.');

    expect(Thread::query()->count())->toBe(0)
        ->and(Poll::query()->count())->toBe(0);
});

it('refuses thread create when text moderation is unreachable', function () {
    config()->set('moderation.enabled', true);
    config()->set('moderation.on_failure', 'reject');

    $this->mock(ContentModerator::class, function ($mock) {
        $mock->shouldReceive('reviewText')->once()->andThrow(new RuntimeException('timeout'));
        $mock->shouldReceive('review')->never();
    });

    $user = storeThreadAuthor();
    $forum = makeStoreThreadForum();

    $this->actingAs($user)->post('/api/threads', [
        'forum_id' => $forum->id,
        'title' => 'Нова дискусија за матура',
    ])->assertUnprocessable()
        ->assertJsonPath('errors.title.0', 'Проверката на текстот не успеа. Обиди се повторно.');

    expect(Thread::query()->count())->toBe(0);
});

it('creates a thread when gemini allows the title description and poll', function () {
    config()->set('moderation.enabled', true);

    $this->mock(ContentModerator::class, function ($mock) {
        $mock->shouldReceive('reviewText')
            ->once()
            ->withArgs(function (string $text): bool {
                return str_contains($text, 'Title: Нова дискусија за матура')
                    && str_contains($text, 'Description: Како да се подготвам')
                    && str_contains($text, 'Poll question: Кога ќе полагаш?')
                    && str_contains($text, 'Poll option 2: Август');
            })
            ->andReturn(ModerationVerdict::allow('ordinary school thread'));
        $mock->shouldReceive('review')->never();
    });

    $user = storeThreadAuthor();
    $forum = makeStoreThreadForum();

    $this->actingAs($user)->post('/api/threads', [
        'forum_id' => $forum->id,
        'title' => 'Нова дискусија за матура',
        'description' => '<p>Како да се подготвам</p>',
        'poll' => [
            'question' => 'Кога ќе полагаш?',
            'options' => ['Јуни', 'Август'],
            'duration_days' => 7,
        ],
    ])->assertCreated()
        ->assertJsonPath('data.title', 'Нова дискусија за матура');
});
