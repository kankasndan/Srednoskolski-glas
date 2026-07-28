<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use App\Models\Vocation;
use Illuminate\Database\Seeder;

class StudentDataSeeder extends Seeder
{
    /**
     * Student profiles linked to real schools/vocations by name.
     *
     * @var list<array{email: string, school: string, city: string, vocation: string, grade: int}>
     */
    private const STUDENTS = [
        ['email' => 'demo@example.com', 'school' => 'Георги Димитров', 'city' => 'Скопје', 'vocation' => 'Електротехничка струка', 'grade' => 3],
        ['email' => 'ana@example.com', 'school' => 'Јане Сандански', 'city' => 'Битола', 'vocation' => 'Здравствена струка', 'grade' => 2],
        ['email' => 'marko@example.com', 'school' => 'Никола Карев', 'city' => 'Струмица', 'vocation' => 'Машинска струка', 'grade' => 4],
        ['email' => 'elena@example.com', 'school' => 'Орце Николов', 'city' => 'Скопје', 'vocation' => 'Економско-правна и трговска струка', 'grade' => 1],
        ['email' => 'stefan@example.com', 'school' => 'Гоце Стојчески', 'city' => 'Тетово', 'vocation' => 'Сообраќајна струка', 'grade' => 3],
        ['email' => 'ivana@example.com', 'school' => 'Кочо Рацин', 'city' => 'Велес', 'vocation' => 'Хемиско-технолошка струка', 'grade' => 2],
        ['email' => 'test@example.com', 'school' => 'Раде Јовчевски-Корчагин', 'city' => 'Скопје', 'vocation' => 'ПМА', 'grade' => 4],
        ['email' => 'nikola@example.com', 'school' => 'Раде Јовчевски-Корчагин', 'city' => 'Скопје', 'vocation' => 'ПМА', 'grade' => 4],
        ['email' => 'profesor@example.com', 'school' => 'Раде Јовчевски-Корчагин', 'city' => 'Скопје', 'vocation' => 'ПМА', 'grade' => 4],
    ];

    public function run(): void
    {
        foreach (self::STUDENTS as $student) {
            $user = User::where('email', $student['email'])->first();
            $school = School::whereHas('city', fn ($query) => $query->where('name', $student['city']))
                ->where('name', $student['school'])
                ->first();
            $vocation = Vocation::where('name', $student['vocation'])->first();

            if ($user === null || $school === null || $vocation === null) {
                continue;
            }

            $user->studentData()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'school_id' => $school->id,
                    'vocation_id' => $vocation->id,
                    'grade' => $student['grade'],
                ],
            );

            $forum = $school->forum;

            if ($forum !== null) {
                $user->forums()->syncWithoutDetaching([$forum->id]);
            }
        }
    }
}
