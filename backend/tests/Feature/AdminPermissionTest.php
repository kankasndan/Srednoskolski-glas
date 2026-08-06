<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows a super admin with access admin panel permission to open admin nav routes', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $admin->syncRoles(['super_admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('report.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('role.index'))
        ->assertOk();
});

it('forbids a regular user from accessing the admin panel', function () {
    $user = User::factory()->create(['role' => 'user']);
    $user->syncRoles(['user']);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('forbids staff without the access admin panel permission', function () {
    $role = Role::findByName('admin', 'web');
    $role->revokePermissionTo('access admin panel');

    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});
