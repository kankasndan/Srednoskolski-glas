<?php

namespace App\Support;

use Closure;
use Illuminate\Validation\Rule;

final class Username
{
    /** Latin or Cyrillic letters, digits, underscore, dot, hyphen. */
    public const PATTERN = '/^[\p{Latin}\p{Cyrillic}0-9_.-]+$/u';

    public const ROUTE_PATTERN = '[\p{Latin}\p{Cyrillic}0-9_.-]+';

    /** @var list<string> */
    public const RESERVED = [
        'admin',
        'administrator',
        'moderator',
        'super_admin',
        'superadmin',
        'support',
        'staff',
        'official',
        'srednoskolski',
        'glas',
        'api',
        'me',
        'login',
        'register',
        'onboarding',
        'root',
        'system',
        'help',
        'security',
        'mod',
        'anon',
        'anonymous',
    ];

    /**
     * Cyrillic letters that are visually identical to a Latin one, plus digits
     * used as letters. Folded before comparing against the reserved list so
     * "аdmin" (Cyrillic а) and "adm1n" cannot pose as staff.
     *
     * @var array<string, string>
     */
    private const CONFUSABLES = [
        'а' => 'a', 'в' => 'b', 'е' => 'e', 'ѕ' => 's', 'і' => 'i', 'ј' => 'j',
        'к' => 'k', 'м' => 'm', 'н' => 'h', 'о' => 'o', 'р' => 'p', 'с' => 'c',
        'т' => 't', 'у' => 'y', 'х' => 'x',
        '0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '7' => 't',
    ];

    /**
     * @return list<mixed>
     */
    public static function rules(?int $ignoreUserId = null): array
    {
        return [
            'required',
            'string',
            'min:3',
            'max:20',
            'regex:'.self::PATTERN,
            static function (string $attribute, mixed $value, Closure $fail): void {
                $username = is_string($value) ? $value : '';

                if (self::mixesScripts($username)) {
                    $fail('Корисничкото име не може да меша латинични и кирилични букви.');

                    return;
                }

                if (self::isReserved($username)) {
                    $fail('Ова корисничко име е резервирано.');
                }
            },
            Rule::unique('users', 'username')->ignore($ignoreUserId),
        ];
    }

    /**
     * Mixing alphabets is what makes homoglyph impersonation possible
     * ("mаrko" with one Cyrillic а renders identically to "marko").
     */
    public static function mixesScripts(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return preg_match('/\p{Latin}/u', $value) === 1
            && preg_match('/\p{Cyrillic}/u', $value) === 1;
    }

    public static function isReserved(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $canonical = self::canonical($value);

        foreach (self::RESERVED as $reserved) {
            if ($canonical === self::canonical($reserved)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Strip the decoration a name can hide behind (separators, look-alike
     * characters, case) so "Admin", "a.d.m.i.n" and "аdm1n" all collapse to the
     * same string.
     */
    private static function canonical(string $value): string
    {
        $folded = strtr(mb_strtolower($value, 'UTF-8'), self::CONFUSABLES);

        return preg_replace('/[._-]+/u', '', $folded) ?? $folded;
    }
}
