<?php

use App\Models\Comment;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use App\Support\SyncUserContentPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function commentForum(): Forum
{
    return Forum::query()->create([
        'name' => 'Општи дискусии',
        'slug' => 'opshti-diskusii',
        'description' => 'General forum',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);
}

function onboardedCommenter(): User
{
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);
    app(SyncUserContentPermissions::class)->handle($user);
    $user->givePermissionTo(SyncUserContentPermissions::CREATE_COMMENTS);

    return $user->fresh();
}

function commentThread(Forum $forum, User $author): Thread
{
    return Thread::forceCreate([
        'title' => 'Test thread',
        'description' => 'Body',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);
}

it('requires authentication to create a comment', function () {
    $author = User::factory()->create();
    $thread = commentThread(commentForum(), $author);

    $this->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Здраво',
    ])->assertUnauthorized();
});

it('creates a top-level comment on a thread', function () {
    $user = onboardedCommenter();
    $thread = commentThread(commentForum(), $user);

    $response = $this->actingAs($user)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Се согласувам со темата.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.content', 'Се согласувам со темата.')
        ->assertJsonPath('data.parent_id', null)
        ->assertJsonPath('data.has_voted', false)
        ->assertJsonPath('data.author.id', $user->id)
        ->assertJsonPath('data.replies_count', 0);

    expect((int) $response->json('data.upvotes'))->toBe(0);

    expect(Comment::query()->where([
        'thread_id' => $thread->id,
        'user_id' => $user->id,
        'parent_id' => null,
    ])->exists())->toBeTrue();
});

it('creates a child comment under a parent on the same thread', function () {
    $user = onboardedCommenter();
    $thread = commentThread(commentForum(), $user);

    $parent = Comment::forceCreate([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $user->id,
        'content' => 'Родителски коментар',
    ]);

    $response = $this->actingAs($user)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Ова е одговор.',
        'parent_id' => $parent->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.content', 'Ова е одговор.')
        ->assertJsonPath('data.parent_id', $parent->id);

    expect(Comment::query()->where([
        'thread_id' => $thread->id,
        'parent_id' => $parent->id,
        'content' => 'Ова е одговор.',
    ])->exists())->toBeTrue();
});

it('creates a nested reply under another reply', function () {
    $user = onboardedCommenter();
    $thread = commentThread(commentForum(), $user);

    $parent = Comment::forceCreate([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $user->id,
        'content' => 'Top',
    ]);

    $child = Comment::forceCreate([
        'thread_id' => $thread->id,
        'parent_id' => $parent->id,
        'user_id' => $user->id,
        'content' => 'Child',
    ]);

    $this->actingAs($user)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Grandchild',
        'parent_id' => $child->id,
    ])->assertCreated()
        ->assertJsonPath('data.parent_id', $child->id);
});

it('rejects a parent_id from another thread', function () {
    $user = onboardedCommenter();
    $forum = commentForum();
    $thread = commentThread($forum, $user);
    $otherThread = commentThread($forum, $user);

    $foreignParent = Comment::forceCreate([
        'thread_id' => $otherThread->id,
        'parent_id' => null,
        'user_id' => $user->id,
        'content' => 'Друга дискусија',
    ]);

    $this->actingAs($user)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Невалиден одговор',
        'parent_id' => $foreignParent->id,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);
});

it('requires content', function () {
    $user = onboardedCommenter();
    $thread = commentThread(commentForum(), $user);

    $this->actingAs($user)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => '',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['content']);
});

it('hides the author when the anonymous thread owner comments', function () {
    $author = onboardedCommenter();
    $other = onboardedCommenter();
    $thread = commentThread(commentForum(), $author);
    $thread->forceFill(['is_anonymous' => true])->save();

    $this->actingAs($author)
        ->postJson("/api/threads/{$thread->id}/comments", [
            'content' => 'Јас сум авторот.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.author', null);

    $this->actingAs($other)
        ->postJson("/api/threads/{$thread->id}/comments", [
            'content' => 'Јас не сум.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.author.id', $other->id);
});
