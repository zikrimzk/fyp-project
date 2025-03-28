<?php

namespace App\Imports;

use App\Models\Staff;
use App\Models\Student;
use App\Models\Semester;
use App\Models\Programme;
use App\Models\Supervision;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */

    public $insertedCount = 0;
    public $skippedCount = 0;
    public $skippedRows = [];

    public function collection(Collection $rows)
    {

        /* GET CURRENT SEMESTER */
        $currentSemester = Semester::where('sem_status', 1)->first();
        $curr_sem_id = $currentSemester->id ?? 'N/A';
        $curr_sem = str_replace('/', '', $currentSemester->sem_label ?? 'N/A');

        foreach ($rows as $row) {

            if ($row->filter()->isEmpty()) {
                continue;
            }


            /* STUDENT GENDER */
            $gender = Str::upper($row['gender'] ?? '');
            if ($gender === 'M' || $gender === 'MALE') {
                $row['gender'] = 'male';
            } elseif ($gender === 'F' || $gender === 'FEMALE') {
                $row['gender'] = 'female';
            } else {
                $row['gender'] = 'other';
            }

            /* STUDENT PROGRAMME */
            $programmeId = null;
            $progCode = Str::upper($row['programme_code'] ?? '');
            $progMode = Str::upper($row['programme_mode'] ?? '');
            if (!empty($progCode) && !empty($progMode)) {
                $programmeId = Programme::where('prog_code', $progCode)
                    ->where('prog_mode', $progMode)
                    ->value('id');
            }
            $row['programme_id'] = $programmeId;

            /* STUDENT SUPERVISIONS */
            $msvId = null;
            $cosvId = null;
            if (!empty($row['main_supervisor_id'])) {
                $msvId = Staff::where('staff_id', Str::upper($row['main_supervisor_id']))->value('id');
            }
            if (!empty($row['co_supervisor_id'])) {
                $cosvId = Staff::where('staff_id', Str::upper($row['co_supervisor_id']))->value('id');
            }

            $row['main_supervisor_id'] = $msvId;
            $row['co_supervisor_id'] = $cosvId;

            /* TRIM WHITE SPACE */
            $trimmedData = array_map(fn($value) => is_string($value) ? trim($value) : $value, $row->toArray());

            /* VALIDATE ALL INPUTS */
            $validator = Validator::make($trimmedData, [
                'student_name' => 'required|string',
                'matricno' => 'required|string|unique:students,student_matricno',
                'email' => 'required|email|unique:students,student_email',
                'password' => 'nullable|string|min:8|max:50',
                'address' => 'nullable|string',
                'phoneno' => 'nullable|string',
                'gender' => 'required|string|in:male,female,other',
                'programme_id' => 'required|integer|exists:programmes,id',
                'main_supervisor_id' => 'required|integer|exists:staff,id',
                'co_supervisor_id' => 'nullable|integer|exists:staff,id|different:main_supervisor_id',
                'title_of_research' => 'nullable|string|max:150'
            ]);

            if ($validator->fails()) {
                $this->skippedCount++;
                $this->skippedRows[] = [
                    'data' => $row->toArray(),
                    'errors' => $validator->errors()->all()
                ];
                continue;
            }

            $validated = $validator->validated();

            /* MAKE STUDENT DIRECTORY PATH */
            $student_name_dir = Str::upper($validated['student_name']);
            $student_matricno = Str::upper($validated['matricno']);
            $student_directory = "Student/{$curr_sem}/{$student_matricno}_" . str_replace(' ', '_', $student_name_dir);
            Storage::makeDirectory($student_directory);

            /* SET STUDENT INITIAL PASSWORD & STUDENT NAME FORMATING */
            $student_name = Str::headline($validated['student_name']);
            $password = bcrypt("pg@" . Str::lower($validated['matricno']));

            /* CREATE STUDENT DATA */
            $student = Student::create([
                'student_name' => $student_name,
                'student_matricno' => $student_matricno,
                'student_email' => Str::lower($validated['email']),
                'student_password' => $password,
                'student_address' => $validated['address'] ?? null,
                'student_phoneno' => $validated['phoneno'] ?? null,
                'student_gender' => $validated['gender'],
                'student_status' => 1,
                'student_photo' => null,
                'student_directory' => $student_directory,
                'student_titleOfResearch' => Str::headline($validated['title_of_research'] ?? ''),
                'semester_id' =>  $curr_sem_id,
                'programme_id' => $validated['programme_id'],
            ]);

            /* CHECK THE EXISTANCE OF SUPERVISOR */
            $checksv = Supervision::where('student_id', $student->id)->where('staff_id', $msvId)->exists() ||  Supervision::where('student_id', $student->id)->where('staff_id', $cosvId)->exists();

            if ($checksv) {
                return back()->with('error', 'Oops! The entered staff is already assigned to student. Please check and select a different staff.');
            }

            /* CREATE SUPERVISION DATA [ MAIN SUPERVISOR ] */
            Supervision::create([
                'student_id' => $student->id,
                'staff_id' => $msvId,
                'supervision_role' => 1
            ]);

            /* CREATE SUPERVISION DATA [ CO-SUPERVISOR ] */
            if (!is_null($cosvId)) {
                Supervision::create([
                    'student_id' => $student->id,
                    'staff_id' => $cosvId,
                    'supervision_role' => 2
                ]);
            }

            /* ASSIGN SUBMISSION TO STUDENT */
            //To be continue ...
            
            $this->insertedCount++;
        }
    }
}
