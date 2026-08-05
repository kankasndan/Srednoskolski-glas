<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear Spatie cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions based on your project
        $permissions = [
            'view reports',
            'resolve reports',
            'view sanctions',
            'apply sanctions',
            'remove sanctions',
            'view appeals',
            'resolve appeals',
            'manage users',
            'manage schools',
            'manage posts',
            'manage comments',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name'       => $perm,
                'guard_name' => 'web',
            ]);
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $moderator  = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        $student    = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        // Attach permissions to roles
        $superAdmin->givePermissionTo($permissions);

        $moderator->givePermissionTo([
            'view reports',
            'resolve reports',
            'view sanctions',
            'apply sanctions',
            'view appeals',
            'resolve appeals',
            'manage posts',
            'manage comments',
        ]);

        $student->givePermissionTo([
            'manage posts',
            'manage comments',
        ]);

        // Assign super_admin to user id 1 (adjust if needed)
        $user = User::find(1);
        if ($user) {
            $user->assignRole('super_admin');
        }
    }
}