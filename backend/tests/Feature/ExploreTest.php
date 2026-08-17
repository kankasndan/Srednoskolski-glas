<?php

use App\Models\Comment;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function exploreForum(string $name, string $slug, string $type = 'general', int $views = 0): Forum
{
    return Forum::query()->create([
        'name' => $name,
        'slug' => $slug,
        'description' => "{$name} description",
        'type' => $type,
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
        'views' => $views,
    ]);
}

it('tracks forum views only when track_view is set', function () {
    $forum = exploreForum('Спорт', 'sport-views');

    $this->getJson("/api/p/{$forum->slug}")
        ->assertSuccessful();

    expect($forum->fresh()->views)->toBe(0);

    $this->getJson("/api/p/{$forum->slug}?track_view=1")
        ->assertSuccessful()
        ->assertJsonPath('data.forum.views', 1);

    expect($forum->fresh()->views)->toBe(1);
});

it('returns top general forums by views and excludes schools', function () {
    exploreForum('School', 'school-explore', 'school', 999);
    exploreForum('Low', 'low', 'general', 1);
    exploreForum('High', 'high', 'general', 50);
    exploreForum('Mid', 'mid', 'general', 20);
    exploreForum('Also', 'also', 'general', 10);
    exploreForum('Fifth', 'fifth', 'general', 5);

    $slugs = $this->getJson('/api/explore')
        ->assertSuccessful()
        ->json('data.forums.*.slug');

    expect($slugs)->toBe(['high', 'mid', 'also', 'fifth'])
        ->and($slugs)->not->toContain('school-explore')
        ->and($slugs)->not->toContain('low');
});

it('ranks weekly threads by votes and comments interactions', function () {
    $author = User::factory()->create();
    $voter = User::factory()->create();
    $forum = exploreForum('Спорт', 'sport-threads');
    $quiet = exploreForum('Тивко', 'quiet-threads');

    $hot = Thread::forceCreate([
        'title' => 'Hot week',
        'description' => 'Body',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);

    $cold = Thread::forceCreate([
        'title' => 'Cold week',
        'description' => 'Body',
        'upvotes' => 100,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $quiet->id,
        'is_anonymous' => false,
    ]);

    Vote::query()->create([
        'user_id' => $voter->id,
        'votable_type' => Thread::class,
        'votable_id' => $hot->id,
    ]);

    Comment::forceCreate([
        'thread_id' => $hot->id,
        'user_id' => $author->id,
        'content' => 'Nice',
    ]);

    // Old interaction should not count.
    $oldVote = Vote::query()->create([
        'user_id' => $author->id,
        'votable_type' => Thread::class,
        'votable_id' => $cold->id,
    ]);
    $oldVote->forceFill(['created_at' => now()->subWeeks(2), 'updated_at' => now()->subWeeks(2)])->saveQuietly();

    $titles = $this->getJson('/api/explore')
        ->assertSuccessful()
        ->json('data.threads.*.title');

    expect($titles[0])->toBe('Hot week');
});

it('excludes threads created more than a week ago', function () {
    $author = User::factory()->create();
    $forum = exploreForum('Спорт', 'sport-old');

    $recent = Thread::forceCreate([
        'title' => 'Recent thread',
        'description' => 'Body',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);

    $old = Thread::forceCreate([
        'title' => 'Old thread',
        'description' => 'Body',
        'upvotes' => 999,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);
    $old->forceFill([
        'created_at' => now()->subWeeks(2),
        'updated_at' => now()->subWeeks(2),
    ])->saveQuietly();

    // Fresh interaction on the old thread must not bring it back.
    Vote::query()->create([
        'user_id' => $author->id,
        'votable_type' => Thread::class,
        'votable_id' => $old->id,
    ]);

    $titles = $this->getJson('/api/explore')
        ->assertSuccessful()
        ->json('data.threads.*.title');

    expect($titles)->toContain('Recent thread')
        ->and($titles)->not->toContain('Old thread')
        ->and($recent->created_at->greaterThan(now()->subWeek()))->toBeTrue();
});
