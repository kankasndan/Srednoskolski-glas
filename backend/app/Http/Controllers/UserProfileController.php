<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Resources\ForumResource;
use App\Http\Resources\ProfileCommentResource;
use App\Http\Resources\PublicUserResource;
use App\Http\Resources\ThreadResource;
use App\Models\User;
use App\Services\Feed\FeedCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    use FiltersThreads;

    /**
     * Public profile header + tab counts.
     *
     * GET /api/u/{username}
     */
    public function show(Request $request, string $username): JsonResponse
    {
        $profileUser = $this->findPublicUser($username);
        $viewer = $request->user('web') ?? $request->user();

        return response()->json([
            'data' => [
                'user' => new PublicUserResource($profileUser),
                'counts' => [
                    'threads' => $profileUser->threads()->count(),
                    'comments' => $profileUser->comments()->count(),
                    'followed_forums' => $profileUser->forums()->count(),
                    'followers' => $profileUser->followers()->count(),
                ],
                'is_following' => $viewer !== null
                    && (int) $viewer->id !== (int) $profileUser->id
                    && $viewer->following()->where('following_id', $profileUser->id)->exists(),
                'is_own_profile' => $viewer !== null
                    && (int) $viewer->id === (int) $profileUser->id,
            ],
        ]);
    }

    /**
     * GET /api/u/{username}/threads
     */
    public function threads(Request $request, string $username): JsonResponse
    {
        $profileUser = $this->findPublicUser($username);
        $viewer = $request->user('web') ?? $request->user();

        $query = $profileUser->threads()
            ->with($this->threadListWith($viewer))
            ->withCount('comments')
            ->latest()
            ->limit(50);

        $this->applyHasVoted($query, $viewer);

        return ThreadResource::collection($query->get())->response();
    }

    /**
     * GET /api/u/{username}/comments
     */
    public function comments(Request $request, string $username): JsonResponse
    {
        $profileUser = $this->findPublicUser($username);
        $viewer = $request->user('web') ?? $request->user();

        $query = $profileUser->comments()
            ->with(['thread.forum'])
            ->latest()
            ->limit(50);

        if ($viewer !== null) {
            $query->withExists([
                'votes as has_voted' => fn ($votes) => $votes->where('user_id', $viewer->id),
            ]);
        }

        return ProfileCommentResource::collection($query->get())->response();
    }

    /**
     * GET /api/u/{username}/followed-forums
     */
    public function followedForums(string $username): JsonResponse
    {
        $profileUser = $this->findPublicUser($username);

        $forums = $profileUser->forums()
            ->orderBy('name')
            ->limit(100)
            ->get();

        return ForumResource::collection($forums)->response();
    }

    /**
     * POST /api/u/{username}/follow
     */
    public function follow(Request $request, string $username): JsonResponse
    {
        $profileUser = $this->findPublicUser($username);
        $viewer = $request->user();

        abort_if((int) $viewer->id === (int) $profileUser->id, 422, 'Не можеш да се следиш себеси.');

        $viewer->following()->syncWithoutDetaching([$profileUser->id]);

        // Author-affinity signal changed for the personalized feed.
        FeedCache::forgetForUser($viewer);

        return response()->json([
            'data' => [
                'is_following' => true,
                'followers' => $profileUser->followers()->count(),
            ],
        ]);
    }

    /**
     * DELETE /api/u/{username}/follow
     */
    public function unfollow(Request $request, string $username): JsonResponse
    {
        $profileUser = $this->findPublicUser($username);
        $viewer = $request->user();

        $viewer->following()->detach($profileUser->id);

        FeedCache::forgetForUser($viewer);

        return response()->json([
            'data' => [
                'is_following' => false,
                'followers' => $profileUser->followers()->count(),
            ],
        ]);
    }

    private function findPublicUser(string $username): User
    {
        return User::query()
            ->where('username', $username)
            ->whereNotNull('username')
            ->whereNotNull('onboarding_completed_at')
            ->with([
                'studentData.school.city',
                'studentData.school.forum',
                'studentData.vocation',
            ])
            ->firstOrFail();
    }
}
