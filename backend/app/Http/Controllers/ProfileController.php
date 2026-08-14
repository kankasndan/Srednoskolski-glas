<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ForumResource;
use App\Http\Resources\MeResource;
use App\Http\Resources\ProfileCommentResource;
use App\Http\Resources\PublicUserResource;
use App\Http\Resources\ThreadResource;
use App\Models\Comment;
use App\Models\Forum;
use App\Models\User;
use App\Services\StudentEnrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use FiltersThreads;

    public function __construct(private readonly StudentEnrollment $enrollment) {}

    /**
     * Update the authenticated user's avatar and/or school information.
     * Username cannot be changed after onboarding.
     *
     * PUT /api/me
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if (array_key_exists('image_url', $validated)) {
            $imageUrl = $validated['image_url'];

            if ($imageUrl === null || $imageUrl === '') {
                $defaults = config('avatars.defaults', []);
                $imageUrl = $defaults[0] ?? null;
            }

            $user->imageUrl = $imageUrl;
            $user->save();
        }

        if (
            array_key_exists('school', $validated)
            && array_key_exists('area', $validated)
            && array_key_exists('year', $validated)
            && filled($validated['school'])
            && filled($validated['area'])
            && filled($validated['year'])
        ) {
            $this->enrollment->updateFromProfile($user, $validated);
        }

        $user = $user->fresh([
            'studentData.school.city',
            'studentData.school.forum',
            'studentData.vocation',
        ]);

        return response()->json([
            'user' => (new MeResource($user))->resolve(),
            'capabilities' => $this->enrollment->allCapabilities($user),
        ]);
    }

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
                'followed_threads' => $user->followedThreads()->count(),
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
                ...Comment::authorWith(),
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

        $forums->each(fn (Forum $forum) => $forum->setAttribute('is_following', true));

        return ForumResource::collection($forums)->response();
    }

    /**
     * Threads the authenticated user follows (newest follow first).
     *
     * GET /api/me/followed-threads
     */
    public function followedThreads(Request $request): JsonResponse
    {
        $user = $request->user();

        $threads = $user->followedThreads()
            ->with($this->threadListWith($user))
            ->withCount('comments')
            ->withExists([
                'votes as has_voted' => fn ($votes) => $votes->where('user_id', $user->id),
                'followers as is_following' => fn ($followers) => $followers->where('users.id', $user->id),
            ])
            ->orderByDesc('thread_follows.created_at')
            ->limit(50)
            ->get();

        return ThreadResource::collection($threads)->response();
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
