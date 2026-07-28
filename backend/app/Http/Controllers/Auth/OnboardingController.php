<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOnboardingRequest;
use App\Models\City;
use App\Models\School;
use App\Models\StudentData;
use App\Models\User;
use App\Models\Vocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OnboardingController extends Controller
{
    /**
     * @var array<string, int>
     */
    private const GRADE_MAP = [
        'Прва' => 1,
        'Втора' => 2,
        'Трета' => 3,
        'Четврта' => 4,
    ];

    private const SCHOOL_SEPARATOR = '|';

    public function store(StoreOnboardingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $user->fill([
            'username' => $validated['username'],
            'onboarding_completed_at' => now(),
        ]);
        $user->save();

        if ($validated['is_student']) {
            ['school' => $schoolName, 'city' => $cityName] = $this->parseSchoolSelection($validated['school']);
            $school = $this->resolveSchool($cityName, $schoolName);
            $vocation = Vocation::query()->where('name', $validated['area'])->first();

            if ($vocation === null) {
                throw ValidationException::withMessages([
                    'area' => ['Избраното подрачје не е валидно.'],
                ]);
            }

            $grade = self::GRADE_MAP[$validated['year']] ?? null;

            if ($grade === null) {
                throw ValidationException::withMessages([
                    'year' => ['Избраната година не е валидна.'],
                ]);
            }

            StudentData::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'school_id' => $school->id,
                    'vocation_id' => $vocation->id,
                    'grade' => $grade,
                ],
            );

            $this->followSchoolForum($user, $school);
        } else {
            $user->studentData()?->delete();
        }

        return response()->json([
            'message' => 'Onboarding saved',
            'user' => $user->fresh(['studentData.school.city', 'studentData.vocation']),
        ]);
    }

    /**
     * Split the combined "school|city" selection value into its parts.
     *
     * @return array{school: string, city: string}
     */
    private function parseSchoolSelection(string $value): array
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

    private function resolveSchool(string $cityName, string $schoolName): School
    {
        $city = City::query()->where('name', $cityName)->first();

        if ($city === null) {
            throw ValidationException::withMessages([
                'city' => ['Избраниот град не е валиден.'],
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

    /**
     * Automatically follow the school's forum when the student picks that school.
     */
    private function followSchoolForum(User $user, School $school): void
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
