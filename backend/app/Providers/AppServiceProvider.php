<?php

namespace App\Providers;

use App\Models\Appeal;
use App\Models\Report;
use App\Models\Sanction;
use App\Observers\AppealObserver;
use App\Observers\ReportObserver;
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
        Report::observe(ReportObserver::class);
        Sanction::observe(SanctionObserver::class);
        Appeal::observe(AppealObserver::class);
    }
}
