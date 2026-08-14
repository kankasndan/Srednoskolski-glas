<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if (app()->isProduction() && ($request->is('admin') || $request->is('admin/*'))) {
            $response->headers->set('Content-Security-Policy', $this->adminCsp());
        } else {
            $response->headers->set(
                'Content-Security-Policy',
                "frame-ancestors 'none'; object-src 'none'; base-uri 'self'",
            );
        }

        if (config('app.env') === 'production') {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains',
            );
        }

        return $response;
    }

    private function adminCsp(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://kit.fontawesome.com https://ka-f.fontawesome.com https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://ka-f.fontawesome.com https://fonts.bunny.net",
            "img-src 'self' data: https:",
            "font-src 'self' data: https://ka-f.fontawesome.com https://fonts.bunny.net",
            "connect-src 'self' https://ka-f.fontawesome.com https://kit.fontawesome.com",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
    }
}
