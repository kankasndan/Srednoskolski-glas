<?php

namespace App\Console\Commands;

use App\Services\StudentEnrollment;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('students:advance-grades')]
#[Description('Promote students one year on 1 September, unless they already moved up this academic year.')]
class AdvanceStudentGradesCommand extends Command
{
    public function handle(StudentEnrollment $enrollment): int
    {
        $updated = $enrollment->advanceGrades();

        $this->info("Advanced {$updated} student(s) to the next year.");

        return self::SUCCESS;
    }
}
