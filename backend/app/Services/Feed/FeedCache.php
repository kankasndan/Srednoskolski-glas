<?php

namespace App\Services\Feed;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Short-TTL cache of ranked feed thread IDs (not full thread payloads).
 */
final class FeedCache
{
    public const TTL_SECONDS = 45;

    public static function key(?User $user, string $sort, ?string $time): string
    {
        $who = $user instanceof User ? 'u'.$user->id : 'guest';
        $timeKey = $time !== null && $time !== '' ? $time : 'all';

        return "feed:ranked:v1:{$who}:{$sort}:{$timeKey}";
    }

    /**
     * @param  list<int>  $orderedIds
     */
    public static function put(?User $user, string $sort, ?string $time, array $orderedIds): void
    {
        Cache::put(
            self::key($user, $sort, $time),
            [
                'ids' => array_values(array_map('intval', $orderedIds)),
            ],
            self::TTL_SECONDS,
        );
    }

    /**
     * @return list<int>|null
     */
    public static function get(?User $user, string $sort, ?string $time): ?array
    {
        $payload = Cache::get(self::key($user, $sort, $time));

        if (! is_array($payload) || ! isset($payload['ids']) || ! is_array($payload['ids'])) {
            return null;
        }

        return array_values(array_map('intval', $payload['ids']));
    }

    public static function forgetForUser(User $user): void
    {
        // Forget common sort/time combos the SPA uses. Misses are cheap; TTL also covers the rest.
        $sorts = ['trending', 'top', 'newest', 'discussed'];
        $times = ['all', 'day', 'week', 'month', 'six-months', 'year'];

        foreach ($sorts as $sort) {
            foreach ($times as $time) {
                Cache::forget(self::key($user, $sort, $time));
            }
        }
    }
}
