<?php

use App\Models\Feedback;
use App\Models\Sanction;
use App\Models\User;
use App\Notifications\NewFeedbackNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function feedbackStaff(string $role = 'admin'): User
{
    $user = User::factory()->create([
        'role' => $role,
        'onboarding_completed_at' => now(),
    ]);
    $user->assignRole($role);

    return $user;
}

function feedbackMember(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'user',
        'onboarding_completed_at' => now(),
    ], $overrides));
}

it('lets a guest submit feedback', function () {
    $response = $this->postJson('/api/feedback', [
        'rating' => 4,
        'message' => 'Сакам повеќе теми за матура.',
    ])->assertCreated();

    expect($response->json('data.id'))->toBeInt();

    $this->assertDatabaseHas('feedback', [
        'user_id' => null,
        'rating' => 4,
        'message' => 'Сакам повеќе теми за матура.',
    ]);
});

it('lets a banned user submit feedback', function () {
    $user = feedbackMember();
    Sanction::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->postJson('/api/feedback', [
            'rating' => 2,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('feedback', [
        'user_id' => $user->id,
        'rating' => 2,
    ]);
});

it('stores the logged-in user on feedback', function () {
    $user = feedbackMember(['username' => 'marko']);

    $this->actingAs($user)
        ->postJson('/api/feedback', [
            'rating' => 5,
            'message' => '',
        ])
        ->assertCreated();

    $this->assertDatabaseHas('feedback', [
        'user_id' => $user->id,
        'rating' => 5,
        'message' => null,
    ]);
});

it('rejects a missing rating', function () {
    $this->postJson('/api/feedback', [
        'message' => 'Без оценка.',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Оцени ја страната од 1 до 5.');
});

it('rejects a rating outside 1 to 5', function () {
    $this->postJson('/api/feedback', [
        'rating' => 6,
    ])
        ->assertUnprocessable();
});

it('notifies staff about new feedback', function () {
    $admin = feedbackStaff('admin');

    $this->postJson('/api/feedback', [
        'rating' => 3,
        'message' => 'Пребарувањето е бавно.',
    ])->assertCreated();

    $feedback = Feedback::query()->first();

    expect($admin->unreadNotifications()->where('type', NewFeedbackNotification::class)->count())->toBe(1)
        ->and($admin->unreadNotifications()->first()?->data['feedback_id'])->toBe($feedback->id);
});

it('lets staff open the feedback inbox', function () {
    Feedback::factory()->guest()->create(['rating' => 5, 'message' => 'Супер е.']);

    $this->actingAs(feedbackStaff('moderator'))
        ->get(route('feedback.index'))
        ->assertSuccessful()
        ->assertSee('Мислења за платформата')
        ->assertSee('Супер е.')
        ->assertSee('Гостин');
});

it('lets an admin mark feedback as reviewed', function () {
    $admin = feedbackStaff('admin');
    $item = Feedback::factory()->guest()->create();

    $this->actingAs($admin)
        ->from(route('feedback.show', $item))
        ->patch(route('feedback.review', $item))
        ->assertRedirect(route('feedback.index'))
        ->assertSessionHas('success');

    expect($item->fresh()->isReviewed())->toBeTrue()
        ->and($item->fresh()->reviewed_by)->toBe($admin->id);
});

it('lets a moderator mark feedback as reviewed', function () {
    $item = Feedback::factory()->create();

    $this->actingAs(feedbackStaff('moderator'))
        ->patch(route('feedback.review', $item))
        ->assertRedirect(route('feedback.index'))
        ->assertSessionHas('success');

    expect($item->fresh()->isReviewed())->toBeTrue();
});

it('lets staff undo a review', function () {
    $admin = feedbackStaff('admin');
    $item = Feedback::factory()->guest()->create([
        'reviewed_at' => now(),
        'reviewed_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->patch(route('feedback.unreview', $item))
        ->assertRedirect(route('feedback.show', $item))
        ->assertSessionHas('success');

    expect($item->fresh()->isReviewed())->toBeFalse()
        ->and($item->fresh()->reviewed_by)->toBeNull();
});

it('lets an admin delete feedback', function () {
    $item = Feedback::factory()->create();

    $this->actingAs(feedbackStaff('admin'))
        ->from(route('feedback.index'))
        ->delete(route('feedback.destroy', $item))
        ->assertRedirect(route('feedback.index'));

    $this->assertDatabaseMissing('feedback', ['id' => $item->id]);
});
