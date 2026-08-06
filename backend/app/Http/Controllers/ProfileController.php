<?php

namespace App\Http\Controllers;

use App\Http\Resources\ForumResource;
use App\Http\Resources\ProfileCommentResource;
use App\Http\Resources\ThreadResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Threads created by the authenticated user (newest first).
     *
     * GET /api/me/threads
     */
    public function threads(Request $request): JsonResponse
    {
        $user = $request->user();

        $threads = $user->threads()
            ->with([
                'user.studentData.school.city',
                'forum',
                'threadAttachment',
                'poll.options' => fn ($q) => $q->withCount('votes'),
                'poll.votes',
            ])
            ->withCount('comments')
            ->withExists([
                'votes as has_voted' => fn ($votes) => $votes->where('user_id', $user->id),
            ])
            ->latest()
            ->get();

        $threads->each(function ($thread): void {
            if ($thread->poll) {
                $thread->poll->loadCount('votes');
            }
        });

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
                'user.studentData.school.city',
                'thread.forum',
            ])
            ->withExists([
                'votes as has_voted' => fn ($votes) => $votes->where('user_id', $user->id),
            ])
            ->latest()
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
            ->get();

        return ForumResource::collection($forums)->response();
    }
}
