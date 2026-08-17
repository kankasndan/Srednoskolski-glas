<?php

use App\Models\Comment;
use App\Models\Forum;
use App\Models\Mention;
use App\Models\Thread;
use App\Models\User;
use App\Support\MentionParser;
use App\Support\SyncUserContentPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function mentionForum(): Forum
{
    return Forum::query()->create([
        'name' => 'Општи дискусии',
        'slug' => 'opshti-diskusii-mentions',
        'description' => 'General forum',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);
}

function mentionAuthor(): User
{
    $user = User::factory()->create([
        'username' => 'author_mk',
        'onboarding_completed_at' => now(),
    ]);
    app(SyncUserContentPermissions::class)->handle($user);
    $user->givePermissionTo(SyncUserContentPermissions::CREATE_COMMENTS);

    return $user->fresh();
}

function mentionThread(Forum $forum, User $author): Thread
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

it('extracts unique @usernames from comment text', function () {
    expect(MentionParser::usernames('Здраво @ana_k и повторно @ana_k и @marko_p.'))
        ->toBe(['ana_k', 'marko_p']);
});

it('does not treat email addresses as mentions', function () {
    expect(MentionParser::usernames('Пиши на ana@example.com или @ana_k'))
        ->toBe(['ana_k']);
});

it('does not include a trailing period in the username', function () {
    expect(MentionParser::usernames('Прашај го @marko_p.'))->toBe(['marko_p']);
});

it('stores mentions when creating a comment and returns them for display', function () {
    $author = mentionAuthor();
    $mentioned = User::factory()->create([
        'username' => 'ana_k',
        'onboarding_completed_at' => now(),
    ]);
    $thread = mentionThread(mentionForum(), $author);

    $response = $this->actingAs($author)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Што мислиш @ana_k за ова?',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.content', 'Што мислиш @ana_k за ова?')
        ->assertJsonPath('data.mentions.0.id', $mentioned->id)
        ->assertJsonPath('data.mentions.0.username', 'ana_k');

    expect(Mention::query()->where([
        'mentionable_type' => Comment::class,
        'mentionable_id' => $response->json('data.id'),
        'mentioning_user_id' => $author->id,
        'mentioned_user_id' => $mentioned->id,
    ])->exists())->toBeTrue();
});

it('ignores unknown usernames, duplicates, and self mentions', function () {
    $author = mentionAuthor();
    $mentioned = User::factory()->create([
        'username' => 'ana_k',
        'onboarding_completed_at' => now(),
    ]);
    $thread = mentionThread(mentionForum(), $author);

    $response = $this->actingAs($author)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => "@ana_k @ana_k @ghost_user @{$author->username}",
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.mentions.0.username', 'ana_k');

    expect($response->json('data.mentions'))->toHaveCount(1)
        ->and(Mention::query()->where('mentioned_user_id', $mentioned->id)->count())->toBe(1)
        ->and(Mention::query()->where('mentioned_user_id', $author->id)->exists())->toBeFalse();
});

it('replaces mentions when a comment is updated', function () {
    $author = mentionAuthor();
    $first = User::factory()->create([
        'username' => 'ana_k',
        'onboarding_completed_at' => now(),
    ]);
    $second = User::factory()->create([
        'username' => 'marko_p',
        'onboarding_completed_at' => now(),
    ]);
    $thread = mentionThread(mentionForum(), $author);

    $created = $this->actingAs($author)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Здраво @ana_k',
    ])->assertCreated();

    $commentId = $created->json('data.id');

    $updated = $this->actingAs($author)->putJson("/api/comments/{$commentId}", [
        'content' => 'Сега @marko_p',
    ])->assertSuccessful();

    expect($updated->json('data.mentions'))->toHaveCount(1)
        ->and($updated->json('data.mentions.0.username'))->toBe('marko_p');

    expect(Mention::query()->where([
        'mentionable_id' => $commentId,
        'mentioned_user_id' => $first->id,
    ])->exists())->toBeFalse()
        ->and(Mention::query()->where([
            'mentionable_id' => $commentId,
            'mentioned_user_id' => $second->id,
        ])->exists())->toBeTrue();
});

it('clears mentions when @usernames are removed from a comment', function () {
    $author = mentionAuthor();
    User::factory()->create([
        'username' => 'ana_k',
        'onboarding_completed_at' => now(),
    ]);
    $thread = mentionThread(mentionForum(), $author);

    $commentId = $this->actingAs($author)->postJson("/api/threads/{$thread->id}/comments", [
        'content' => 'Здраво @ana_k',
    ])->assertCreated()->json('data.id');

    $this->actingAs($author)->putJson("/api/comments/{$commentId}", [
        'content' => 'Без спомнување.',
    ])->assertSuccessful()
        ->assertJsonPath('data.mentions', []);

    expect(Mention::query()->where('mentionable_id', $commentId)->exists())->toBeFalse();
});
