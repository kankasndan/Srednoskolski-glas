<?php

use App\Models\Appeal;
use App\Models\Sanction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deletes appeals permanently', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $sanction = Sanction::create([
        'user_id' => $user->id,
        'issued_by' => $admin->id,
        'type' => 'warning',
        'reason' => 'Test reason',
    ]);

    $appeal = Appeal::create([
        'sanction_id' => $sanction->id,
        'user_id' => $user->id,
        'explanation' => 'Test appeal explanation',
        'status' => 'pending',
    ]);

    $appeal->delete();

    expect(Appeal::query()->count())->toBe(0)
        ->and(Appeal::whereKey($appeal->id)->exists())->toBeFalse();
});
