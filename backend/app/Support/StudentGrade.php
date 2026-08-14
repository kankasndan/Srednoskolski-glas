<?php

namespace App\Support;

final class StudentGrade
{
    public const MIN = 1;

    public const MAX = 4;

    /** @var array<string, int> */
    private const MAP = [
        'Прва' => 1,
        'Втора' => 2,
        'Трета' => 3,
        'Четврта' => 4,
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
    ];

    public static function fromInput(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (isset(self::MAP[$value])) {
            return self::MAP[$value];
        }

        if (ctype_digit($value)) {
            $grade = (int) $value;

            return ($grade >= self::MIN && $grade <= self::MAX) ? $grade : null;
        }

        return null;
    }

    public static function maxAllowedFrom(int $current): int
    {
        return min(self::MAX, $current + 1);
    }
}
