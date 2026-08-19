<?php

namespace App\Services\Media;

use App\Contracts\ContentModerator;
use App\Contracts\MediaStorage;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Manager;

/**
 * Resolves and caches media storage drivers.
 *
 * New providers can be added by defining a `create{Name}Driver` method and
 * a matching entry under `config/media.php` -> `drivers`.
 *
 * @method MediaStorage driver(string|null $driver = null)
 */
class MediaManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('media.default', 'imagekit');
    }

    protected function createImagekitDriver(): MediaStorage
    {
        return $this->withModeration(new ImageKitStorage(
            $this->config->get('media.drivers.imagekit', []),
        ));
    }

    protected function createS3Driver(): MediaStorage
    {
        return $this->withModeration(new S3Storage(
            $this->container->make(FilesystemFactory::class),
            $this->config->get('media.drivers.s3', []),
        ));
    }

    /**
     * Every driver is wrapped so ImageKit and S3 both refuse disallowed files
     * before a public object is created.
     */
    private function withModeration(MediaStorage $driver): MediaStorage
    {
        return new ModeratedMediaStorage(
            $driver,
            fn (): ContentModerator => $this->container->make(ContentModerator::class),
        );
    }
}
