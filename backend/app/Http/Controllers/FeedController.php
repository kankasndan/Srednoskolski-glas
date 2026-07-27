<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Resources\ThreadResource;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FeedController extends Controller
{
    use FiltersThreads;

    /**
     * Cross-forum feed for /feed.
     *
     * - Guest / user with no followed forums → site-wide trending (same filters as forum lists).
     * - User with followed forums → same site-wide pool, but threads from followed forums
     *   and previously viewed threads get a soft score boost so they surface earlier while
     *   other trending threads still appear (important when the user only follows 1–2 forums).
     *
     * Query: page, sort (trending|top|newest|discussed), time (day|week|month|six-months|year|all)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('web') ?? $request->user();

        $query = Thread::query()
            ->with(['user.studentData.school.city', 'threadAttachment', 'forum'])
            ->withCount('comments');

        $this->applyHasVoted($query, $user);

        $followedForumIds = $user instanceof User
            ? $user->forums()->pluck('forums.id')
            : collect();

        if ($followedForumIds->isNotEmpty()) {
            $this->applyPersonalizedFeedOrdering($query, $request, $user, $followedForumIds);
        } else {
            $this->applyThreadFilters($query, $request);
        }

        $threads = $query->paginate($this->threadsPerPage())->withQueryString();

        return ThreadResource::collection($threads)->response();
    }

    /**
     * Site-wide pool with a soft boost for followed forums + viewed threads.
     *
     * @param  Collection<int, int|string>  $followedForumIds
     */
    private function applyPersonalizedFeedOrdering(
        Builder $query,
        Request $request,
        User $user,
        Collection $followedForumIds,
    ): void {
        if ($since = $this->threadTimeWindow($request->query('time'))) {
            $query->where('created_at', '>=', $since);
        }

        $forumPlaceholders = $followedForumIds->map(fn () => '?')->implode(', ');
        $bindings = [...$followedForumIds->all(), $user->id];

        $relevanceBoost = "CASE WHEN forum_id IN ({$forumPlaceholders}) THEN 30 ELSE 0 END"
            .' + CASE WHEN EXISTS ('
            .'SELECT 1 FROM thread_views'
            .' WHERE thread_views.thread_id = threads.id'
            .' AND thread_views.user_id = ?'
            .') THEN 15 ELSE 0 END';

        match ((string) $request->query('sort', 'trending')) {
            'newest' => $query
                ->orderByRaw("({$relevanceBoost}) DESC", $bindings)
                ->latest(),
            'top' => $query
                ->orderByRaw("(upvotes + ({$relevanceBoost})) DESC", $bindings)
                ->latest(),
            'discussed' => $query
                ->orderByRaw("(comments_count + ({$relevanceBoost})) DESC", $bindings)
                ->latest(),
            default => $query
                ->orderByRaw("(upvotes + ({$relevanceBoost})) DESC", $bindings)
                ->orderByDesc('comments_count')
                ->latest(),
        };
    }
}
