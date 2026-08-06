<?php

use App\Models\Appeal;
use App\Models\Sanction;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('passes explicit totals to the appeals index view', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create(['role' => 'admin']);
    $admin->syncRoles(['admin']);
    $user = User::factory()->create();

    $sanction = Sanction::create([
        'user_id' => $user->id,
        'issued_by' => $admin->id,
        'type' => 'warning',
        'reason' => 'Test reason',
    ]);

    Appeal::create([
        'sanction_id' => $sanction->id,
        'user_id' => $user->id,
        'explanation' => 'Pending appeal',
        'status' => 'pending',
    ]);

    $resolvedAppeal = Appeal::create([
        'sanction_id' => $sanction->id,
        'user_id' => $user->id,
        'explanation' => 'Resolved appeal',
        'status' => 'accepted',
    ]);

    $resolvedAppeal->delete();

    $this->actingAs($admin)
        ->get(route('appeal.index'))
        ->assertOk()
        ->assertViewHas('activeAppealsTotal', 1)
        ->assertViewHas('resolvedAppealsTotal', 1);
});
