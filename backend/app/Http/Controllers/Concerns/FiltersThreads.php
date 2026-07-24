<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Shared sort + time-window filters for thread list endpoints.
 *
 * Query params:
 * - sort: trending | top | newest | discussed  (default: trending)
 * - time: day | week | month | six-months | year | all  (default: all)
 */
trait FiltersThreads
{
    protected function applyThreadFilters(Builder $query, Request $request): void
    {
        if ($since = $this->threadTimeWindow($request->query('time'))) {
            $query->where('created_at', '>=', $since);
        }

        $this->applyThreadSort($query, (string) $request->query('sort', 'trending'));
    }

    protected function applyThreadSort(Builder $query, string $sort): void
    {
        match ($sort) {
            'newest' => $query->latest(),
            'top' => $query->orderByDesc('upvotes')->latest(),
            'discussed' => $query->orderByDesc('comments_count')->latest(),
            // trending: mix of engagement (upvotes + discussion), then newest
            default => $query->orderByDesc('upvotes')->orderByDesc('comments_count')->latest(),
        };
    }

    protected function threadTimeWindow(?string $time): ?Carbon
    {
        return match ($time) {
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'six-months' => now()->subMonths(6),
            'year' => now()->subYear(),
            default => null, // "all" or missing → no time filter
        };
    }

    /**
     * Default page size for infinite-scroll thread lists.
     */
    protected function threadsPerPage(): int
    {
        return 5;
    }

    /**
     * Eager-load whether the current user has upvoted each thread/comment row.
     */
    protected function applyHasVoted(Builder $query, mixed $user): void
    {
        if ($user === null) {
            return;
        }

        $query->withExists([
            'votes as has_voted' => fn ($votes) => $votes->where('user_id', $user->id),
        ]);
    }
}
