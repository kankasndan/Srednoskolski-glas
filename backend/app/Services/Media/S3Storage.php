<?php

namespace App\Services\Media;

use App\Contracts\MediaStorage;
use App\Support\Media\ResolvesMediaType;
use App\Support\Media\StoredMedia;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * S3 storage driver.
 *
 * Delegates to Laravel's native S3 filesystem disk, so it works with real
 * AWS S3 as well as any S3-compatible service. Kept behind the same
 * MediaStorage contract as ImageKit for a drop-in swap.
 */
class S3Storage implements MediaStorage
{
    use ResolvesMediaType;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly FilesystemFactory $filesystem,
        private readonly array $config,
    ) {}

    public function upload(UploadedFile $file, ?string $directory = null, array $options = []): StoredMedia
    {
        $directory = trim($directory ?? '', '/');
        $extension = $file->getClientOriginalExtension();
        $fileName = $options['file_name'] ?? Str::uuid()->toString().($extension !== '' ? ".{$extension}" : '');

        $path = $this->disk()->putFileAs($directory, $file, $fileName, [
            'visibility' => $options['visibility'] ?? $this->config['visibility'] ?? 'public',
        ]);

        return new StoredMedia(
            provider: 's3',
            id: $path,
            path: $path,
            url: $this->disk()->url($path),
            name: $fileName,
            type: $this->resolveMediaType($file->getMimeType()),
            size: $file->getSize(),
            mimeType: $file->getMimeType(),
        );
    }

    public function delete(StoredMedia|string $media): bool
    {
        $path = $media instanceof StoredMedia ? $media->path : $media;

        return $this->disk()->delete($path);
    }

    public function url(string $path, array $options = []): string
    {
        return $this->disk()->url($path);
    }

    private function disk(): Filesystem
    {
        return $this->filesystem->disk($this->config['disk'] ?? 's3');
    }
}
