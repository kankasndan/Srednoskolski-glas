<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOnboardingRequest;
use App\Models\City;
use App\Models\School;
use App\Models\StudentData;
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
            $school = $this->resolveSchool($validated['city'], $validated['school']);
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
        } else {
            $user->studentData()?->delete();
        }

        return response()->json([
            'message' => 'Onboarding saved',
            'user' => $user->fresh(['studentData.school.city', 'studentData.vocation']),
        ]);
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
}
