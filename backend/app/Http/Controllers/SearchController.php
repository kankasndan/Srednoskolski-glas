<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Resources\ForumResource;
use App\Http\Resources\ThreadResource;
use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use FiltersThreads;

    /**
     * Site-wide (or forum-scoped) search for threads + matching forum cards.
     *
     * Query: q, forum (slug), page, per_page (1–20, default 5),
     *        sort (relevance|trending|top|newest|discussed),
     *        time (day|week|month|six-months|year|all)
     *
     * Empty `q` returns a trending/explore list (same filters as the feed).
     * Relevance (default when `q` is set): title matches first, then body, then comments.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'forum' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:relevance,trending,top,newest,discussed'],
            'time' => ['nullable', 'in:day,week,month,six-months,year,all'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $forumSlug = $validated['forum'] ?? null;
        $user = $request->user('web') ?? $request->user();

        $forum = null;
        if (is_string($forumSlug) && $forumSlug !== '') {
            $forum = Forum::query()->where('slug', $forumSlug)->firstOrFail();
        }

        $query = Thread::query()
            ->with($this->threadListWith($user))
            ->withCount('comments');

        $this->applyHasVoted($query, $user);

        if ($forum !== null) {
            $query->where('forum_id', $forum->id);
        }

        $like = $q !== '' ? $this->likePattern($q) : null;

        if ($like !== null) {
            $query->where(function (Builder $inner) use ($like) {
                $inner->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas(
                        'comments',
                        fn (Builder $comments) => $comments->where('content', 'like', $like),
                    );
            });
        }

        if ($since = $this->threadTimeWindow($request->query('time'))) {
            $query->where('created_at', '>=', $since);
        }

        $sort = (string) ($validated['sort'] ?? ($like !== null ? 'relevance' : 'trending'));
        if ($sort === 'trending' && $like !== null) {
            $sort = 'relevance';
        }

        if ($like !== null && ($sort === 'relevance' || $sort === 'trending')) {
            $query->orderByRaw(
                'CASE WHEN title LIKE ? THEN 0 WHEN description LIKE ? THEN 1 ELSE 2 END',
                [$like, $like],
            )->orderByDesc('upvotes')->latest();
        } else {
            $this->applyThreadSort($query, $sort);
        }

        $perPage = $validated['per_page'] ?? $this->threadsPerPage();
        $threads = $query->paginate($perPage)->withQueryString();

        $forumHits = collect();
        if ($like !== null && $forum === null) {
            $forumHits = Forum::query()
                ->where(function (Builder $inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like);
                })
                ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$like])
                ->orderBy('name')
                ->limit(3)
                ->get();
        }

        return ThreadResource::collection($threads)
            ->additional([
                'forums' => $forumHits
                    ->map(fn (Forum $hit) => (new ForumResource($hit))->card()->resolve())
                    ->values()
                    ->all(),
            ])
            ->response();
    }

    private function likePattern(string $q): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);

        return '%'.$escaped.'%';
    }
}
