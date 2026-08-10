<?php

namespace App\Services\Feed;

use App\Models\Comment;
use App\Models\FeedHide;
use App\Models\Report;
use App\Models\Thread;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Preloaded personalization signals for one feed request.
 */
final class UserFeedContext
{
    /**
     * @param  Collection<int, int>  $followedForumIds
     * @param  Collection<int, int>  $engagedForumIds
     * @param  Collection<int, int>  $preferredAuthorIds
     * @param  Collection<int, int>  $followingUserIds
     * @param  Collection<int, int>  $viewedThreadIds
     * @param  Collection<int, int>  $hiddenThreadIds
     * @param  Collection<int, int>  $reportedThreadIds
     */
    public function __construct(
        public readonly Collection $followedForumIds,
        public readonly ?int $schoolForumId,
        public readonly Collection $engagedForumIds,
        public readonly Collection $preferredAuthorIds,
        public readonly Collection $followingUserIds,
        public readonly Collection $viewedThreadIds,
        public readonly Collection $hiddenThreadIds,
        public readonly Collection $reportedThreadIds,
    ) {}

    public static function guest(): self
    {
        return new self(
            followedForumIds: collect(),
            schoolForumId: null,
            engagedForumIds: collect(),
            preferredAuthorIds: collect(),
            followingUserIds: collect(),
            viewedThreadIds: collect(),
            hiddenThreadIds: collect(),
            reportedThreadIds: collect(),
        );
    }

    public static function forUser(User $user): self
    {
        $followedForumIds = $user->forums()->pluck('forums.id')->map(fn ($id) => (int) $id)->values();

        $schoolForumId = $user->studentData()
            ->with('school.forum')
            ->first()
            ?->school
            ?->forum
            ?->id;
        $schoolForumId = $schoolForumId !== null ? (int) $schoolForumId : null;

        $since = now()->subDays(30);
        $threadClass = Thread::class;

        $votedThreadIds = Vote::query()
            ->where('user_id', $user->id)
            ->where('votable_type', $threadClass)
            ->where('created_at', '>=', $since)
            ->pluck('votable_id');

        $commentedThreadIds = Comment::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $since)
            ->pluck('thread_id');

        $engagedThreadIds = $votedThreadIds->merge($commentedThreadIds)->unique()->values();

        $engagedForumIds = $engagedThreadIds->isEmpty()
            ? collect()
            : Thread::query()
                ->whereIn('id', $engagedThreadIds)
                ->pluck('forum_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

        $preferredAuthorIds = collect();
        if ($votedThreadIds->isNotEmpty()) {
            $preferredAuthorIds = Thread::query()
                ->whereIn('id', $votedThreadIds)
                ->where('user_id', '!=', $user->id)
                ->select('user_id', DB::raw('count(*) as vote_count'))
                ->groupBy('user_id')
                ->havingRaw('count(*) >= 2')
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->values();
        }

        $followingUserIds = $user->following()->pluck('users.id')->map(fn ($id) => (int) $id)->values();

        $viewedThreadIds = $user->threadViews()->pluck('thread_id')->map(fn ($id) => (int) $id)->values();

        $hiddenThreadIds = FeedHide::query()
            ->where('user_id', $user->id)
            ->pluck('thread_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $reportedThreadIds = Report::query()
            ->where('reporter_id', $user->id)
            ->where('reportable_type', $threadClass)
            ->pluck('reportable_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        return new self(
            followedForumIds: $followedForumIds,
            schoolForumId: $schoolForumId,
            engagedForumIds: $engagedForumIds,
            preferredAuthorIds: $preferredAuthorIds,
            followingUserIds: $followingUserIds,
            viewedThreadIds: $viewedThreadIds,
            hiddenThreadIds: $hiddenThreadIds,
            reportedThreadIds: $reportedThreadIds,
        );
    }

    /**
     * Forums treated as the user's "home" bucket (follows + school cold-start).
     *
     * @return Collection<int, int>
     */
    public function homeForumIds(): Collection
    {
        $ids = $this->followedForumIds->values();

        if ($this->schoolForumId !== null) {
            $ids = $ids->push($this->schoolForumId)->unique()->values();
        }

        return $ids;
    }

    /**
     * @return Collection<int, int>
     */
    public function excludedThreadIds(): Collection
    {
        return $this->hiddenThreadIds
            ->merge($this->reportedThreadIds)
            ->unique()
            ->values();
    }
}
