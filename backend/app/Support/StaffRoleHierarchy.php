<?php

namespace App\Support;

use App\Models\User;

class StaffRoleHierarchy
{
    public const RANKS = [
        'user' => 0,
        'moderator' => 1,
        'admin' => 2,
        'super_admin' => 3,
    ];

    public static function rank(?string $role): int
    {
        return self::RANKS[$role] ?? 0;
    }

    /**
     * Actor may manage target only when the target is strictly below them.
     * Equal or higher ranks (including other super_admins) are protected.
     */
    public static function canManage(User $actor, User $target): bool
    {
        if ($actor->is($target)) {
            return false;
        }

        return self::rank($actor->role) > self::rank($target->role);
    }

    /**
     * Actor may assign a role only when that role is strictly below their own.
     */
    public static function canAssign(User $actor, string $role): bool
    {
        if (! array_key_exists($role, self::RANKS) || $role === 'user') {
            return false;
        }

        return self::rank($actor->role) > self::rank($role);
    }

    /**
     * @return list<string>
     */
    public static function assignableRoles(User $actor): array
    {
        return array_values(array_filter(
            array_keys(self::RANKS),
            fn (string $role): bool => self::canAssign($actor, $role)
        ));
    }

    public static function isStaff(?string $role): bool
    {
        return self::rank($role) >= self::RANKS['moderator'];
    }

    /**
     * Staff if either the users.role column or an assigned Spatie staff role
     * says so. Checking only one leaves an inconsistent staff account
     * treatable as a regular user.
     */
    public static function isStaffAccount(User $user): bool
    {
        return self::isStaff($user->role)
            || $user->hasAnyRole(['moderator', 'admin', 'super_admin']);
    }
}
