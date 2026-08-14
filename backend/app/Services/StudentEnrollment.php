<?php

namespace App\Services;

use App\Models\City;
use App\Models\School;
use App\Models\StudentData;
use App\Models\User;
use App\Models\Vocation;
use App\Support\AcademicCalendar;
use App\Support\StudentGrade;
use App\Support\SyncUserContentPermissions;
use Illuminate\Validation\ValidationException;

class StudentEnrollment
{
    public const SCHOOL_SEPARATOR = '|';

    public function __construct(private readonly SyncUserContentPermissions $syncPermissions) {}

    /**
     * @return array{
     *     can_change_school: bool,
     *     school_change_unlocks_at: string|null,
     *     min_grade: int|null,
     *     max_grade: int|null
     * }
     */
    public function capabilities(User $user): array
    {
        $user->loadMissing('studentData');
        $student = $user->studentData;
        $canChangeSchool = AcademicCalendar::canChangeSchool($student?->school_changed_at);
        $currentGrade = $student?->grade !== null ? (int) $student->grade : null;
        $maxGrade = $currentGrade === null
            ? StudentGrade::MAX
            : StudentGrade::maxAllowedFrom($currentGrade);

        if (
            $currentGrade !== null
            && $currentGrade < StudentGrade::MAX
            && ! AcademicCalendar::canChangeSchool($student->grade_promoted_at)
        ) {
            $maxGrade = $currentGrade;
        }

        return [
            'can_change_school' => $canChangeSchool,
            'school_change_unlocks_at' => $canChangeSchool
                ? null
                : AcademicCalendar::nextYearStart()->toDateString(),
            'min_grade' => $currentGrade,
            'max_grade' => $maxGrade,
        ];
    }

