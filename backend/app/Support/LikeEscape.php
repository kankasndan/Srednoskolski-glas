<?php

namespace App\Support;

/**
 * Escapes user input before it is placed inside a SQL LIKE pattern.
 *
 * Bindings already stop SQL injection, but they do not stop wildcard injection:
 * a bare `%` or `_` in the search box still changes which rows match, which lets
 * a caller enumerate rows the search was never meant to reach (and turns short
 * queries into full table scans).
 */
final class LikeEscape
{
    public static function escape(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    /** Pattern matching values that contain $value. */
    public static function contains(string $value): string
    {
        return '%'.self::escape($value).'%';
    }

    /** Pattern matching values that start with $value. */
    public static function startsWith(string $value): string
    {
        return self::escape($value).'%';
    }
}
