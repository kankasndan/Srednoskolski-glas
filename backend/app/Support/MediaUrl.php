<?php

namespace App\Support;

final class MediaUrl
{
    public static function resolve(?string $value): ?string
    {
        $value = self::normalize($value);

        if ($value === null) {
            return null;
        }

        if (self::isExternalUrl($value)) {
            return $value;
        }

        if (self::isStaticAssetPath($value)) {
            return $value;
        }

        $value = ltrim($value, '/');

        return url('/api/media/'.$value);
    }

    public static function storedPathFromUrl(?string $value): ?string
    {
        $value = self::normalize($value);

        if ($value === null) {
            return null;
        }

        if (str_starts_with($value, '/')) {
            return null;
        }

        if (! self::isExternalUrl($value)) {
            return $value;
        }

        $appHost = parse_url(rtrim((string) config('app.url'), '/'), PHP_URL_HOST);
        $urlParts = parse_url($value);

        if (! is_array($urlParts) || ! is_string($appHost) || $appHost === '') {
            return null;
        }

        if (($urlParts['host'] ?? null) !== $appHost) {
            return null;
        }

        $path = (string) ($urlParts['path'] ?? '');
        $prefix = '/api/media/';

        if (! str_starts_with($path, $prefix)) {
            return null;
        }

        return rawurldecode(substr($path, strlen($prefix)));
    }

    public static function matches(string $candidate, string $url): bool
    {
        $candidate = self::normalize($candidate);
        $url = self::normalize($url);

        if ($candidate === null || $url === null) {
            return false;
        }

        if ($candidate === $url) {
            return true;
        }

        $candidateResolved = self::resolve($candidate);

        return $candidateResolved !== null && hash_equals($candidateResolved, $url);
    }

    private static function normalize(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return $value === '' ? null : $value;
    }

    private static function isExternalUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private static function isStaticAssetPath(string $value): bool
    {
        return preg_match('#^/(avatars|icons|banners|new thread icons|Generic-avatar-profile\.svg)(/|$)#u', $value) === 1;
    }
}