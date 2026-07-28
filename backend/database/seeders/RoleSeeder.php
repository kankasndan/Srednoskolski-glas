<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Spatie roles used by the admin panel. Mirror of users.role enum values
     * (except plain "user", which needs no Spatie role).
     *
     * @var list<string>
     */
    private const ROLES = [
        'super_admin',
        'admin',
        'moderator',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::ROLES as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
