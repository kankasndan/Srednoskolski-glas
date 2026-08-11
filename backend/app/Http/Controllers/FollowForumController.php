<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\User;
use App\Services\Feed\FeedCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class FollowForumController extends Controller
{
    /**
     * Follow a forum (general or school).
     *
     * POST /api/p/{forum:slug}/follow
     *
     * Following school forums boosts those forums in the personalized home feed.
     * The user's own school forum is still attached at onboarding.
     */
    public function store(Request $request, Forum $forum): JsonResponse
    {
        $user = $request->user();

        /** @var array{is_following: bool, members_count: int} $result */
        $result = DB::transaction(function () use ($user, $forum): array {
            $sync = $user->forums()->syncWithoutDetaching([$forum->id]);

            if ($sync['attached'] !== []) {
                $forum->increment('members_count');
            }

            $forum->refresh();

            return [
                'is_following' => true,
                'members_count' => (int) $forum->members_count,
            ];
        });

        // Affinity / home-bucket changed — rebuild ranked IDs on next feed load.
        FeedCache::forgetForUser($user);

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
     * Unfollow a forum.
     *
     * DELETE /api/p/{forum:slug}/follow
     *
     * Users cannot unfollow their own school forum (set at onboarding).
     * Other school forums and general forums can be unfollowed.
     */
    public function destroy(Request $request, Forum $forum): JsonResponse
    {
        $user = $request->user();
        $this->ensureCanUnfollow($user, $forum);

        /** @var array{is_following: bool, members_count: int} $result */
        $result = DB::transaction(function () use ($user, $forum): array {
            $detached = $user->forums()->detach($forum->id);

            if ($detached > 0 && $forum->members_count > 0) {
                $forum->decrement('members_count');
            }

            $forum->refresh();

            return [
                'is_following' => false,
                'members_count' => (int) $forum->members_count,
            ];
        });

        FeedCache::forgetForUser($user);

        return response()->json([
            'data' => $result,
        ]);
    }

    private function ensureCanUnfollow(User $user, Forum $forum): void
    {
        if ($forum->type !== 'school') {
            return;
        }

        $ownSchoolForumId = $user->schoolForumId();
        if ($ownSchoolForumId !== null && $ownSchoolForumId === (int) $forum->id) {
            throw new UnprocessableEntityHttpException(
                'Не можеш да го отследиш форумот на твоето училиште.',
            );
        }
    }
}
