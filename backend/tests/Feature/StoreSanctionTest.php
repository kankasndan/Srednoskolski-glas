<?php

use App\Models\Sanction;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function sanctionStaff(string $role = 'admin'): User
{
    $user = User::factory()->create([
        'role' => $role,
        'onboarding_completed_at' => now(),
    ]);
    $user->assignRole($role);

    return $user;
}

function sanctionTarget(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'user',
        'onboarding_completed_at' => now(),
    ], $overrides));
}

it('lets staff ban a regular user who has no Spatie user role', function () {
    $actor = sanctionStaff('admin');
    $target = sanctionTarget();

    expect($target->hasRole('user'))->toBeFalse();

    $this->actingAs($actor)
        ->from(route('sanction.index'))
        ->post(route('sanction.create'), [
            'user_id' => $target->id,
            'type' => '7-day',
            'reason' => 'Повторно прекршување на правилата.',
        ])
        ->assertRedirect(route('sanction.index'))
        ->assertSessionHas('success');

    expect(Sanction::query()->where('user_id', $target->id)->where('type', '7-day')->exists())->toBeTrue();
});

it('lets staff search a user who already has a sanction', function () {
    $actor = sanctionStaff('admin');
    $target = sanctionTarget(['username' => 'banned_marko']);

    Sanction::factory()->create(['user_id' => $target->id]);

    $this->actingAs($actor)
        ->getJson(route('user.liveSearch', ['q' => 'banned_marko']))
        ->assertSuccessful()
        ->assertJsonFragment(['id' => $target->id, 'username' => 'banned_marko']);
});

it('lets staff issue another sanction without removing the previous one', function () {
    $actor = sanctionStaff('admin');
    $target = sanctionTarget();

    Sanction::factory()->create([
        'user_id' => $target->id,
        'type' => 'warning',
        'reason' => 'Прво предупредување.',
    ]);

    $this->actingAs($actor)
        ->from(route('sanction.index'))
        ->post(route('sanction.create'), [
            'user_id' => $target->id,
            'type' => '7-day',
            'reason' => 'Повторно прекршување.',
        ])
        ->assertRedirect(route('sanction.index'))
        ->assertSessionHas('success');

    expect(Sanction::query()->where('user_id', $target->id)->count())->toBe(2)
        ->and(Sanction::query()->where('user_id', $target->id)->where('type', '7-day')->exists())->toBeTrue();
});

it('lets staff remove a ban and issue a new one to the same user', function () {
    $actor = sanctionStaff('admin');
    $target = sanctionTarget();

    $this->actingAs($actor)
        ->from(route('sanction.index'))
        ->post(route('sanction.create'), [
            'user_id' => $target->id,
            'type' => '7-day',
            'reason' => 'Прва забрана.',
        ])
        ->assertRedirect(route('sanction.index'));

    $sanction = Sanction::query()->where('user_id', $target->id)->firstOrFail();

    $this->actingAs($actor)
        ->from(route('sanction.index'))
        ->delete(route('sanction.remove', $sanction))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->actingAs($actor)
        ->from(route('sanction.index'))
        ->post(route('sanction.create'), [
            'user_id' => $target->id,
            'type' => 'permanent_ban',
            'reason' => 'Нова трајна забрана.',
        ])
        ->assertRedirect(route('sanction.index'))
        ->assertSessionHas('success');

    expect(Sanction::query()->where('user_id', $target->id)->where('type', 'permanent_ban')->exists())->toBeTrue()
        ->and(Sanction::withTrashed()->whereKey($sanction->id)->first()?->trashed())->toBeTrue();
});

it('rejects sanctions against staff accounts', function () {
    $actor = sanctionStaff('admin');
    $moderator = sanctionStaff('moderator');

    $this->actingAs($actor)
        ->from(route('sanction.index'))
        ->post(route('sanction.create'), [
            'user_id' => $moderator->id,
            'type' => '7-day',
            'reason' => 'Не смее да се банира модератор.',
        ])
        ->assertRedirect(route('sanction.index'))
        ->assertSessionHasErrors(['user_id']);

    expect(Sanction::query()->where('user_id', $moderator->id)->exists())->toBeFalse();
});

it('rejects sanctions against a user column that still has a Spatie staff role', function () {
    $actor = sanctionStaff('admin');
    $inconsistent = sanctionTarget();
    $inconsistent->assignRole('moderator');

    $this->actingAs($actor)
        ->from(route('sanction.index'))
        ->post(route('sanction.create'), [
            'user_id' => $inconsistent->id,
            'type' => '7-day',
            'reason' => 'Скриен модератор.',
        ])
        ->assertRedirect(route('sanction.index'))
        ->assertSessionHasErrors(['user_id']);

    expect(Sanction::query()->where('user_id', $inconsistent->id)->exists())->toBeFalse();
});

it('does not let a moderator issue a permanent ban', function () {
    $actor = sanctionStaff('moderator');
    $target = sanctionTarget();

    $this->actingAs($actor)
        ->from(route('sanction.index'))
        ->post(route('sanction.create'), [
            'user_id' => $target->id,
            'type' => 'permanent_ban',
            'reason' => 'Трајна забрана од модератор.',
        ])
        ->assertRedirect(route('sanction.index'))
        ->assertSessionHasErrors(['type']);

    expect(Sanction::query()->where('user_id', $target->id)->exists())->toBeFalse();
});

it('lets staff issue a custom-duration ban', function () {
    $actor = sanctionStaff('admin');
    $target = sanctionTarget();

    $this->actingAs($actor)
        ->from(route('sanction.index'))
        ->post(route('sanction.create'), [
            'user_id' => $target->id,
            'type' => 'custom',
            'days' => 14,
            'reason' => 'Прилагодена забрана од две недели.',
        ])
        ->assertRedirect(route('sanction.index'))
        ->assertSessionHas('success');

    $sanction = Sanction::query()->where('user_id', $target->id)->first();

    expect($sanction)->not->toBeNull()
        ->and($sanction->type)->toBe('custom')
        ->and($sanction->expires_at?->toDateString())->toBe(now()->addDays(14)->toDateString());
});
