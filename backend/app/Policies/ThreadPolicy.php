<?php

namespace App\Policies;

use App\Models\Forum;
use App\Models\User;
use App\Support\SyncUserContentPermissions;

class ThreadPolicy
{
    /**
     * Create a thread in the given forum.
     *
     * Rules:
     * - Guest / incomplete onboarding → deny
     * - Needs Spatie "create threads" (granted only when user belongs to a school)
     * - General forums → allowed for school members
     * - School forums → only the user's own school forum
     */
    public function create(User $user, Forum $forum): bool
    {
        if ($user->onboarding_completed_at === null) {
            return false;
        }

        // Staff bypass.
        if ($user->hasAnyRole(['super_admin', 'admin', 'moderator']) || $user->can('manage threads')) {
            return true;
        }

        if (! $user->can(SyncUserContentPermissions::CREATE_THREADS)) {
            return false;
        }

        if ($forum->type === 'general') {
            return $user->belongsToSchool();
        }

        if ($forum->type === 'school') {
            $schoolForumId = $user->schoolForumId();

            return $schoolForumId !== null && $schoolForumId === (int) $forum->id;
        }

        return false;
    }
}
