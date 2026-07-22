<?php

namespace App\Providers;

use App\Contracts\MediaStorage;
use App\Services\Media\MediaManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MediaManager::class, fn (Application $app): MediaManager => new MediaManager($app));

        $this->app->singleton(MediaStorage::class, fn (Application $app): MediaStorage => $app->make(MediaManager::class)->driver());
    }
}
