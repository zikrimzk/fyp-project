<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Semester;
use Illuminate\Support\Str;
use App\Models\StudentSemester;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentSemesterImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */

    public $insertedCount = 0;
    public $skippedCount = 0;
    public $skippedRows = [];
    public function collection(Collection $rows)
    {
        $current_semester_id = Semester::where('sem_status', 1)->first()->id;

        foreach ($rows as $row) {

            if ($row->filter()->isEmpty()) {
                continue;
            }

            //STUDENT MATRIC NUMBER 
            if (Str::upper($row['student_matricno']) != null) {
                $student = Student::where('student_matricno', Str::upper($row['student_matricno']))->where('student_status', 1)->first();
                $row['student_matricno'] = $student->id ?? null;
            }

            //CHECK EXIST 
            $checkExists = StudentSemester::where('student_id', $row['student_matricno'])
                ->where('semester_id', $current_semester_id)
                ->exists();

            $row['current_semester_id'] = $current_semester_id;

            $trimmedData = array_map(function ($value) {
                return is_string($value) ? trim($value) : $value;
            }, $row->toArray());

            $validator = Validator::make($trimmedData, [
                'student_matricno' => 'required|integer|exists:students,id',
                'current_semester_id' => 'required|integer|exists:semesters,id',
            ], [], [
                'student_matricno' => 'student matric number',
                'current_semester_id' => 'current semester',
            ]);

            // ERROR HANDLING 
            $errors = [];

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
            }

            if ($checkExists) {
                $errors[] = 'The student is already enrolled in the current semester.';
            }

            if (!empty($errors)) {
                $this->skippedCount++;
                $this->skippedRows[] = [
                    'data' => $row->toArray(),
                    'errors' => $errors
                ];
                continue;
            }

            $validated = $validator->validated();

            /* CREATE STUDENT SEMESTER DATA */
            StudentSemester::create([
                'student_id' => $validated['student_matricno'],
                'semester_id' => $validated['current_semester_id']
            ]);

            $this->insertedCount++;
        }
    }
}
