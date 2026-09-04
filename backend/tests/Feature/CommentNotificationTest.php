<?php

use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use App\Notifications\CommentActivityNotification;
use App\Notifications\NewFeedbackNotification;
use App\Notifications\NewFollowNotification;
use App\Support\SyncUserContentPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function notificationForum(): Forum
{
    return Forum::query()->create([
        'name' => 'Општи дискусии',
        'slug' => 'opshti-diskusii-notifications',
        'description' => 'General forum',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);
}

function notificationCommenter(array $overrides = []): User
{
    $user = User::factory()->create(array_merge([
        'onboarding_completed_at' => now(),
    ], $overrides));
    app(SyncUserContentPermissions::class)->handle($user);
    $user->givePermissionTo(SyncUserContentPermissions::CREATE_COMMENTS);

    return $user->fresh();
}

function notificationThread(Forum $forum, User $author, array $overrides = []): Thread
{
    return Thread::forceCreate(array_merge([
        'title' => 'Тест дискусија',
        'description' => 'Body',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ], $overrides));
}

function unreadReasons(User $user): array
{
    return $user->unreadNotifications()
        ->where('type', CommentActivityNotification::class)
        ->get()
        ->pluck('data.reason')
        ->all();
}

it('notifies a user mentioned in a comment', function () {
    $commenter = notificationCommenter(['username' => 'marko_p']);
    $mentioned = User::factory()->create([
        'username' => 'ana_k',
        'onboarding_completed_at' => now(),
    ]);
    $thread = notificationThread(notificationForum(), $commenter);

    $this->actingAs($commenter)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Што мислиш @ana_k?',
    ])->assertCreated();

    expect(unreadReasons($mentioned))->toBe(['mention'])
        ->and($commenter->notifications()->count())->toBe(0);

    $payload = $mentioned->unreadNotifications()->first()->data;
    expect($payload['url'])->toStartWith('/p/opshti-diskusii-notifications/')
        ->and($payload['url'])->toContain('#comment-')
        ->and($payload['actor_username'])->toBe('marko_p')
        ->and($payload['title'])->toBe('Спомнување');
});

it('notifies the thread author when someone comments on their post', function () {
    $author = User::factory()->create(['username' => 'owner_mk']);
    $commenter = notificationCommenter(['username' => 'guest_mk']);
    $thread = notificationThread(notificationForum(), $author);

    $this->actingAs($commenter)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Добар пост.',
    ])->assertCreated();

    expect(unreadReasons($author))->toBe(['thread_comment'])
        ->and($commenter->notifications()->count())->toBe(0);

    $payload = $author->unreadNotifications()->first()->data;
    expect($payload['message'])->toContain('guest_mk')
        ->and($payload['message'])->toContain('Тест дискусија');
});

it('notifies followers when someone comments on a thread they follow', function () {
    $author = User::factory()->create();
    $follower = User::factory()->create(['username' => 'follower_mk']);
    $commenter = notificationCommenter(['username' => 'commenter_mk']);
    $thread = notificationThread(notificationForum(), $author);
    $follower->followedThreads()->attach($thread->id);

    $this->actingAs($commenter)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Се согласувам.',
    ])->assertCreated();

    expect(unreadReasons($follower))->toBe(['followed_thread_comment'])
        ->and(unreadReasons($author))->toBe(['thread_comment']);
});

it('does not notify the commenter even if they follow the thread or own it', function () {
    $author = notificationCommenter(['username' => 'self_mk']);
    $thread = notificationThread(notificationForum(), $author);
    $author->followedThreads()->attach($thread->id);

    $this->actingAs($author)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Дополнување.',
    ])->assertCreated();

    expect($author->notifications()->count())->toBe(0);
});

it('sends a mention instead of a thread-comment when the author is mentioned', function () {
    $author = User::factory()->create(['username' => 'owner_mk']);
    $commenter = notificationCommenter();
    $thread = notificationThread(notificationForum(), $author);

    $this->actingAs($commenter)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Здраво @owner_mk',
    ])->assertCreated();

    expect(unreadReasons($author))->toBe(['mention']);
});

it('sends only a thread-comment when the author also follows the thread', function () {
    $author = User::factory()->create();
    $commenter = notificationCommenter();
    $thread = notificationThread(notificationForum(), $author);
    $author->followedThreads()->attach($thread->id);

    $this->actingAs($commenter)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Нов коментар.',
    ])->assertCreated();

    expect(unreadReasons($author))->toBe(['thread_comment']);
});

it('hides the username when the anonymous thread author comments', function () {
    $author = notificationCommenter(['username' => 'secret_mk']);
    $follower = User::factory()->create();
    $thread = notificationThread(notificationForum(), $author, ['is_anonymous' => true]);
    $follower->followedThreads()->attach($thread->id);

    $this->actingAs($author)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Јас сум авторот.',
    ])->assertCreated();

    expect(unreadReasons($follower))->toBe(['followed_thread_comment']);

    $payload = $follower->unreadNotifications()->first()->data;
    expect($payload['actor_username'])->toBeNull()
        ->and($payload['message'])->toStartWith('Некој коментираше');
});

