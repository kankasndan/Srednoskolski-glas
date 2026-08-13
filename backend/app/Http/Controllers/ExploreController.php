<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Resources\ForumResource;
use App\Http\Resources\ThreadResource;
use App\Models\Comment;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    use FiltersThreads;

    private const TOP_FORUMS = 4;

    private const TOP_THREADS = 20;

    /**
     * Explore page payload in one round-trip.
     *
     * GET /api/explore
     *
     * - forums: top 4 general forums by views
     * - threads: general-forum threads created in the last 7 days, ranked by
     *   interactions (votes + comments) in that same window
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('web') ?? $request->user();
        $since = now()->subWeek();

        $forums = Forum::query()
            ->where('type', 'general')
            ->orderByDesc('views')
            ->orderBy('name')
            ->limit(self::TOP_FORUMS)
            ->get();

        // One lookup for follow state on the 4 cards (avoids N+1 in ForumResource).
        $followedForumIds = $user !== null
            ? $user->forums()->pluck('forums.id')->map(fn ($id) => (int) $id)->all()
            : [];

        $forums->each(function (Forum $forum) use ($followedForumIds): void {
            $forum->setAttribute('is_following', in_array((int) $forum->id, $followedForumIds, true));
        });

        $threadClass = Thread::class;

        $threadsQuery = Thread::query()
            ->select('threads.*')
            ->join('forums', 'forums.id', '=', 'threads.forum_id')
            ->where('forums.type', 'general')
            ->where('threads.created_at', '>=', $since)
            ->selectSub(
                Vote::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('votes.votable_id', 'threads.id')
                    ->where('votes.votable_type', $threadClass)
                    ->where('votes.created_at', '>=', $since),
                'week_votes_count',
            )
            ->selectSub(
                Comment::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('comments.thread_id', 'threads.id')
                    ->where('comments.created_at', '>=', $since)
                    ->whereNull('comments.deleted_at'),
                'week_comments_count',
            )
            ->with($this->threadListWith($user))
            ->withCount('comments')
            ->orderByRaw('(week_votes_count + week_comments_count) desc')
            ->orderByDesc('threads.created_at')
            ->limit(self::TOP_THREADS);

        $this->applyHasVoted($threadsQuery, $user);

        $threads = $threadsQuery->get();

        return response()->json([
            'data' => [
                'forums' => $forums->map(function (Forum $forum) use ($user) {
                    $payload = (new ForumResource($forum))->resolve();
                    if ($user !== null) {
                        $payload['is_following'] = (bool) $forum->is_following;
                    }

                    return $payload;
                })->values()->all(),
                'threads' => ThreadResource::collection($threads)->resolve(),
            ],
        ]);
    }
}
