<?php

use App\Models\Comment;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function searchForum(string $name, string $slug): Forum
{
    return Forum::query()->create([
        'name' => $name,
        'slug' => $slug,
        'description' => "{$name} description",
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);
}

function searchThread(Forum $forum, User $author, string $title, int $upvotes): Thread
{
    return Thread::forceCreate([
        'title' => $title,
        'description' => 'Body',
        'upvotes' => $upvotes,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);
}

it('returns a paginated explore list when the query is empty', function () {
    $author = User::factory()->create();
    $forum = searchForum('Општи дискусии', 'opshti_diskusii');

    searchThread($forum, $author, 'Low', 1);
    searchThread($forum, $author, 'High', 99);

    $this->getJson('/api/search')
        ->assertSuccessful()
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('data.0.title', 'High')
        ->assertJsonPath('forums', []);
});

it('ranks a title that starts with the query above a later substring match', function () {
    $author = User::factory()->create();
    $forum = searchForum('Општи дискусии', 'opshti_diskusii');

    searchThread($forum, $author, 'Something something dren', 50);
    searchThread($forum, $author, 'Drzavna matura', 1);

    $titles = $this->getJson('/api/search?q=dr')
        ->assertSuccessful()
        ->json('data.*.title');

    expect($titles)->toBe(['Drzavna matura', 'Something something dren']);
});

it('ranks title matches above comment-only matches', function () {
    $author = User::factory()->create();
    $forum = searchForum('Општи дискусии', 'opshti_diskusii');

    $titleHit = searchThread($forum, $author, 'Совети за матура', 0);
    $commentHit = searchThread($forum, $author, 'Нешто друго', 50);

    Comment::forceCreate([
        'thread_id' => $commentHit->id,
        'parent_id' => null,
        'user_id' => $author->id,
        'content' => 'матура е тешка оваа година',
    ]);

    $titles = $this->getJson('/api/search?'.http_build_query(['q' => 'матура']))
        ->assertSuccessful()
        ->json('data.*.title');

    expect($titles)->toBe(['Совети за матура', 'Нешто друго'])
        ->and($titleHit->title)->toBe($titles[0]);
});

it('scopes results to a forum slug and skips forum suggestions', function () {
    $author = User::factory()->create();
    $sport = searchForum('Спорт', 'sport');
    $tech = searchForum('Технологија', 'tehnologija');

    searchThread($sport, $author, 'Hello sport', 1);
    searchThread($tech, $author, 'Hello tech', 1);

    $this->getJson('/api/search?q=Hello&forum=sport')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Hello sport')
        ->assertJsonPath('forums', []);
});

it('returns matching forum cards alongside threads', function () {
    $author = User::factory()->create();
    $matura = searchForum('Државна матура', 'drzhavna_matura');
    searchForum('Спорт', 'sport');
    searchThread($matura, $author, 'матура прашања', 3);

    $response = $this->getJson('/api/search?'.http_build_query(['q' => 'матура']))
        ->assertSuccessful()
        ->assertJsonPath('forums.0.slug', 'drzhavna_matura')
        ->assertJsonPath('data.0.title', 'матура прашања');

    expect($response->json('forums'))->toHaveCount(1);
});

it('returns 404 for an unknown forum slug', function () {
    $this->getJson('/api/search?q=hello&forum=missing')
        ->assertNotFound();
});

it('validates the query length', function () {
    $this->getJson('/api/search?q='.str_repeat('a', 201))
        ->assertUnprocessable();

    $this->getJson('/api/search?q=a')
        ->assertUnprocessable();
});

it('honours per_page for the live dropdown', function () {
    $author = User::factory()->create();
    $forum = searchForum('Општи дискусии', 'opshti_diskusii');

    foreach (range(1, 4) as $i) {
        searchThread($forum, $author, "Thread {$i}", $i);
    }

    $this->getJson('/api/search?q=Thread&per_page=2')
        ->assertSuccessful()
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonCount(2, 'data');
});
