<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Thread;
use App\Policies\CommentPolicy;
use App\Policies\ThreadPolicy;
use App\View\Composers\AdminLayoutComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Thread::class, ThreadPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);

        View::composer('layouts.master', AdminLayoutComposer::class);

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by(
                strtolower((string) $request->input('email')).'|'.$request->ip(),
            );
        });

        RateLimiter::for('social-auth', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('api-writes', function (Request $request) {
            $userKey = optional($request->user())->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(60)->by((string) $userKey);
        });

        RateLimiter::for('media-upload', function (Request $request) {
            $userKey = optional($request->user())->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(10)->by((string) $userKey);
        });

        RateLimiter::for('thread-create', function (Request $request) {
            $userKey = optional($request->user())->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(10)->by((string) $userKey);
        });

        RateLimiter::for('comment-create', function (Request $request) {
            $userKey = optional($request->user())->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(30)->by((string) $userKey);
        });

        RateLimiter::for('api-reads', function (Request $request) {
            return Limit::perMinute(120)->by((string) $request->ip());
        });

        RateLimiter::for('api-search', function (Request $request) {
            return Limit::perMinute(30)->by((string) $request->ip());
        });
    }
}