it('notifies the parent comment author when someone replies', function () {
    $threadAuthor = User::factory()->create();
    $parentAuthor = notificationCommenter(['username' => 'parent_mk']);
    $replier = notificationCommenter(['username' => 'replier_mk']);
    $thread = notificationThread(notificationForum(), $threadAuthor);

    $parentId = $this->actingAs($parentAuthor)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Прв коментар.',
    ])->assertCreated()->json('data.id');

    $this->actingAs($replier)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Одговор.',
        'parent_id' => $parentId,
    ])->assertCreated();

    expect(unreadReasons($parentAuthor))->toBe(['comment_reply'])
        ->and($replier->notifications()->count())->toBe(0);

    $payload = $parentAuthor->unreadNotifications()->first()->data;
    expect($payload['title'])->toBe('Одговор')
        ->and($payload['message'])->toContain('replier_mk')
        ->and($payload['url'])->toContain('#comment-')
        ->and($payload['url'])->toContain("expand={$parentId}")
        ->and($payload['expand_path'])->toBe([(int) $parentId]);
});

it('includes the full ancestor expand path for a nested reply notification', function () {
    $threadAuthor = User::factory()->create();
    $rootAuthor = notificationCommenter();
    $midAuthor = notificationCommenter();
    $replier = notificationCommenter();
    $thread = notificationThread(notificationForum(), $threadAuthor);

    $rootId = $this->actingAs($rootAuthor)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Корен.',
    ])->assertCreated()->json('data.id');

    $midId = $this->actingAs($midAuthor)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Среден.',
        'parent_id' => $rootId,
    ])->assertCreated()->json('data.id');

    $this->actingAs($replier)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Длабок одговор.',
        'parent_id' => $midId,
    ])->assertCreated();

    $payload = $midAuthor->unreadNotifications()->first()->data;
    expect($payload['reason'])->toBe('comment_reply')
        ->and($payload['expand_path'])->toBe([(int) $rootId, (int) $midId])
        ->and($payload['url'])->toContain('expand='.$rootId.'.'.$midId);
});

it('sends a reply instead of a thread-comment when someone replies to the author', function () {
    $author = notificationCommenter(['username' => 'owner_mk']);
    $replier = notificationCommenter();
    $thread = notificationThread(notificationForum(), $author);

    $parentId = $this->actingAs($author)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Мој коментар.',
    ])->assertCreated()->json('data.id');

    expect($author->notifications()->count())->toBe(0);

    $this->actingAs($replier)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Одговор.',
        'parent_id' => $parentId,
    ])->assertCreated();

    expect(unreadReasons($author))->toBe(['comment_reply']);
});

it('sends a mention instead of a reply when the parent author is mentioned', function () {
    $threadAuthor = User::factory()->create();
    $parentAuthor = notificationCommenter(['username' => 'parent_mk']);
    $replier = notificationCommenter();
    $thread = notificationThread(notificationForum(), $threadAuthor);

    $parentId = $this->actingAs($parentAuthor)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Прв коментар.',
    ])->assertCreated()->json('data.id');

    $this->actingAs($replier)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Здраво @parent_mk',
        'parent_id' => $parentId,
    ])->assertCreated();

    expect(unreadReasons($parentAuthor))->toBe(['mention']);
});

it('does not notify when you reply to your own comment', function () {
    $author = notificationCommenter();
    $thread = notificationThread(notificationForum(), User::factory()->create());

    $parentId = $this->actingAs($author)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Прв коментар.',
    ])->assertCreated()->json('data.id');

    $this->actingAs($author)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Дополнување.',
        'parent_id' => $parentId,
    ])->assertCreated();

    expect($author->notifications()->count())->toBe(0);
});

it('notifies newly mentioned users on edit without re-notifying the thread author', function () {
    $author = User::factory()->create(['username' => 'owner_mk']);
    $first = User::factory()->create(['username' => 'ana_k', 'onboarding_completed_at' => now()]);
    $second = User::factory()->create(['username' => 'marko_p', 'onboarding_completed_at' => now()]);
    $commenter = notificationCommenter();
    $thread = notificationThread(notificationForum(), $author);

    $commentId = $this->actingAs($commenter)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Здраво @ana_k',
    ])->assertCreated()->json('data.id');

    expect(unreadReasons($author))->toBe(['thread_comment'])
        ->and(unreadReasons($first))->toBe(['mention']);

    $this->actingAs($commenter)->putJson("/api/comments/{$commentId}", [
        'content' => 'Сега @marko_p',
    ])->assertSuccessful();

    expect(unreadReasons($author))->toBe(['thread_comment'])
        ->and(unreadReasons($first))->toBe(['mention'])
        ->and(unreadReasons($second))->toBe(['mention']);
});

it('requires authentication for notification endpoints', function () {
    $this->getJson('/api/me/notifications')->assertUnauthorized();
    $this->postJson('/api/me/notifications/read-all')->assertUnauthorized();
    $this->postJson('/api/me/notifications/'.Str::uuid().'/read')->assertUnauthorized();
});

