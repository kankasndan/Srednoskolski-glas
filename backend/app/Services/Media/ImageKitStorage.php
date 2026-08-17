<?php

namespace App\Services\Media;

use App\Contracts\MediaStorage;
use App\Support\Media\ResolvesMediaType;
use App\Support\Media\StoredMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * ImageKit.io storage driver.
 *
 * Talks to the ImageKit REST API directly (no SDK dependency) using HTTP
 * Basic Auth with the private key as the username.
 *
 * @see https://imagekit.io/docs/api-reference/upload-file/upload-file
 */
class ImageKitStorage implements MediaStorage
{
    use ResolvesMediaType;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private readonly array $config)
    {
        foreach (['public_key', 'private_key', 'url_endpoint'] as $key) {
            if (empty($this->config[$key])) {
                throw new RuntimeException("Missing ImageKit configuration value [{$key}]. Set it in config/media.php or your .env file.");
            }
        }
    }

    public function upload(UploadedFile $file, ?string $directory = null, array $options = []): StoredMedia
    {
        $fileName = $options['file_name'] ?? $this->safeFileName($file);

        $payload = [
            'fileName' => $fileName,
            'useUniqueFileName' => ($options['use_unique_file_name'] ?? $this->config['use_unique_file_name'] ?? true) ? 'true' : 'false',
        ];

        if ($directory !== null && $directory !== '') {
            $payload['folder'] = $directory;
        }

        if (! empty($options['tags'])) {
            $payload['tags'] = is_array($options['tags']) ? implode(',', $options['tags']) : $options['tags'];
        }

        if (! $file->isValid()) {
            throw new RuntimeException($file->getErrorMessage());
        }

        $response = Http::withBasicAuth($this->config['private_key'], '')
            ->attach('file', $file->getContent(), $fileName)
            ->post($this->config['upload_endpoint'], $payload);

        if ($response->failed()) {
            throw new RuntimeException('ImageKit upload failed: '.$response->body());
        }

        /** @var array<string, mixed> $data */
        $data = $response->json();

        return new StoredMedia(
            provider: 'imagekit',
            id: (string) $data['fileId'],
            path: (string) $data['filePath'],
            url: (string) $data['url'],
            name: (string) $data['name'],
            type: $this->resolveMediaType($file->getMimeType()),
            size: $data['size'] ?? $file->getSize(),
            mimeType: $file->getMimeType(),
            metadata: $data,
        );
    }

    /**
     * Keeps the uploader's name recognisable but never trusts it: the extension
     * comes from the sniffed MIME type (a "photo.html" would otherwise be served
     * as HTML from the media host) and odd characters are collapsed.
     */
    private function safeFileName(UploadedFile $file): string
    {
        $extension = (string) ($file->extension() ?: '');

        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $base = preg_replace('/[^\p{L}\p{N}_-]+/u', '-', $base) ?? '';
        $base = trim(mb_substr($base, 0, 60), '-_');

        if ($base === '') {
            $base = Str::uuid()->toString();
        }

        return $extension !== '' ? "{$base}.{$extension}" : $base;
    }

    public function delete(StoredMedia|string $media): bool
    {
        $fileId = $media instanceof StoredMedia ? $media->id : $media;

        $response = Http::withBasicAuth($this->config['private_key'], '')
            ->delete(rtrim($this->config['api_endpoint'], '/')."/files/{$fileId}");

        return $response->successful();
    }

    public function url(string $path, array $options = []): string
    {
        $url = rtrim($this->config['url_endpoint'], '/').'/'.ltrim($path, '/');

        if (! empty($options['transformations']) && is_array($options['transformations'])) {
            $transformation = collect($options['transformations'])
                ->map(fn (mixed $value, string $key): string => "{$key}-{$value}")
                ->implode(',');

            $url .= '?tr='.$transformation;
        }

        return $url;
    }
}
