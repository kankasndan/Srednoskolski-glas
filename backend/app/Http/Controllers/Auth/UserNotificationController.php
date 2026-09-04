<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserNotificationResource;
use App\Models\User;
use App\Support\StudentNotificationTypes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class UserNotificationController extends Controller
{
    /**
     * Latest in-app notifications for the signed-in user (student bell only).
     *
     * GET /api/me/notifications
     */
    public function index(Request $request): JsonResponse
    {
        $unreadCount = $request->user()
            ->unreadNotifications()
            ->whereIn('type', StudentNotificationTypes::all())
            ->count();

        $notifications = $this->studentNotifications($request)
            ->latest()
            ->limit(30)
            ->get();

        return response()->json([
            'data' => UserNotificationResource::collection($notifications)->resolve(),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * POST /api/me/notifications/{id}/read
     */
    public function read(Request $request, string $id): JsonResponse
    {
        /** @var DatabaseNotification $notification */
        $notification = $this->studentNotifications($request)
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'data' => (new UserNotificationResource($notification->fresh()))->resolve(),
        ]);
    }

    /**
     * POST /api/me/notifications/read-all
     */
    public function readAll(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications()
            ->whereIn('type', StudentNotificationTypes::all())
            ->update(['read_at' => now()]);

        return response()->json([
            'data' => [
                'ok' => true,
            ],
        ]);
    }

    /**
     * @return MorphMany<DatabaseNotification, User>
     */
    private function studentNotifications(Request $request)
    {
        return $request->user()
            ->notifications()
            ->whereIn('type', StudentNotificationTypes::all());
    }
}
