<?php

namespace App\Providers;

use App\Models\Sanction;
use App\Observers\SanctionObserver;
use App\View\Composers\AdminLayoutComposer;
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
        View::composer('layouts.master', AdminLayoutComposer::class);
        Sanction::observe(SanctionObserver::class);
    }
}
