<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeResource;
use App\Services\StudentEnrollment;
use App\Support\SyncUserContentPermissions;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(
        Request $request,
        SyncUserContentPermissions $syncPermissions,
        StudentEnrollment $enrollment,
    ) {
        $user = $request->user()->load([
            'studentData.school.city',
            'studentData.school.forum',
            'studentData.vocation',
        ]);

        $syncPermissions->ensureFresh($user);

        $activeBan = $user->activeBan();

        return response()->json([
            'user' => (new MeResource($user))->resolve(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            'capabilities' => $enrollment->allCapabilities($user),
            'sanction_notice' => $user->pendingSanctionNotice($activeBan),
            'active_ban' => $user->activeBanPayload($activeBan),
        ]);
    }
}
