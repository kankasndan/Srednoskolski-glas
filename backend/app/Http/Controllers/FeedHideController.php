<?php

namespace App\Http\Controllers;

use App\Models\FeedHide;
use App\Models\Thread;
use App\Services\Feed\FeedCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedHideController extends Controller
{
    /**
     * Hide a thread from the caller's personalized feed.
     *
     * POST /api/threads/{thread}/hide
     */
    public function store(Request $request, Thread $thread): JsonResponse
    {
        FeedHide::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'thread_id' => $thread->id,
        ]);

        FeedCache::forgetForUser($request->user());

        return response()->json([
            'data' => [
                'hidden' => true,
                'thread_id' => $thread->id,
            ],
        ]);
    }

    /**
     * Unhide a thread from the caller's feed.
     *
     * DELETE /api/threads/{thread}/hide
     */
    public function destroy(Request $request, Thread $thread): JsonResponse
    {
        FeedHide::query()
            ->where('user_id', $request->user()->id)
            ->where('thread_id', $thread->id)
            ->delete();

        FeedCache::forgetForUser($request->user());

        return response()->json([
            'data' => [
                'hidden' => false,
                'thread_id' => $thread->id,
            ],
        ]);
    }
}
