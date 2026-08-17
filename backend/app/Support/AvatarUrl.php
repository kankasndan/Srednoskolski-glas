<?php

namespace App\Support;

use App\Models\MediaUpload;
use App\Models\User;

final class AvatarUrl
{
    /**
     * Default local assets, the user's current photo, an ImageKit-generated
     * image, or a file this user uploaded themselves.
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

        if (self::isGeneratedAvatarUrl($url)) {
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
     * ImageKit builds these from a prompt in the URL itself, so there is no
     * upload row to own. Being on the media host is not enough on its own:
     * otherwise any avatar or attachment URL could be worn by any user.
     */
    public static function isGeneratedAvatarUrl(string $url): bool
    {
        return self::isConfiguredMediaUrl($url)
            && str_contains($url, '/ik-genimg-');
    }

    /**
     * True when the URL is hosted on the configured ImageKit endpoint or S3
     * public URL.
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
