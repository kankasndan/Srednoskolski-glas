<?php

use App\Models\City;
use App\Models\School;
use App\Models\StudentData;
use App\Models\User;
use App\Models\Vocation;
use App\Services\StudentEnrollment;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function () {
    $this->travelBack();
});

function advanceGradesSchool(): School
{
    $city = City::query()->create(['name' => 'Скопје']);

    return School::query()->create([
        'name' => 'Јосип Броз Тито',
        'city_id' => $city->id,
    ]);
}

it('promotes students on 1 september unless they already moved up', function () {
    $this->travelTo(CarbonImmutable::parse('2027-09-01 00:05:00', 'Europe/Skopje'));

    $school = advanceGradesSchool();
    $vocation = Vocation::query()->create(['name' => 'Електротехничка струка']);

    $plain = User::factory()->create(['onboarding_completed_at' => now()]);
    $fourthYear = User::factory()->create(['onboarding_completed_at' => now()]);
    $alreadyPromoted = User::factory()->create(['onboarding_completed_at' => now()]);
    $autoLastYear = User::factory()->create(['onboarding_completed_at' => now()]);

    StudentData::query()->create([
        'user_id' => $plain->id,
        'school_id' => $school->id,
        'vocation_id' => $vocation->id,
        'grade' => 3,
    ]);
    StudentData::query()->create([
        'user_id' => $fourthYear->id,
        'school_id' => $school->id,
        'vocation_id' => $vocation->id,
        'grade' => 4,
    ]);
    StudentData::query()->create([
        'user_id' => $alreadyPromoted->id,
        'school_id' => $school->id,
        'vocation_id' => $vocation->id,
        'grade' => 3,
        'grade_promoted_at' => CarbonImmutable::parse('2026-10-15 12:00:00', 'Europe/Skopje'),
    ]);
    StudentData::query()->create([
        'user_id' => $autoLastYear->id,
        'school_id' => $school->id,
        'vocation_id' => $vocation->id,
        'grade' => 2,
        'grade_promoted_at' => CarbonImmutable::parse('2026-09-01 00:00:00', 'Europe/Skopje'),
    ]);

    $updated = app(StudentEnrollment::class)->advanceGrades();

    expect($updated)->toBe(2)
        ->and($plain->fresh()->studentData->grade)->toBe(4)
        ->and($fourthYear->fresh()->studentData->grade)->toBe(4)
        ->and($alreadyPromoted->fresh()->studentData->grade)->toBe(3)
        ->and($autoLastYear->fresh()->studentData->grade)->toBe(3);

    expect(app(StudentEnrollment::class)->advanceGrades())->toBe(0)
        ->and($autoLastYear->fresh()->studentData->grade)->toBe(3);

    $this->artisan('students:advance-grades')->assertSuccessful();
});
