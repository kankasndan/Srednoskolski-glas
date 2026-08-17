<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\SyncUserContentPermissions;
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
            'remove permanent sanctions',

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

            // Schools (kept separate from forums: deleting a school touches
            // student records, which is not the same authority as forum CRUD)
            'view schools',
            'search schools',
            'create schools',
            'delete schools',

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

            // Content moderation (staff)
            'manage threads',
            'manage comments',

            // Content creation (students / onboarded users)
            'create threads',
            'create comments',
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

        // Moderator: day-to-day moderation only (no forum CRUD, appeal resolution, ban removal, exports, roles).
        $moderatorPermissions = [
            'access admin panel',

            'view dashboard',

            'view own profile',
            'update own profile',
            'update own profile images',
            'update own password',

            'view reports',
            'approve reports',
            'reject reports',

            'view sanctions',
            'create sanctions',

            'view appeals',
            'search appeals',
            'view appeal details',

            'view users',
            'search users',
            'view user details',

            'view forums',
            'search forums',
            'view forum details',

            'view schools',
            'search schools',

            'logout admin',

            'manage threads',
            'manage comments',
        ];

        // Admin: operational powers including forums, appeals, sanction removal — not staff role management.
        $adminPermissions = array_merge($moderatorPermissions, [
            'export dashboard',

            'remove sanctions',

            'accept appeals',
            'reject appeals',

            'export user as pdf',

            'create forums',
            'update forums',
            'delete forums',

            'create schools',
            'delete schools',
        ]);

        $superAdmin->syncPermissions($permissions);
        $admin->syncPermissions($adminPermissions);
        $moderator->syncPermissions($moderatorPermissions);

        // Onboarded users may comment anywhere. "create threads" is granted per-user
        // only when they belong to a school (see SyncUserContentPermissions).
        $user->syncPermissions([
            'create comments',
        ]);

        $sync = app(SyncUserContentPermissions::class);
        User::query()
            ->whereNotNull('onboarding_completed_at')
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($sync): void {
                foreach ($users as $existingUser) {
                    $sync->handle($existingUser);
                }
            });
    }
}
