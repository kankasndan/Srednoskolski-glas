<?php

namespace App\Facades;

use App\Contracts\MediaStorage;
use App\Services\Media\MediaManager;
use App\Support\Media\StoredMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Facade;

/**
 * @method static StoredMedia upload(UploadedFile $file, string|null $directory = null, array $options = [])
 * @method static bool delete(StoredMedia|string $media)
 * @method static string url(string $path, array $options = [])
 * @method static MediaStorage driver(string|null $driver = null)
 *
 * @see MediaManager
 */
class Media extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MediaManager::class;
    }
}
