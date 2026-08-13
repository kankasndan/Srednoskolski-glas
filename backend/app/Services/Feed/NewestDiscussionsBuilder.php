<?php

namespace App\Services\Feed;

use App\Http\Controllers\Concerns\FiltersThreads;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

/**
 * Builds the /newest page: newest threads from general forums + followed schools,
 * with a 2:1 focus mix toward forums the user follows.
 */
final class NewestDiscussionsBuilder
{
    use FiltersThreads;

    /** ~2 focused (followed) : 1 other general. */
    private const MIX_PATTERN = ['focused', 'focused', 'other'];

    private const CANDIDATE_LIMIT = 250;

    public function paginate(Request $request, ?User $user): LengthAwarePaginator
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = $this->threadsPerPage();

        $context = $user instanceof User
            ? UserFeedContext::forUser($user)
            : UserFeedContext::guest();

        $eligibleForumIds = $this->eligibleForumIds($context->followedForumIds);
        $focusedForumIds = $context->followedForumIds
            ->intersect($eligibleForumIds)
            ->values();

        if ($eligibleForumIds->isEmpty()) {
            return $this->emptyPage($request, $page, $perPage);
        }

        $candidates = $this->loadCandidates($request, $context, $eligibleForumIds);

        $orderedIds = $focusedForumIds->isEmpty()
            ? $candidates->map(fn (Thread $thread) => (int) $thread->id)->all()
            : $this->mixFocusedAndOther($candidates, $focusedForumIds);

        return $this->paginateHydratedIds($orderedIds, $page, $perPage, $request, $user);
    }

    /**
     * All general forums + school forums the user follows.
     *
     * @param  Collection<int, int>  $followedForumIds
     * @return Collection<int, int>
     */
    private function eligibleForumIds(Collection $followedForumIds): Collection
    {
        $generalIds = Forum::query()
            ->where('type', 'general')
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        $followedSchoolIds = $followedForumIds->isEmpty()
            ? collect()
            : Forum::query()
                ->where('type', 'school')
                ->whereIn('id', $followedForumIds->all())
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

        return $generalIds
            ->merge($followedSchoolIds)
            ->unique()
            ->values();
    }

    /**
     * @param  Collection<int, int>  $eligibleForumIds
     * @return Collection<int, Thread>
     */
    private function loadCandidates(
        Request $request,
        UserFeedContext $context,
        Collection $eligibleForumIds,
    ): Collection {
        $query = Thread::query()
            ->select(['id', 'forum_id', 'created_at'])
            ->whereIn('forum_id', $eligibleForumIds->all());

        if ($since = $this->threadTimeWindow($request->query('time'))) {
            $query->where('created_at', '>=', $since);
        }

        $excluded = $context->excludedThreadIds();
        if ($excluded->isNotEmpty()) {
            $query->whereNotIn('id', $excluded->all());
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::CANDIDATE_LIMIT)
            ->get();
    }

    /**
     * Interleave focused (followed) and other eligible threads ~2:1, newest within each pool.
     *
     * @param  Collection<int, Thread>  $candidates
     * @param  Collection<int, int>  $focusedForumIds
     * @return list<int>
     */
    private function mixFocusedAndOther(Collection $candidates, Collection $focusedForumIds): array
    {
        $focused = $candidates
            ->filter(fn (Thread $thread) => $focusedForumIds->contains((int) $thread->forum_id))
            ->values();

        $other = $candidates
            ->reject(fn (Thread $thread) => $focusedForumIds->contains((int) $thread->forum_id))
            ->values();

        $focusedIndex = 0;
        $otherIndex = 0;
        $patternIndex = 0;
        $ordered = [];
        $total = $candidates->count();

        while (count($ordered) < $total) {
            $bucket = self::MIX_PATTERN[$patternIndex % count(self::MIX_PATTERN)];
            $patternIndex++;

            if ($bucket === 'focused' && $focusedIndex < $focused->count()) {
                $ordered[] = (int) $focused[$focusedIndex]->id;
                $focusedIndex++;

                continue;
            }

            if ($bucket === 'other' && $otherIndex < $other->count()) {
                $ordered[] = (int) $other[$otherIndex]->id;
                $otherIndex++;

                continue;
            }

            if ($focusedIndex < $focused->count()) {
                $ordered[] = (int) $focused[$focusedIndex]->id;
                $focusedIndex++;

                continue;
            }

            if ($otherIndex < $other->count()) {
                $ordered[] = (int) $other[$otherIndex]->id;
                $otherIndex++;

                continue;
            }

            break;
        }

        return $ordered;
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

    private function emptyPage(Request $request, int $page, int $perPage): LengthAwarePaginator
    {
        return (new Paginator(
            collect(),
            0,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        ))->withQueryString();
    }
}