it('lists student notifications and unread count', function () {
    $author = User::factory()->create();
    $commenter = notificationCommenter(['username' => 'guest_mk']);
    $thread = notificationThread(notificationForum(), $author);

    $this->actingAs($commenter)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Здраво.',
    ])->assertCreated();

    $this->actingAs($author)
        ->getJson('/api/me/notifications')
        ->assertOk()
        ->assertJsonPath('unread_count', 1)
        ->assertJsonPath('data.0.reason', 'thread_comment')
        ->assertJsonPath('data.0.title', 'Нов коментар')
        ->assertJsonPath('data.0.actor_username', 'guest_mk')
        ->assertJsonPath('data.0.read_at', null);

    expect($this->actingAs($author)->getJson('/api/me/notifications')->json('data.0.url'))
        ->toContain('/p/opshti-diskusii-notifications/')
        ->toContain('#comment-');
});

it('does not expose admin notifications in the student bell', function () {
    $user = User::factory()->create();

    $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => NewFeedbackNotification::class,
        'data' => [
            'title' => 'Ново мислење',
            'message' => 'admin only',
            'url' => '/feedback',
        ],
    ]);

    $this->actingAs($user)
        ->getJson('/api/me/notifications')
        ->assertOk()
        ->assertJsonPath('unread_count', 0)
        ->assertJsonCount(0, 'data');
});

it('marks one notification as read', function () {
    $author = User::factory()->create();
    $commenter = notificationCommenter();
    $thread = notificationThread(notificationForum(), $author);

    $this->actingAs($commenter)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Здраво.',
    ])->assertCreated();

    $id = $author->unreadNotifications()->first()->id;

    $this->actingAs($author)
        ->postJson("/api/me/notifications/{$id}/read")
        ->assertOk()
        ->assertJsonPath('data.id', $id);

    expect($author->fresh()->unreadNotifications()->count())->toBe(0)
        ->and($author->notifications()->first()->read_at)->not->toBeNull();

    $this->actingAs($author)
        ->getJson('/api/me/notifications')
        ->assertOk()
        ->assertJsonPath('unread_count', 0);

    expect($this->actingAs($author)->getJson('/api/me/notifications')->json('data.0.read_at'))
        ->not->toBeNull();
});

it('does not let a user mark someone elses notification as read', function () {
    $author = User::factory()->create();
    $other = User::factory()->create();
    $commenter = notificationCommenter();
    $thread = notificationThread(notificationForum(), $author);

    $this->actingAs($commenter)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Здраво.',
    ])->assertCreated();

    $id = $author->unreadNotifications()->first()->id;

    $this->actingAs($other)
        ->postJson("/api/me/notifications/{$id}/read")
        ->assertNotFound();
});

it('marks all student notifications as read', function () {
    $author = User::factory()->create();
    $commenter = notificationCommenter();
    $thread = notificationThread(notificationForum(), $author);

    $this->actingAs($commenter)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Прв.',
    ])->assertCreated();
    $this->actingAs($commenter)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Втор.',
    ])->assertCreated();

    expect($author->unreadNotifications()->count())->toBe(2);

    $this->actingAs($author)
        ->postJson('/api/me/notifications/read-all')
        ->assertOk()
        ->assertJsonPath('data.ok', true);

    expect($author->fresh()->unreadNotifications()->count())->toBe(0);
});

it('notifies a user when someone starts following them', function () {
    $followed = User::factory()->create(['username' => 'star_mk']);
    $follower = User::factory()->create(['username' => 'fan_mk']);

    $this->actingAs($follower)
        ->postJson("/api/u/{$followed->username}/follow")
        ->assertSuccessful();

    expect($followed->unreadNotifications()->where('type', NewFollowNotification::class)->count())->toBe(1)
        ->and($follower->notifications()->count())->toBe(0);

    $payload = $followed->unreadNotifications()->first()->data;
    expect($payload['reason'])->toBe('new_follow')
        ->and($payload['title'])->toBe('Нов следбеник')
        ->and($payload['url'])->toBe('/u/fan_mk')
        ->and($payload['actor_username'])->toBe('fan_mk');
});

it('does not notify again when the same user follows twice', function () {
    $followed = User::factory()->create(['username' => 'star_mk']);
    $follower = User::factory()->create(['username' => 'fan_mk']);

    $this->actingAs($follower)->postJson("/api/u/{$followed->username}/follow")->assertSuccessful();
    $this->actingAs($follower)->postJson("/api/u/{$followed->username}/follow")->assertSuccessful();

    expect($followed->unreadNotifications()->where('type', NewFollowNotification::class)->count())->toBe(1);
});

it('lists follow notifications in the student bell', function () {
    $followed = User::factory()->create(['username' => 'star_mk']);
    $follower = User::factory()->create(['username' => 'fan_mk']);

    $this->actingAs($follower)->postJson("/api/u/{$followed->username}/follow")->assertSuccessful();

    $this->actingAs($followed)
        ->getJson('/api/me/notifications')
        ->assertOk()
        ->assertJsonPath('unread_count', 1)
        ->assertJsonPath('data.0.reason', 'new_follow')
        ->assertJsonPath('data.0.url', '/u/fan_mk')
        ->assertJsonPath('data.0.read_at', null);
});
