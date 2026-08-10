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

        // Self-heal Spatie permissions for older accounts created before this feature.
        $syncPermissions->ensureFresh($user);

        return response()->json([
            'user' => $user,
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            'capabilities' => [
                'can_create_comments' => $user->canCreateComments(),
                // Coarse flag: may start threads somewhere (own school + general forums).
                'can_create_threads' => $user->hasCompletedOnboarding()
                    && ($user->can('create threads') || $user->can('manage threads')),
                'school_forum_id' => $user->schoolForumId(),
            ],
        ]);
    }
}
