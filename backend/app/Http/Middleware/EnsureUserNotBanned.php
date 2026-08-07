<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserNotBanned
{
    /**
     * Block write actions for users with an active restrictive sanction.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->isBanned()) {
            return response()->json([
                'message' => 'Вашата сметка е суспендирана. Не можете да извршувате оваа акција.',
            ], 403);
        }

        return $next($request);
    }
}
