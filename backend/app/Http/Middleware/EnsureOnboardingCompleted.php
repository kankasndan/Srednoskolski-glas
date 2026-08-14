<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    public const MESSAGE = 'Заврши го onboarding процесот за да можеш да ја извршиш оваа акција.';

    /**
     * Block community write actions until the user finishes onboarding.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->hasCompletedOnboarding()) {
            return response()->json([
                'message' => self::MESSAGE,
            ], 403);
        }

        return $next($request);
    }
}
