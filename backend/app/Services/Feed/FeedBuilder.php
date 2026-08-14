<?php

namespace App\Services\Feed;

use App\Http\Controllers\Concerns\FiltersThreads;
use App\Models\FeedHide;
use App\Models\Report;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds a page of the home feed.
 *
 * Trending path:
 * 1) Cache lookup for ordered thread IDs (short TTL)
 * 2) On miss: lean candidate query → score → mix → diversify → store IDs
 * 3) Slice page IDs → hydrate only those rows with full relations
 */
final class FeedBuilder
{
    use FiltersThreads;

    public function __construct(
        private readonly FeedRanker $ranker = new FeedRanker,
    ) {}

    public function paginate(Request $request, ?User $user): LengthAwarePaginator
    {
        $sort = (string) $request->query('sort', 'trending');
        $time = $request->query('time');
        $timeKey = is_string($time) && $time !== '' ? $time : 'all';
        $page = max(1, (int) $request->query('page', 1));
        $perPage = $this->threadsPerPage();

        // Personalized pipeline is for the default "trending" sort.
        // Other sorts stay predictable (newest/top/discussed) but still honor hides/reports.
        if ($sort !== 'trending') {
            $context = $user instanceof User
                ? UserFeedContext::forUser($user)
                : UserFeedContext::guest();

            return $this->paginateSimpleSort($request, $user, $context, $sort, $page, $perPage);
        }

        $orderedIds = FeedCache::get($user, $sort, $timeKey);

        if ($orderedIds === null) {
            $context = $user instanceof User
                ? UserFeedContext::forUser($user)
                : UserFeedContext::guest();

            $candidates = $this->loadLeanCandidates($request, $context, $user);
            $scored = $this->ranker->scoreThreads($candidates, $context);
            $ordered = $this->ranker->buildMixedOrder($scored, $context);
            $orderedIds = $ordered->map(fn (Thread $thread) => (int) $thread->id)->all();

            FeedCache::put($user, $sort, $timeKey, $orderedIds);
        }

        // Belt-and-suspenders: drop anything hidden/reported after the list was cached.
        $orderedIds = $this->filterExcludedIds($orderedIds, $user);

        return $this->paginateHydratedIds($orderedIds, $page, $perPage, $request, $user);
    }

    /**
     * Lean rows for scoring only — no author/attachments/poll eager-loads.
     *
     * @return Collection<int, Thread>
     */
    private function loadLeanCandidates(Request $request, UserFeedContext $context, ?User $user): Collection
    {
        $since = $this->effectiveCandidateSince($request->query('time'));
        $recentSince = now()->subDay();

        $query = Thread::query()
            ->select([
                'id',
                'title',
                'upvotes',
                'views',
                'user_id',
                'forum_id',
                'is_anonymous',
                'created_at',
                'edited_at',
            ])
            ->withCount('comments')
            ->withCount([
                'votes as recent_votes_count' => fn ($votes) => $votes->where('created_at', '>=', $recentSince),
                'comments as recent_comments_count' => fn ($comments) => $comments->where('created_at', '>=', $recentSince),
            ])
            ->where('created_at', '>=', $since);

        if ($user === null) {
            $query->listedForGuest();
        }

        $excluded = $context->excludedThreadIds();
        if ($excluded->isNotEmpty()) {
            $query->whereNotIn('id', $excluded->all());
        }

        return $query
            ->orderByDesc('created_at')
            ->limit(FeedRanker::CANDIDATE_LIMIT)
            ->get();
    }

    /**
     * Tighter window for trending candidates.
     * Explicit day/week/month filters still apply when tighter than the default cap.
     */
    private function effectiveCandidateSince(mixed $time): Carbon
    {
        $requested = $this->threadTimeWindow(is_string($time) ? $time : null);
        $cap = now()->subDays(FeedRanker::CANDIDATE_MAX_AGE_DAYS);

        if ($requested === null) {
            return $cap;
        }

        // Use the more restrictive (newer) lower bound.
        return $requested->greaterThan($cap) ? $requested : $cap;
    }

    /**
     * @param  list<int>  $orderedIds
     * @return list<int>
     */
    private function filterExcludedIds(array $orderedIds, ?User $user): array
    {
        if (! $user instanceof User || $orderedIds === []) {
            return $orderedIds;
        }

        $excluded = FeedHide::query()
            ->where('user_id', $user->id)
            ->pluck('thread_id')
            ->merge(
                Report::query()
                    ->where('reporter_id', $user->id)
                    ->where('reportable_type', Thread::class)
                    ->pluck('reportable_id'),
            )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        if ($excluded === []) {
            return $orderedIds;
        }

        $excludedLookup = array_fill_keys($excluded, true);

        return array_values(array_filter(
            $orderedIds,
            fn (int $id) => ! isset($excludedLookup[$id]),
        ));
    }

    /**
     * @param  list<int>  $orderedIds
     */
    private function paginateHydratedIds(
        array $orderedIds,
        int $page,
        int $perPage,
        Request $request,
        ?User $user,
    ): LengthAwarePaginator {
        $total = count($orderedIds);
        $offset = ($page - 1) * $perPage;
        $pageIds = array_slice($orderedIds, $offset, $perPage);
        $items = $this->hydrateThreads($pageIds, $user);

        return (new Paginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        ))->withQueryString();
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, Thread>
     */
    private function hydrateThreads(array $ids, ?User $user): Collection
    {
        if ($ids === []) {
            return collect();
        }

        $query = Thread::query()
            ->whereIn('id', $ids)
            ->with($this->threadListWith($user))
            ->withCount('comments');

        $this->applyHasVoted($query, $user);

        $byId = $query->get()->keyBy('id');

        return collect($ids)
            ->map(fn (int $id) => $byId->get($id))
            ->filter()
            ->values();
    }

    private function paginateSimpleSort(
        Request $request,
        ?User $user,
        UserFeedContext $context,
        string $sort,
        int $page,
        int $perPage,
    ): LengthAwarePaginator {
        $query = Thread::query()
            ->with($this->threadListWith($user))
            ->withCount('comments');

        $this->applyHasVoted($query, $user);

        if ($user === null) {
            $query->listedForGuest();
        }

        if ($since = $this->threadTimeWindow($request->query('time'))) {
            $query->where('created_at', '>=', $since);
        }

        $excluded = $context->excludedThreadIds();
        if ($excluded->isNotEmpty()) {
            $query->whereNotIn('id', $excluded->all());
        }

        $this->applyThreadSort($query, $sort);

        return $query->paginate($perPage)->withQueryString();
    }
}
