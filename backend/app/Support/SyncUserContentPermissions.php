<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Keeps Spatie content permissions in sync with onboarding + school membership.
 *
 * - Guest / incomplete onboarding → no create permissions
 * - Onboarded without school → create comments only
 * - Onboarded with school → create comments + create threads
 */
final class SyncUserContentPermissions
{
    public const CREATE_COMMENTS = 'create comments';

    public const CREATE_THREADS = 'create threads';

    public function handle(User $user): void
    {
        Permission::findOrCreate(self::CREATE_COMMENTS, 'web');
        Permission::findOrCreate(self::CREATE_THREADS, 'web');
        Role::findOrCreate('user', 'web');

        $isStaff = $user->hasAnyRole(['super_admin', 'admin', 'moderator']);

        // Incomplete onboarding: no content role / create permissions.
        if ($user->onboarding_completed_at === null) {
            if (! $isStaff && $user->hasRole('user')) {
                $user->removeRole('user');
            }
            $user->revokePermissionTo([self::CREATE_COMMENTS, self::CREATE_THREADS]);

            return;
        }

        if (! $isStaff && ! $user->hasRole('user')) {
            $user->assignRole('user');
        }

        // Role "user" already includes "create comments". Ensure school members can start threads.
        if ($user->belongsToSchool()) {
            if (! $user->hasPermissionTo(self::CREATE_THREADS)) {
                $user->givePermissionTo(self::CREATE_THREADS);
            }
        } elseif ($user->hasDirectPermission(self::CREATE_THREADS)) {
            $user->revokePermissionTo(self::CREATE_THREADS);
        }
    }

    public function ensureFresh(User $user): void
    {
        if ($user->onboarding_completed_at === null) {
            if ($user->hasRole('user') || $user->hasDirectPermission(self::CREATE_THREADS) || $user->hasDirectPermission(self::CREATE_COMMENTS)) {
                $this->handle($user);
            }

            return;
        }

        $missingRole = ! $user->hasAnyRole(['user', 'super_admin', 'admin', 'moderator']);
        $missingComments = ! $user->can(self::CREATE_COMMENTS) && ! $user->can('manage comments');
        $missingThreads = $user->belongsToSchool() && ! $user->can(self::CREATE_THREADS) && ! $user->can('manage threads');
        $extraThreads = ! $user->belongsToSchool() && $user->hasDirectPermission(self::CREATE_THREADS);

        if ($missingRole || $missingComments || $missingThreads || $extraThreads) {
            $this->handle($user);
        }
    }
}
