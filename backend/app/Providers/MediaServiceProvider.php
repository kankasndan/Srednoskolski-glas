<?php

namespace App\Providers;

use App\Contracts\ContentModerator;
use App\Contracts\MediaStorage;
use App\Services\Media\MediaManager;
use App\Services\Moderation\GeminiModerator;
use App\Services\Moderation\VideoFrameSampler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MediaManager::class, fn (Application $app): MediaManager => new MediaManager($app));

        $this->app->singleton(MediaStorage::class, fn (Application $app): MediaStorage => $app->make(MediaManager::class)->driver());

        $this->app->singleton(ContentModerator::class, function (Application $app): ContentModerator {
            $driver = (string) $app['config']->get('moderation.driver', 'gemini');

            return match ($driver) {
                'gemini' => new GeminiModerator(
                    $app['config']->get('moderation.drivers.gemini', []),
                    $app->make(VideoFrameSampler::class),
                ),
                default => throw new InvalidArgumentException("Unsupported content moderation driver [{$driver}]."),
            };
        });
    }
}
