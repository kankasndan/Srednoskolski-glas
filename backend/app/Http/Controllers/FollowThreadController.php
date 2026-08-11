<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowThreadController extends Controller
{
    /**
     * Follow a thread (visual follow in MVP — no notifications).
     *
     * POST /api/threads/{thread}/follow
     */
    public function store(Request $request, Thread $thread): JsonResponse
    {
        $user = $request->user();

        $user->followedThreads()->syncWithoutDetaching([$thread->id]);

        return response()->json([
            'data' => [
                'is_following' => true,
            ],
        ]);
    }

    /**
     * Unfollow a thread.
     *
     * DELETE /api/threads/{thread}/follow
     */
    public function destroy(Request $request, Thread $thread): JsonResponse
    {
        $user = $request->user();

        $user->followedThreads()->detach($thread->id);

        return response()->json([
            'data' => [
                'is_following' => false,
            ],
        ]);
    }
}
