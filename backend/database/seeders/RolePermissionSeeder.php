<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear Spatie cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions based on your project
        $permissions = [
            // Admin access
            'access admin panel',

            // Dashboard
            'view dashboard',
            'export dashboard',

            // Profile
            'view own profile',
            'update own profile',
            'update own profile images',
            'update own password',

            // Reports
            'view reports',
            'approve reports',
            'reject reports',

            // Sanctions
            'view sanctions',
            'create sanctions',
            'remove sanctions',

            // Appeals
            'view appeals',
            'search appeals',
            'view appeal details',   // show
            'accept appeals',
            'reject appeals',

            // Users
            'view users',
            'search users',
            'view user details',
            'export user as pdf',

            // Forums
            'view forums',
            'search forums',
            'create forums',
            'update forums',
            'delete forums',
            'view forum details',

            // Roles & staff management
            'view roles page',
            'update user role',
            'delete user role',
            'update forum role settings',
            'search staff',
            'view staff details',
            'search grant role candidates',
            'grant roles',

            // Auth
            'logout admin',

            'manage threads',
            'manage comments',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'web',
            ]);
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $moderator = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Attach permissions to roles
        $superAdmin->givePermissionTo($permissions);

        $moderator->givePermissionTo([
            // Admin access
            'access admin panel',

            // Dashboard
            'view dashboard',
            'export dashboard',

            // Profile
            'view own profile',
            'update own profile',
            'update own profile images',
            'update own password',

            // Reports
            'view reports',
            'approve reports',
            'reject reports',

            // Sanctions
            'view sanctions',
            'create sanctions',
            'remove sanctions',

            // Appeals
            'view appeals',
            'search appeals',
            'view appeal details',   // show
            'accept appeals',
            'reject appeals',

            // Users
            'view users',
            'search users',
            'view user details',
            'export user as pdf',

            // Forums
            'view forums',
            'search forums',
            'create forums',
            'update forums',
            'delete forums',
            'view forum details',
        ]);

        $admin->givePermissionTo([
            // Admin access
            'access admin panel',

            // Dashboard
            'view dashboard',
            'export dashboard',

            // Profile
            'view own profile',
            'update own profile',
            'update own profile images',
            'update own password',

            // Reports
            'view reports',
            'approve reports',
            'reject reports',

            // Sanctions
            'view sanctions',
            'create sanctions',
            'remove sanctions',

            // Appeals
            'view appeals',
            'search appeals',
            'view appeal details',   // show
            'accept appeals',
            'reject appeals',

            // Users
            'view users',
            'search users',
            'view user details',
            'export user as pdf',

            // Forums
            'view forums',
            'search forums',
            'create forums',
            'update forums',
            'delete forums',
            'view forum details',
        ]);

        $user->givePermissionTo([
            'manage threads',
            'manage comments',
        ]);
    }
}
