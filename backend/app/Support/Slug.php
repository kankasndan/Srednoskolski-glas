<?php

namespace App\Support;

class Slug
{
    /**
     * Macedonian Cyrillic -> ASCII map, mirroring the frontend `slugify` in
     * `frontend/src/lib/forums.js` so backend and frontend slugs stay identical.
     *
     * @var array<string, string>
     */
    private const CYRILLIC_TO_LATIN = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'ѓ' => 'gj',
        'е' => 'e', 'ж' => 'zh', 'з' => 'z', 'ѕ' => 'dz', 'и' => 'i', 'ј' => 'j',
        'к' => 'k', 'л' => 'l', 'љ' => 'lj', 'м' => 'm', 'н' => 'n', 'њ' => 'nj',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'ќ' => 'kj',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'џ' => 'dzh',
        'ш' => 'sh',
    ];

    /**
     * Convert a (possibly Cyrillic) name into a lowercase underscore slug.
     */
    public static function make(string $value): string
    {
        $lower = mb_strtolower(trim($value));
        $slug = '';

        foreach (mb_str_split($lower) as $char) {
            $slug .= $char === ' ' ? '_' : (self::CYRILLIC_TO_LATIN[$char] ?? $char);
        }

        return $slug;
    }
}
