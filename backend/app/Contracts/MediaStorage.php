<?php

namespace App\Contracts;

use App\Support\Media\StoredMedia;
use Illuminate\Http\UploadedFile;

/**
 * Contract every media storage provider must implement.
 *
 * Application code depends on this interface only, so the concrete backend
 * (ImageKit today, S3 tomorrow) can be swapped through configuration without
 * touching callers.
 */
interface MediaStorage
{
    /**
     * Upload a file and return its provider-agnostic representation.
     *
     * @param  string|null  $directory  Folder/key prefix; falls back to config when null.
     * @param  array<string, mixed>  $options  Driver specific options (file_name, tags, visibility, ...).
     */
    public function upload(UploadedFile $file, ?string $directory = null, array $options = []): StoredMedia;

    /**
     * Delete a previously stored file.
     *
     * Accepts either the full StoredMedia object or its provider identifier.
     */
    public function delete(StoredMedia|string $media): bool;

    /**
     * Build a public URL for a stored file path.
     *
     * @param  array<string, mixed>  $options  Driver specific options (e.g. transformations).
     */
    public function url(string $path, array $options = []): string;
}
