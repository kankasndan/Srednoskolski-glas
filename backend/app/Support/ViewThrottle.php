<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * One public view increment per visitor per resource inside the window.
 */
final class ViewThrottle
{
    public const TTL_SECONDS = 1800;

    public static function shouldIncrement(Request $request, string $resource, int|string $id): bool
    {
        $who = $request->user()?->getAuthIdentifier() ?? $request->ip() ?? 'guest';
        $key = 'view:'.$resource.':'.$id.':'.sha1((string) $who);

        return Cache::add($key, true, self::TTL_SECONDS);
    }
}
