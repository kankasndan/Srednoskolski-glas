<?php

namespace App\Support;

use App\Models\MediaUpload;
use App\Models\User;

final class AvatarUrl
{
    /**
     * Default local assets, the user's current photo, ImageKit/S3 media
     * (uploads and generated images), or a file this user uploaded.
     */
    public static function isAllowed(string $url, ?User $user): bool
    {
        if (in_array($url, config('avatars.defaults', []), true)) {
            return true;
        }

        $current = $user?->imageUrl;
        if (is_string($current) && $current !== '' && hash_equals($current, $url)) {
            return true;
        }

        if (self::isConfiguredMediaUrl($url)) {
            return true;
        }

        $userId = $user?->id;
        if ($userId === null) {
            return false;
        }

        return MediaUpload::query()
            ->where('user_id', $userId)
            ->where('url', $url)
            ->exists();
    }

    /**
     * True when the URL is hosted on the configured ImageKit endpoint or S3
     * public URL. AI-generated avatars live on ImageKit without a MediaUpload row.
     */
    public static function isConfiguredMediaUrl(string $url): bool
    {
        return self::matchesEndpoint($url, config('media.drivers.imagekit.url_endpoint'))
            || self::matchesEndpoint($url, config('filesystems.disks.s3.url'));
    }

    private static function matchesEndpoint(string $url, mixed $endpoint): bool
    {
        $endpoint = rtrim((string) $endpoint, '/');
        if ($endpoint === '') {
            return false;
        }

        $urlParts = parse_url($url);
        $endpointParts = parse_url($endpoint);

        if (! is_array($urlParts) || ! is_array($endpointParts)) {
            return false;
        }

        if (strtolower((string) ($urlParts['scheme'] ?? '')) !== 'https') {
            return false;
        }

        $urlHost = strtolower((string) ($urlParts['host'] ?? ''));
        $endpointHost = strtolower((string) ($endpointParts['host'] ?? ''));

        if ($urlHost === '' || $urlHost !== $endpointHost) {
            return false;
        }

        $endpointPath = rtrim((string) ($endpointParts['path'] ?? ''), '/');
        $urlPath = rawurldecode((string) ($urlParts['path'] ?? ''));

        if ($endpointPath === '') {
            return $urlPath !== '' && $urlPath !== '/';
        }

        return $urlPath === $endpointPath
            || str_starts_with($urlPath, $endpointPath.'/');
    }
}
