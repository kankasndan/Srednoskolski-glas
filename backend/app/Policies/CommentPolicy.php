<?php

namespace App\Policies;

use App\Models\Thread;
use App\Models\User;
use App\Support\SyncUserContentPermissions;

class CommentPolicy
{
    /**
     * Comment on any thread (including other schools) when onboarded.
     */
    public function create(User $user, Thread $thread): bool
    {
        if ($user->onboarding_completed_at === null) {
            return false;
        }

        if ($user->hasAnyRole(['super_admin', 'admin', 'moderator']) || $user->can('manage comments')) {
            return true;
        }

        return $user->can(SyncUserContentPermissions::CREATE_COMMENTS);
    }
}
