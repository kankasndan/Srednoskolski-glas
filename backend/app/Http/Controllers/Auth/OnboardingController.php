<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOnboardingRequest;
use App\Http\Resources\MeResource;
use App\Models\StudentData;
use App\Models\Vocation;
use App\Services\StudentEnrollment;
use App\Support\StudentGrade;
use App\Support\SyncUserContentPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OnboardingController extends Controller
{
    public function store(
        StoreOnboardingRequest $request,
        SyncUserContentPermissions $syncPermissions,
        StudentEnrollment $enrollment,
    ): JsonResponse {
        $user = $request->user();

        abort_if(
            $user->onboarding_completed_at !== null,
            403,
            'Онбордингот е веќе завршен.',
        );

        $validated = $request->validated();

        $user->fill([
            'username' => $validated['username'],
            'onboarding_completed_at' => now(),
        ]);
        $user->save();

        if ($validated['is_student']) {
            ['school' => $schoolName, 'city' => $cityName] = $enrollment->parseSchoolSelection($validated['school']);
            $school = $enrollment->resolveSchool($cityName, $schoolName);
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

            StudentData::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'school_id' => $school->id,
                    'vocation_id' => $vocation->id,
                    'grade' => $grade,
                ],
            );

            $enrollment->followSchoolForum($user, $school);
        } else {
            $user->studentData()?->delete();
        }

        $user = $user->fresh(['studentData.school.city', 'studentData.school.forum', 'studentData.vocation']);
        $syncPermissions->handle($user);
        $user->forgetCachedPermissions();

        return response()->json([
            'message' => 'Onboarding saved',
            'user' => (new MeResource($user))->resolve(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            'capabilities' => $enrollment->allCapabilities($user),
        ]);
    }
}
