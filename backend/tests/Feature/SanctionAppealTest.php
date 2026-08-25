<?php

use App\Models\Appeal;
use App\Models\Comment;
use App\Models\Forum;
use App\Models\Report;
use App\Models\Sanction;
use App\Models\Thread;
use App\Models\User;
use App\Notifications\NewAppealNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function appealUser(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'user',
        'onboarding_completed_at' => now(),
    ], $overrides));
}

function appealStaff(string $role = 'admin'): User
{
    $user = User::factory()->create([
        'role' => $role,
        'onboarding_completed_at' => now(),
    ]);
    $user->assignRole($role);

    return $user;
}

function appealForum(): Forum
{
    return Forum::query()->create([
        'name' => 'Општи дискусии',
        'slug' => 'opshti-appeal-'.uniqid(),
        'description' => 'General',
        'type' => 'general',
        'school_id' => null,
        'imageUrl' => 'https://example.com/image.png',
        'bannerUrl' => 'https://example.com/banner.png',
        'members_count' => 0,
        'threads_count' => 0,
    ]);
}

function appealThread(User $author, Forum $forum, string $title = 'Reported thread', string $description = 'Thread body'): Thread
{
    return Thread::forceCreate([
        'title' => $title,
        'description' => $description,
        'user_id' => $author->id,
        'forum_id' => $forum->id,
        'is_anonymous' => false,
        'upvotes' => 0,
        'views' => 0,
    ]);
}

function appealReport(User $reporter, Comment|Thread $reportable): Report
{
    return Report::query()->create([
        'reporter_id' => $reporter->id,
        'reportable_id' => $reportable->id,
        'reportable_type' => $reportable::class,
        'reason' => 'insulting_content',
        'status' => 'approved',
        'source' => 'human',
    ]);
}

it('includes the admin reason and comment on the sanction notice', function () {
    $user = appealUser();
    $reporter = appealUser();
    $forum = appealForum();
    $thread = appealThread($user, $forum);
    $comment = Comment::forceCreate([
        'thread_id' => $thread->id,
        'user_id' => $user->id,
        'content' => 'Навредувачки коментар.',
        'upvotes' => 0,
    ]);
    $report = appealReport($reporter, $comment);
    $sanction = Sanction::factory()->create([
        'user_id' => $user->id,
        'report_id' => $report->id,
        'reason' => 'Навреда во коментар.',
    ]);

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice.id', $sanction->id)
        ->assertJsonPath('sanction_notice.reason', 'Навреда во коментар.')
        ->assertJsonPath('sanction_notice.content.type', 'comment')
        ->assertJsonPath('sanction_notice.content.body', 'Навредувачки коментар.')
        ->assertJsonPath('sanction_notice.can_appeal', true)
        ->assertJsonPath('sanction_notice.has_pending_appeal', false);
});

it('still includes a deleted comment on the sanction notice', function () {
    $user = appealUser();
    $reporter = appealUser();
    $forum = appealForum();
    $thread = appealThread($user, $forum);
    $comment = Comment::forceCreate([
        'thread_id' => $thread->id,
        'user_id' => $user->id,
        'content' => 'Избришан коментар.',
        'upvotes' => 0,
    ]);
    $report = appealReport($reporter, $comment);
    Sanction::factory()->create([
        'user_id' => $user->id,
        'report_id' => $report->id,
        'reason' => 'Навреда.',
    ]);
    $comment->delete();

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice.content.type', 'comment')
        ->assertJsonPath('sanction_notice.content.body', 'Избришан коментар.');
});

it('includes the thread on the sanction notice', function () {
    $user = appealUser();
    $reporter = appealUser();
    $forum = appealForum();
    $thread = appealThread($user, $forum, 'Лоша дискусија', 'Текст на дискусијата.');
    $report = appealReport($reporter, $thread);
    Sanction::factory()->create([
        'user_id' => $user->id,
        'report_id' => $report->id,
        'reason' => 'Спам дискусија.',
    ]);

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice.reason', 'Спам дискусија.')
        ->assertJsonPath('sanction_notice.content.type', 'thread')
        ->assertJsonPath('sanction_notice.content.title', 'Лоша дискусија')
        ->assertJsonPath('sanction_notice.content.body', 'Текст на дискусијата.');
});

it('omits content when the sanction has no report', function () {
    $user = appealUser();
    Sanction::factory()->create([
        'user_id' => $user->id,
        'reason' => 'Рачно издадена забрана.',
        'report_id' => null,
    ]);

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice.reason', 'Рачно издадена забрана.')
        ->assertJsonPath('sanction_notice.content', null);
});

