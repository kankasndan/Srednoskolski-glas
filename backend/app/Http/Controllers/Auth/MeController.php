<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\SyncUserContentPermissions;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request, SyncUserContentPermissions $syncPermissions)
    {
        $user = $request->user()->load([
            'studentData.school.city',
            'studentData.school.forum',
            'studentData.vocation',
        ]);

        $syncPermissions->ensureFresh($user);

        $canManageThreads = $user->can('manage threads')
            || $user->hasAnyRole(['super_admin', 'admin', 'moderator']);

        // Prefer school membership for the UI flag; Spatie permission is kept in sync above.
        $canCreateThreads = $user->hasCompletedOnboarding()
            && ($canManageThreads || $user->belongsToSchool());

        return response()->json([
            'user' => $user,
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            'capabilities' => [
                'has_completed_onboarding' => $user->hasCompletedOnboarding(),
                'can_create_comments' => $user->canCreateComments(),
                'can_create_threads' => $canCreateThreads,
                'school_forum_id' => $user->schoolForumId(),
            ],
        ]);
    }
}
