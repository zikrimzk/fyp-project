<?php

namespace Tests\Unit;

use App\Models\Student;
use App\Services\SubmissionEligibility;
use Tests\TestCase;

class OpenProcedureEligibilityTest extends TestCase
{
    public function test_open_nonrepeatable_procedure_only_requires_its_semester_timeline(): void
    {
        $student = new Student;
        $student->student_semcount = 3;

        $procedure = (object) [
            'timeline_sem' => 3,
            'init_status' => 1,
            'is_repeatable' => 0,
        ];

        $this->assertSame(1, (new SubmissionEligibility)->requirements($student, $procedure));
    }

    public function test_open_procedure_remains_unavailable_before_its_semester_timeline(): void
    {
        $student = new Student;
        $student->student_semcount = 2;

        $procedure = (object) [
            'timeline_sem' => 3,
            'init_status' => 1,
            'is_repeatable' => 0,
        ];

        $this->assertSame(7, (new SubmissionEligibility)->requirements($student, $procedure));
    }
}
