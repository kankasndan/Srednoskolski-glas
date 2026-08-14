<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Macedonian school year: 1 September through 31 August (Europe/Skopje).
 */
final class AcademicCalendar
{
    public const TIMEZONE = 'Europe/Skopje';

    public const START_MONTH = 9;

    public const START_DAY = 1;

    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now(self::TIMEZONE);
    }

    public static function yearStart(?CarbonImmutable $at = null): CarbonImmutable
    {
        $at = $at?->timezone(self::TIMEZONE) ?? self::now();
        $start = $at->setDate($at->year, self::START_MONTH, self::START_DAY)->startOfDay();

        if ($at->lt($start)) {
            return $start->subYear();
        }

        return $start;
    }

    public static function nextYearStart(?CarbonImmutable $at = null): CarbonImmutable
    {
        return self::yearStart($at)->addYear();
    }

    /**
     * One school change is allowed per academic year. A change dated before
     * this year's 1 September does not consume the current year's slot.
     */
    public static function canChangeSchool(?\DateTimeInterface $lastChangedAt, ?CarbonImmutable $at = null): bool
    {
        if ($lastChangedAt === null) {
            return true;
        }

        $changed = CarbonImmutable::parse($lastChangedAt)->timezone(self::TIMEZONE);

        return $changed->lt(self::yearStart($at));
    }
}
