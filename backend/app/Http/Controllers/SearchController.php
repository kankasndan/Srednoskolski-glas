<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Resources\ForumResource;
use App\Http\Resources\ThreadResource;
use App\Models\Forum;
use App\Models\Thread;
use App\Support\LikeEscape;
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
     * Relevance (default when `q` is set): earlier title matches first
     * (prefix of the title, then prefix of a later word, then substring),
     * then body, then comments.
     */
    public function index(Request $request): JsonResponse
    {
        if (! filled($request->input('q'))) {
            $request->merge(['q' => null]);
        }

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'min:2', 'max:200'],
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

        if ($user === null) {
            $query->listedForGuest();
        }

        if ($forum !== null) {
            $forum->abortUnlessReadableBy($user);
            $query->where('forum_id', $forum->id);
        }

        $patterns = $q !== '' ? $this->likePatterns($q) : null;
        $like = $patterns['contains'] ?? null;

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

        if ($patterns !== null && ($sort === 'relevance' || $sort === 'trending')) {
            $this->applyRelevanceOrder($query, 'title', 'description', $patterns, $q);
            $query->orderByDesc('upvotes')->latest();
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
                ->tap(fn (Builder $forums) => $this->applyRelevanceOrder($forums, 'name', 'description', $patterns, $q))
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

    /**
     * Prefix-of-title, then prefix-of-a-later-word, then substring, then the secondary field.
     *
     * @param  array{starts: string, word_start: string, contains: string}  $patterns
     */
    private function applyRelevanceOrder(
        Builder $query,
        string $primary,
        string $secondary,
        array $patterns,
        string $q,
    ): void {
        $query->orderByRaw(
            "CASE WHEN {$primary} LIKE ? THEN 0 WHEN {$primary} LIKE ? THEN 1 WHEN {$primary} LIKE ? THEN 2 WHEN {$secondary} LIKE ? THEN 3 ELSE 4 END",
            [$patterns['starts'], $patterns['word_start'], $patterns['contains'], $patterns['contains']],
        )->orderByRaw(
            'CASE WHEN INSTR(LOWER('.$primary.'), LOWER(?)) = 0 THEN 9999 ELSE INSTR(LOWER('.$primary.'), LOWER(?)) END',
            [$q, $q],
        );
    }

    /**
     * @return array{starts: string, word_start: string, contains: string}
     */
    private function likePatterns(string $q): array
    {
        $starts = LikeEscape::startsWith($q);

        return [
            'starts' => $starts,
            'word_start' => '% '.$starts,
            'contains' => LikeEscape::contains($q),
        ];
    }
}
