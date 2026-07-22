<?php

namespace App\Support\Media;

trait ResolvesMediaType
{
    /**
     * Map a mime type to a coarse media category.
     *
     * @return 'image'|'video'|'file'
     */
    protected function resolveMediaType(?string $mimeType): string
    {
        return match (true) {
            $mimeType !== null && str_starts_with($mimeType, 'image/') => 'image',
            $mimeType !== null && str_starts_with($mimeType, 'video/') => 'video',
            default => 'file',
        };
    }
}
