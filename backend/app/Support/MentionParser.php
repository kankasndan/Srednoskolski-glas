<?php

namespace App\Support;

final class MentionParser
{
    /**
     * @username tokens. Dots/hyphens may appear inside a name, but not at the end,
     * so `@marko_p.` is parsed as `marko_p`.
     */
    public const TOKEN_REGEX = '/(?<![\p{L}0-9_.-])@([\p{L}0-9_](?:[\p{L}0-9_.-]*[\p{L}0-9_])?)/u';

    /**
     * Unique usernames mentioned in free text, in first-seen order.
     *
     * @return list<string>
     */
    public static function usernames(string $content): array
    {
        preg_match_all(self::TOKEN_REGEX, $content, $matches);

        $usernames = [];
        foreach ($matches[1] as $username) {
            $length = mb_strlen($username, 'UTF-8');
            if ($length < 3 || $length > 20) {
                continue;
            }

            $key = mb_strtolower($username, 'UTF-8');
            if (! array_key_exists($key, $usernames)) {
                $usernames[$key] = $username;
            }
        }

        return array_values($usernames);
    }
}
