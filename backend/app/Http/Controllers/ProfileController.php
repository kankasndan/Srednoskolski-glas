<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Resources\ForumResource;
use App\Http\Resources\ProfileCommentResource;
use App\Http\Resources\PublicUserResource;
use App\Http\Resources\ThreadResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use FiltersThreads;

    /**
     * Lightweight tab badges for the profile page.
     *
     * GET /api/me/counts
     */
    public function counts(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'threads' => $user->threads()->count(),
                'comments' => $user->comments()->count(),
                'followed_forums' => $user->forums()->count(),
                'following_users' => $user->following()->count(),
            ],
        ]);
    }

    /**
     * Threads created by the authenticated user (newest first).
     *
     * GET /api/me/threads
     */
    public function threads(Request $request): JsonResponse
    {
        $user = $request->user();

        $threads = $user->threads()
            ->with($this->threadListWith($user))
            ->withCount('comments')
            ->withExists([
                'votes as has_voted' => fn ($votes) => $votes->where('user_id', $user->id),
            ])
            ->latest()
            ->limit(50)
            ->get();

        return ThreadResource::collection($threads)->response();
    }

    /**
     * Comments written by the authenticated user, with parent thread context.
     *
     * GET /api/me/comments
     */
    public function comments(Request $request): JsonResponse
    {
        $user = $request->user();

        $comments = $user->comments()
            ->with([
                'thread.forum',
            ])
            ->withExists([
                'votes as has_voted' => fn ($votes) => $votes->where('user_id', $user->id),
            ])
            ->latest()
            ->limit(50)
            ->get();

        return ProfileCommentResource::collection($comments)->response();
    }

    /**
     * Forums the authenticated user follows.
     *
     * GET /api/me/followed-forums
     */
    public function followedForums(Request $request): JsonResponse
    {
        $forums = $request->user()
            ->forums()
            ->orderBy('name')
            ->limit(100)
            ->get();

        return ForumResource::collection($forums)->response();
    }

    /**
     * Users the authenticated user follows.
     *
     * GET /api/me/following-users
     */
    public function followingUsers(Request $request): JsonResponse
    {
        $users = $request->user()
            ->following()
            ->whereNotNull('username')
            ->whereNotNull('onboarding_completed_at')
            ->with([
                'studentData.school.city',
                'studentData.school.forum',
                'studentData.vocation',
            ])
            ->orderBy('username')
            ->limit(100)
            ->get();

        return PublicUserResource::collection($users)->response();
    }
}

