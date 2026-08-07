<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class FollowForumController extends Controller
{
    /**
     * Follow a general forum.
     *
     * POST /api/p/{forum:slug}/follow
     *
     * School forums cannot be followed manually — students are attached to their
     * school forum during onboarding and that membership is permanent.
     */
    public function store(Request $request, Forum $forum): JsonResponse
    {
        $this->ensureGeneralForum($forum);

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

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
     * Unfollow a general forum.
     *
     * DELETE /api/p/{forum:slug}/follow
     */
    public function destroy(Request $request, Forum $forum): JsonResponse
    {
        $this->ensureGeneralForum($forum);

        $user = $request->user();

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

        return response()->json([
            'data' => $result,
        ]);
    }

    private function ensureGeneralForum(Forum $forum): void
    {
        if ($forum->type !== 'general') {
            throw new UnprocessableEntityHttpException(
                'Училишните форуми не можат да се следат или отследат рачно.'
            );
        }
    }
}
