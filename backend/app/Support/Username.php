<?php

namespace App\Support;

use Illuminate\Validation\Rule;

final class Username
{
    public const PATTERN = '/^[A-Za-z0-9_.-]+$/';

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
            Rule::unique('users', 'username')->ignore($ignoreUserId),
        ];
    }

    public static function isReserved(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return in_array(strtolower($value), self::RESERVED, true);
    }
}
