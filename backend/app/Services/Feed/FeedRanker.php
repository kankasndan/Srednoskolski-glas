<?php

namespace App\Services\Feed;

use App\Models\Thread;
use Illuminate\Support\Collection;

/**
 * Scores threads (hot + affinity + seen demotion) and builds a mixed, diversified feed order.
 */
final class FeedRanker
{
    public const GRAVITY = 1.8;

    public const SEEN_MULTIPLIER = 0.18;

    /** Max threads pulled into the trending scorer. */
    public const CANDIDATE_LIMIT = 250;

    /** Trending ignores older posts even when time=all (keeps the scorer cheap). */
    public const CANDIDATE_MAX_AGE_DAYS = 30;

    /** Slot pattern per 5 items ≈ 60% home, 20% discovery, 20% fresh. */
    private const MIX_PATTERN = ['home', 'home', 'home', 'discovery', 'fresh'];

    public function hotScore(Thread $thread): float
    {
        $upvotes = max(0, (int) $thread->upvotes);
        $comments = max(0, (int) ($thread->comments_count ?? 0));
        $recentVotes = max(0, (int) ($thread->recent_votes_count ?? 0));
        $recentComments = max(0, (int) ($thread->recent_comments_count ?? 0));

        // Lifetime engagement + stronger weight on last-24h velocity.
        $points = $upvotes + (2 * $comments) + (4 * $recentVotes) + (3 * $recentComments);
        $ageHours = max(0.0, (float) $thread->created_at?->diffInMinutes(now()) / 60);

        return ($points + 1) / ((float) pow($ageHours + 2, self::GRAVITY));
    }

    public function affinityScore(Thread $thread, UserFeedContext $context): float
    {
        $affinity = 0.0;
        $forumId = (int) $thread->forum_id;
        $authorId = (int) $thread->user_id;

        if ($context->followedForumIds->contains($forumId)) {
            $affinity += 0.60;
        }

        if ($context->schoolForumId !== null && $context->schoolForumId === $forumId) {
            $affinity += 0.40;
        }

        if ($context->engagedForumIds->contains($forumId)) {
            $affinity += 0.30;
        }

        if ($context->preferredAuthorIds->contains($authorId)) {
            $affinity += 0.20;
        }

        if ($context->followingUserIds->contains($authorId)) {
            $affinity += 0.25;
        }

        return $affinity;
    }

    public function finalScore(Thread $thread, UserFeedContext $context): float
    {
        $hot = $this->hotScore($thread);
        $affinity = $this->affinityScore($thread, $context);
        $seen = $context->viewedThreadIds->contains((int) $thread->id)
            ? self::SEEN_MULTIPLIER
            : 1.0;

        return $hot * (1.0 + $affinity) * $seen;
    }

    /**
     * Attach feed_score and return threads sorted best-first.
     *
     * @param  Collection<int, Thread>  $threads
     * @return Collection<int, Thread>
     */
    public function scoreThreads(Collection $threads, UserFeedContext $context): Collection
    {
        return $threads
            ->map(function (Thread $thread) use ($context): Thread {
                $thread->setAttribute('feed_score', $this->finalScore($thread, $context));
                $thread->setAttribute('feed_hot', $this->hotScore($thread));
                $thread->setAttribute('feed_affinity', $this->affinityScore($thread, $context));

                return $thread;
            })
            ->sortByDesc(fn (Thread $thread) => (float) $thread->feed_score)
            ->values();
    }

    /**
     * Interleave home / discovery / fresh buckets, then diversify consecutive forums.
     *
     * @param  Collection<int, Thread>  $scored
     * @return Collection<int, Thread>
     */
    public function buildMixedOrder(Collection $scored, UserFeedContext $context): Collection
    {
        $homeForumIds = $context->homeForumIds();

        $byScore = $scored
            ->sortByDesc(fn (Thread $thread) => (float) $thread->feed_score)
            ->values();

        if ($homeForumIds->isEmpty()) {
            return $this->diversify($byScore);
        }

        /** @var list<Thread> $home */
        $home = $byScore
            ->filter(fn (Thread $thread) => $homeForumIds->contains((int) $thread->forum_id))
            ->values()
            ->all();

        /** @var list<Thread> $discovery */
        $discovery = $byScore
            ->reject(fn (Thread $thread) => $homeForumIds->contains((int) $thread->forum_id))
            ->values()
            ->all();

        /** @var list<Thread> $fresh */
        $fresh = $byScore
            ->filter(fn (Thread $thread) => $thread->created_at !== null && $thread->created_at->greaterThan(now()->subDay()))
            ->values()
            ->all();

        $queues = [
            'home' => $home,
            'discovery' => $discovery,
            'fresh' => $fresh,
            'any' => $byScore->all(),
        ];

        $used = [];
        $mixed = collect();
        $patternIndex = 0;
        $total = $byScore->count();

        while ($mixed->count() < $total) {
            $bucket = self::MIX_PATTERN[$patternIndex % count(self::MIX_PATTERN)];
            $patternIndex++;

            $picked = $this->takeNext($queues[$bucket], $used)
                ?? $this->takeNext($queues['home'], $used)
                ?? $this->takeNext($queues['discovery'], $used)
                ?? $this->takeNext($queues['fresh'], $used)
                ?? $this->takeNext($queues['any'], $used);

            if ($picked === null) {
                break;
            }

            $used[(int) $picked->id] = true;
            $mixed->push($picked);
        }

        return $this->diversify($mixed);
    }

    /**
     * Avoid 3+ consecutive threads from the same forum when alternatives exist.
     *
     * @param  Collection<int, Thread>  $ordered
     * @return Collection<int, Thread>
     */
    public function diversify(Collection $ordered): Collection
    {
        $pending = $ordered->values()->all();
        $result = collect();

        while ($pending !== []) {
            $pickIndex = null;

            foreach ($pending as $index => $candidate) {
                if ($result->count() < 2) {
                    $pickIndex = $index;
                    break;
                }

                $lastTwoForums = $result->slice(-2)->map(fn (Thread $thread) => (int) $thread->forum_id);
                $wouldBeThird = $lastTwoForums->every(
                    fn (int $forumId) => $forumId === (int) $candidate->forum_id,
                );

                if (! $wouldBeThird) {
                    $pickIndex = $index;
                    break;
                }
            }

            if ($pickIndex === null) {
                $pickIndex = 0;
            }

            $result->push($pending[$pickIndex]);
            array_splice($pending, $pickIndex, 1);
        }

        return $result->values();
    }

    /**
     * @param  list<Thread>  $queue
     * @param  array<int, bool>  $used
     */
    private function takeNext(array &$queue, array $used): ?Thread
    {
        foreach ($queue as $index => $thread) {
            $id = (int) $thread->id;
            if (! isset($used[$id])) {
                array_splice($queue, $index, 1);

                return $thread;
            }
        }

        return null;
    }
}
