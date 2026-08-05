<?php

use App\Models\Forum;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makePollThread(): array
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
    $thread = Thread::query()->create([
        'title' => 'Poll thread',
        'description' => 'Body',
        'upvotes' => 0,
        'views' => 0,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
    ]);
    $poll = Poll::query()->create([
        'thread_id' => $thread->id,
        'question' => 'Омилен спорт?',
        'ends_at' => now()->addDays(3),
    ]);
    $optionA = PollOption::query()->create([
        'poll_id' => $poll->id,
        'label' => 'Фудбал',
        'position' => 0,
    ]);
    $optionB = PollOption::query()->create([
        'poll_id' => $poll->id,
        'label' => 'Кошарка',
        'position' => 1,
    ]);

    return compact('author', 'poll', 'optionA', 'optionB');
}

it('requires authentication to vote on a poll', function () {
    ['poll' => $poll, 'optionA' => $optionA] = makePollThread();

    $this->postJson("/api/polls/{$poll->id}/vote", [
        'poll_option_id' => $optionA->id,
    ])->assertUnauthorized();
});

it('lets a logged-in user vote once and returns results', function () {
    ['poll' => $poll, 'optionA' => $optionA] = makePollThread();
    $voter = User::factory()->create();

    $this->actingAs($voter)
        ->postJson("/api/polls/{$poll->id}/vote", [
            'poll_option_id' => $optionA->id,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.total_votes', 1)
        ->assertJsonPath('data.user_voted_option_id', $optionA->id)
        ->assertJsonPath('data.options.0.votes_count', 1)
        ->assertJsonPath('data.options.0.percentage', 100);

    $this->actingAs($voter)
        ->postJson("/api/polls/{$poll->id}/vote", [
            'poll_option_id' => $optionA->id,
        ])
        ->assertStatus(409);
});

it('rejects votes after the poll has ended', function () {
    ['poll' => $poll, 'optionA' => $optionA] = makePollThread();
    $poll->update(['ends_at' => now()->subMinute()]);
    $voter = User::factory()->create();

    $this->actingAs($voter)
        ->postJson("/api/polls/{$poll->id}/vote", [
            'poll_option_id' => $optionA->id,
        ])
        ->assertStatus(422);
});
