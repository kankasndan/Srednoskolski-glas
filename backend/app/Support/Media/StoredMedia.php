<?php

namespace App\Support\Media;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * Provider-agnostic representation of a stored file.
 *
 * Every media driver returns this shape, so application and database code
 * never has to care which backend (ImageKit, S3, ...) actually stored it.
 *
 * @implements Arrayable<string, mixed>
 */
class StoredMedia implements Arrayable, JsonSerializable
{
    /**
     * @param  string  $provider  The driver that stored the file ("imagekit", "s3").
     * @param  string  $id  Provider identifier used for deletion (ImageKit fileId, S3 key).
     * @param  string  $path  Logical path/key of the file within the provider.
     * @param  string  $url  Publicly accessible URL of the file.
     * @param  string  $name  Stored file name.
     * @param  'image'|'video'|'file'  $type  Coarse media category derived from the mime type.
     * @param  array<string, mixed>  $metadata  Raw provider response for debugging/extension.
     */
    public function __construct(
        public readonly string $provider,
        public readonly string $id,
        public readonly string $path,
        public readonly string $url,
        public readonly string $name,
        public readonly string $type = 'file',
        public readonly ?int $size = null,
        public readonly ?string $mimeType = null,
        public readonly array $metadata = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'id' => $this->id,
            'path' => $this->path,
            'url' => $this->url,
            'name' => $this->name,
            'type' => $this->type,
            'size' => $this->size,
            'mime_type' => $this->mimeType,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
