<?php

use App\Models\Comment;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeThreadWithComments(): array
{
    $author = User::factory()->create();
    $forum = Forum::query()->create([
        'name' => 'Спорт',
        'slug' => 'sport',
        'description' => 'Sport',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);
    $thread = Thread::forceCreate([
        'title' => 'Poll thread',
        'description' => 'Body',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);

    $oldest = Comment::forceCreate([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $author->id,
        'content' => 'Oldest comment',
    ]);
    $oldest->forceFill([
        'upvotes' => 1,
        'created_at' => now()->subDays(3),
        'updated_at' => now()->subDays(3),
    ])->save();

    $best = Comment::forceCreate([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $author->id,
        'content' => 'Best comment',
    ]);
    $best->forceFill([
        'upvotes' => 10,
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ])->save();

    $newest = Comment::forceCreate([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $author->id,
        'content' => 'Newest comment',
    ]);
    $newest->forceFill([
        'upvotes' => 2,
        'created_at' => now(),
        'updated_at' => now(),
    ])->save();

    Comment::forceCreate([
        'thread_id' => $thread->id,
        'parent_id' => $best->id,
        'user_id' => $author->id,
        'content' => 'Reply under best',
    ]);

    return compact('forum', 'thread', 'oldest', 'best', 'newest');
}

it('increments views and returns top-level comments with replies_count', function () {
    ['forum' => $forum, 'thread' => $thread, 'best' => $best] = makeThreadWithComments();

    $this->getJson("/api/p/{$forum->slug}/comments/{$thread->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.thread.id', $thread->id)
        ->assertJsonCount(3, 'data.comments')
        ->assertJsonPath('data.comments.0.id', $best->id)
        ->assertJsonPath('data.comments.0.replies_count', 1)
        ->assertJsonMissingPath('data.comments.0.replies');

    expect($thread->fresh()->views)->toBe(1);
});

it('sorts comments by best by default', function () {
    ['forum' => $forum, 'thread' => $thread, 'best' => $best] = makeThreadWithComments();

    $this->getJson("/api/p/{$forum->slug}/comments/{$thread->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.comments.0.id', $best->id);
});

it('sorts comments by newest', function () {
    ['forum' => $forum, 'thread' => $thread, 'newest' => $newest] = makeThreadWithComments();

    $this->getJson("/api/p/{$forum->slug}/comments/{$thread->id}?sort=newest")
        ->assertSuccessful()
        ->assertJsonPath('data.comments.0.id', $newest->id);
});

it('sorts comments by oldest', function () {
    ['forum' => $forum, 'thread' => $thread, 'oldest' => $oldest] = makeThreadWithComments();

    $this->getJson("/api/p/{$forum->slug}/comments/{$thread->id}?sort=oldest")
        ->assertSuccessful()
        ->assertJsonPath('data.comments.0.id', $oldest->id);
});

it('does not increment views when track_view is disabled', function () {
    ['forum' => $forum, 'thread' => $thread] = makeThreadWithComments();

    $this->getJson("/api/p/{$forum->slug}/comments/{$thread->id}?track_view=0")
        ->assertSuccessful();

    expect($thread->fresh()->views)->toBe(0);
});

it('does not increment views twice from the same visitor', function () {
    ['forum' => $forum, 'thread' => $thread] = makeThreadWithComments();
    $url = "/api/p/{$forum->slug}/comments/{$thread->id}";

    $this->getJson($url)->assertSuccessful();
    $this->getJson($url)->assertSuccessful();

    expect($thread->fresh()->views)->toBe(1);
});

it('hides the anonymous thread author on their own comments', function () {
    ['forum' => $forum, 'thread' => $thread] = makeThreadWithComments();
    $thread->forceFill(['is_anonymous' => true])->save();

    $this->getJson("/api/p/{$forum->slug}/comments/{$thread->id}?track_view=0")
        ->assertSuccessful()
        ->assertJsonPath('data.comments.0.author', null)
        ->assertJsonMissingPath('data.comments.0.deleted_by');
});

it('omits deleted leaves and keeps tombstones that still have replies', function () {
    ['forum' => $forum, 'thread' => $thread, 'best' => $best] = makeThreadWithComments();

    $deletedLeaf = Comment::forceCreate([
        'thread_id' => $thread->id,
        'parent_id' => $best->id,
        'user_id' => $best->user_id,
        'content' => 'Soon deleted leaf',
    ]);
    $deletedLeaf->delete();

    $deletedRoot = Comment::forceCreate([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $best->user_id,
        'content' => 'Deleted root',
    ]);
    Comment::forceCreate([
        'thread_id' => $thread->id,
        'parent_id' => $deletedRoot->id,
        'user_id' => $best->user_id,
        'content' => 'Live reply under deleted',
    ]);
    $deletedRoot->delete();

    $lonelyDeleted = Comment::forceCreate([
        'thread_id' => $thread->id,
        'parent_id' => null,
        'user_id' => $best->user_id,
        'content' => 'Gone',
    ]);
    $lonelyDeleted->delete();

    $response = $this->getJson("/api/p/{$forum->slug}/comments/{$thread->id}")
        ->assertSuccessful();

    $roots = collect($response->json('data.comments'));
    expect($roots->pluck('id'))->toContain($deletedRoot->id)
        ->and($roots->pluck('id'))->not->toContain($lonelyDeleted->id);

    $bestRow = $roots->firstWhere('id', $best->id);
    // Live "Reply under best" counts; the deleted leaf does not.
    expect($bestRow['replies_count'])->toBe(1);

    $deletedRow = $roots->firstWhere('id', $deletedRoot->id);
    expect($deletedRow['replies_count'])->toBe(1)
        ->and($deletedRow['content'])->toBe('');
});

it('loads direct replies from the replies endpoint', function () {
    ['forum' => $forum, 'thread' => $thread, 'best' => $best] = makeThreadWithComments();

    $child = Comment::query()->where('parent_id', $best->id)->first();
    $grandchild = Comment::forceCreate([
        'thread_id' => $thread->id,
        'parent_id' => $child->id,
        'user_id' => $best->user_id,
        'content' => 'Nested reply',
    ]);

    $this->getJson("/api/p/{$forum->slug}/comments/{$thread->id}")
        ->assertSuccessful()
        ->assertJsonMissingPath('data.comments.0.replies');

    $this->getJson("/api/comments/{$best->id}/replies")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $child->id)
        ->assertJsonPath('data.0.replies_count', 1)
        ->assertJsonMissingPath('data.0.replies');

    $this->getJson("/api/comments/{$child->id}/replies")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $grandchild->id)
        ->assertJsonPath('data.0.replies_count', 0);
});