    /**
     * @param  array{school: string, area: string, year: string}  $validated
     */
    public function updateFromProfile(User $user, array $validated): void
    {
        $user->loadMissing(['studentData.school.forum']);

        ['school' => $schoolName, 'city' => $cityName] = $this->parseSchoolSelection($validated['school']);
        $school = $this->resolveSchool($cityName, $schoolName);
        $vocation = Vocation::query()->where('name', $validated['area'])->first();

        if ($vocation === null) {
            throw ValidationException::withMessages([
                'area' => ['Избраното подрачје не е валидно.'],
            ]);
        }

        $grade = StudentGrade::fromInput($validated['year']);

        if ($grade === null) {
            throw ValidationException::withMessages([
                'year' => ['Избраната година не е валидна.'],
            ]);
        }

        $previous = $user->studentData;
        $previousSchool = $previous?->school;
        $schoolChanged = $previous === null || (int) $previous->school_id !== (int) $school->id;

        if ($schoolChanged && $previous?->school_id !== null && ! AcademicCalendar::canChangeSchool($previous->school_changed_at)) {
            $unlocks = AcademicCalendar::nextYearStart();

            throw ValidationException::withMessages([
                'school' => [$this->schoolLockedMessage($unlocks->year)],
            ]);
        }

        $this->assertGradeAllowed($previous, $grade);

        $schoolChangedAt = $previous?->school_changed_at;
        if ($schoolChanged && $previous?->school_id !== null) {
            $schoolChangedAt = now();
        }

        $gradePromotedAt = $previous?->grade_promoted_at;
        if ($previous?->grade !== null && $grade === ((int) $previous->grade) + 1) {
            $gradePromotedAt = now();
        }

        StudentData::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'school_id' => $school->id,
                'vocation_id' => $vocation->id,
                'grade' => $grade,
                'school_changed_at' => $schoolChangedAt,
                'grade_promoted_at' => $gradePromotedAt,
            ],
        );

        $this->syncSchoolForum($user, $previousSchool, $school);

        $user->refresh()->load(['studentData.school.forum']);
        $this->syncPermissions->handle($user);
        $user->forgetCachedPermissions();
    }

    /**
     * @return array<string, mixed>
     */
    public function allCapabilities(User $user): array
    {
        $canManageThreads = $user->can('manage threads')
            || $user->hasAnyRole(['super_admin', 'admin', 'moderator']);

        return array_merge([
            'has_completed_onboarding' => $user->hasCompletedOnboarding(),
            'can_create_comments' => $user->canCreateComments(),
            'can_create_threads' => $user->hasCompletedOnboarding()
                && ($canManageThreads || $user->belongsToSchool()),
            'school_forum_id' => $user->schoolForumId(),
        ], $this->capabilities($user));
    }

    public function advanceGrades(): int
    {
        $yearStart = AcademicCalendar::yearStart();
        $previousYearStart = $yearStart->subYear();

        // Promote everyone who did not already move up during the academic year
        // that just ended. Auto-promotions are stamped at yearStart so the next
        // 1 September still advances them, while a same-day rerun is a no-op.
        return (int) StudentData::query()
            ->whereNotNull('grade')
            ->where('grade', '<', StudentGrade::MAX)
            ->where(function ($query) use ($previousYearStart): void {
                $query->whereNull('grade_promoted_at')
                    ->orWhere('grade_promoted_at', '<=', $previousYearStart);
            })
            ->increment('grade', 1, [
                'grade_promoted_at' => $yearStart->toDateTimeString(),
            ]);
    }

    public function schoolLockedMessage(int $septemberYear): string
    {
        return "Не можеш да го промениш училиштето до септември {$septemberYear}.";
    }

    /**
     * @return array{school: string, city: string}
     */
    public function parseSchoolSelection(string $value): array
    {
        $separatorPosition = mb_strrpos($value, self::SCHOOL_SEPARATOR);

        if ($separatorPosition === false) {
            return ['school' => trim($value), 'city' => ''];
        }

        return [
            'school' => trim(mb_substr($value, 0, $separatorPosition)),
            'city' => trim(mb_substr($value, $separatorPosition + mb_strlen(self::SCHOOL_SEPARATOR))),
        ];
    }

    public function resolveSchool(string $cityName, string $schoolName): School
    {
        $city = City::query()->where('name', $cityName)->first();

        if ($city === null) {
            throw ValidationException::withMessages([
                'school' => ['Избраното училиште не е валидно.'],
            ]);
        }

        $school = School::query()
            ->where('city_id', $city->id)
            ->where('name', $schoolName)
            ->first();

        if ($school === null) {
            throw ValidationException::withMessages([
                'school' => ['Избраното училиште не е валидно.'],
            ]);
        }

        return $school;
    }

    private function assertGradeAllowed(?StudentData $previous, int $next): void
    {
        $current = $previous?->grade;

        if ($current === null) {
            return;
        }

        if ($next < $current) {
            throw ValidationException::withMessages([
                'year' => ['Не можеш да се вратиш на пониска година.'],
            ]);
        }

        if ($next > StudentGrade::maxAllowedFrom((int) $current)) {
            throw ValidationException::withMessages([
                'year' => ['Можеш да ја задржиш тековната година или да преминеш на следната.'],
            ]);
        }

        if ($next === ((int) $current) + 1 && ! AcademicCalendar::canChangeSchool($previous->grade_promoted_at)) {
            $unlocks = AcademicCalendar::nextYearStart();

            throw ValidationException::withMessages([
                'year' => ["Веќе ја промени годината оваа учебна година. Следната промена е во септември {$unlocks->year}."],
            ]);
        }
    }

    private function syncSchoolForum(User $user, ?School $previous, School $next): void
    {
        if ($previous !== null && (int) $previous->id === (int) $next->id) {
            $this->followSchoolForum($user, $next);

            return;
        }

        if ($previous?->forum !== null) {
            $forum = $previous->forum;
            $detached = $user->forums()->detach($forum->id);

            if ($detached > 0 && $forum->members_count > 0) {
                $forum->decrement('members_count');
            }
        }

        $this->followSchoolForum($user, $next);
    }

    public function followSchoolForum(User $user, School $school): void
    {
        $forum = $school->forum;

        if ($forum === null) {
            return;
        }

        $sync = $user->forums()->syncWithoutDetaching([$forum->id]);

        if ($sync['attached'] !== []) {
            $forum->increment('members_count');
        }
    }
}
