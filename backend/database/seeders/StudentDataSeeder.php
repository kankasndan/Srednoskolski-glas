<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
    ];

    public function run(): void
    {
        $now = now();

        foreach (self::STUDENTS as $student) {
            $userId = DB::table('users')->where('email', $student['email'])->value('id');
            $schoolId = DB::table('schools')
                ->join('cities', 'schools.city_id', '=', 'cities.id')
                ->where('schools.name', $student['school'])
                ->where('cities.name', $student['city'])
                ->value('schools.id');
            $vocationId = DB::table('vocations')->where('name', $student['vocation'])->value('id');

            if ($userId === null || $schoolId === null || $vocationId === null) {
                continue;
            }

            DB::table('student_data')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'school_id' => $schoolId,
                    'vocation_id' => $vocationId,
                    'grade' => $student['grade'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }
}