it('lets a banned user submit an appeal', function () {
    $user = appealUser();
    $sanction = Sanction::factory()->permanent()->create(['user_id' => $user->id]);

    expect($user->isBanned())->toBeTrue();

    $this->actingAs($user)
        ->postJson("/api/me/sanctions/{$sanction->id}/appeals", [
            'explanation' => 'Коментарот беше сарказам, не навреда.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending');

    expect(Appeal::query()->where('sanction_id', $sanction->id)->where('user_id', $user->id)->exists())->toBeTrue();

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('sanction_notice.can_appeal', false)
        ->assertJsonPath('sanction_notice.has_pending_appeal', true);
});

it('rejects a second appeal on the same sanction', function () {
    $user = appealUser();
    $sanction = Sanction::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->postJson("/api/me/sanctions/{$sanction->id}/appeals", [
            'explanation' => 'Прва жалба за оваа санкција.',
        ])
        ->assertCreated();

    $this->actingAs($user)
        ->postJson("/api/me/sanctions/{$sanction->id}/appeals", [
            'explanation' => 'Втора жалба за истата санкција.',
        ])
        ->assertUnprocessable();
});

it('does not let a user appeal someone elses sanction', function () {
    $user = appealUser();
    $other = appealUser();
    $sanction = Sanction::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user)
        ->postJson("/api/me/sanctions/{$sanction->id}/appeals", [
            'explanation' => 'Ова не е моја санкција.',
        ])
        ->assertNotFound();
});

it('lets admin accept an appeal and lift the ban', function () {
    $actor = appealStaff('admin');
    $user = appealUser();
    $sanction = Sanction::factory()->permanent()->create(['user_id' => $user->id]);
    $appeal = Appeal::query()->create([
        'sanction_id' => $sanction->id,
        'user_id' => $user->id,
        'explanation' => 'Барам да се отстрани забраната.',
        'status' => 'pending',
    ]);

    expect($user->isBanned())->toBeTrue();

    $this->actingAs($actor)
        ->from(route('appeal.show', $appeal))
        ->patch(route('appeal.accept', $appeal))
        ->assertRedirect(route('appeal.index'))
        ->assertSessionHas('success');

    expect($user->fresh()->isBanned())->toBeFalse()
        ->and(Sanction::query()->whereKey($sanction->id)->exists())->toBeFalse()
        ->and(Sanction::withTrashed()->find($sanction->id)?->revoked_at)->not->toBeNull()
        ->and($appeal->fresh()?->status)->toBe('accepted');
});

it('notifies staff about a new appeal without a report counter', function () {
    $staff = appealStaff('admin');
    $user = appealUser(['username' => 'marko_appeal']);
    $sanction = Sanction::factory()->permanent()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->postJson("/api/me/sanctions/{$sanction->id}/appeals", [
            'explanation' => 'Коментарот беше сарказам, не навреда.',
        ])
        ->assertCreated();

    $notification = $staff->fresh()->unreadNotifications->first();
    $appeal = Appeal::query()->where('user_id', $user->id)->first();

    expect($staff->fresh()->unreadNotifications)->toHaveCount(1)
        ->and($notification?->type)->toBe(NewAppealNotification::class)
        ->and($notification?->data['title'])->toBe('Нова жалба')
        ->and($notification?->data['message'])->toContain('marko_appeal')
        ->and($notification?->data)->not->toHaveKey('count')
        ->and($notification?->data['url'])->toBe(route('appeal.show', $appeal));
});

it('opens the appeal when staff click the notification', function () {
    $staff = appealStaff('admin');
    $user = appealUser();
    $sanction = Sanction::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->postJson("/api/me/sanctions/{$sanction->id}/appeals", [
            'explanation' => 'Барам преиспитување на санкцијата.',
        ])
        ->assertCreated();

    $appeal = Appeal::query()->where('user_id', $user->id)->firstOrFail();
    $notification = $staff->fresh()->unreadNotifications->first();

    $this->actingAs($staff)
        ->post(route('admin.notifications.read', $notification->id))
        ->assertRedirect(route('appeal.show', $appeal));

    expect($notification->fresh()?->read_at)->not->toBeNull();
});

it('marks appeal notifications read when the appeal is accepted', function () {
    $staff = appealStaff('admin');
    $user = appealUser();
    $sanction = Sanction::factory()->permanent()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->postJson("/api/me/sanctions/{$sanction->id}/appeals", [
            'explanation' => 'Барам да се отстрани забраната.',
        ])
        ->assertCreated();

    expect($staff->fresh()->unreadNotifications)->toHaveCount(1);

    $appeal = Appeal::query()->where('user_id', $user->id)->firstOrFail();

    $this->actingAs($staff)
        ->from(route('appeal.show', $appeal))
        ->patch(route('appeal.accept', $appeal))
        ->assertRedirect(route('appeal.index'));

    expect($staff->fresh()->unreadNotifications)->toHaveCount(0);
});
