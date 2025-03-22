<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Student;
use App\Models\Semester;
use App\Models\Programme;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class SupervisionController extends Controller
{
    /* Student Management */
    public function studentManagement(Request $req)
    {
        try {
            if ($req->ajax()) {

                $data = DB::table('semesters')
                    ->select('id', 'sem_label', 'sem_startdate', 'sem_enddate', 'sem_status')
                    ->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('student_status', function ($row) {
                    $status = '';

                    if ($row->student_status == 1) {
                        $status = '<span class="badge bg-light-success">' . 'Active' . '</span>';
                    } elseif ($row->student_status == 2) {
                        $status = '<span class="badge bg-light-secondary">' . 'Inactive' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('action', function ($row) {
                    $isReferenced = false;
                    // $isReferenced = DB::table('students')->where('semester_id', $row->id)->exists();

                    $buttonEdit =
                        '
                            <a href="javascript: void(0)" class="avtar avtar-xs btn-light-primary" data-bs-toggle="modal"
                                data-bs-target="#updateModal-' . $row->id . '">
                                <i class="ti ti-edit f-20"></i>
                            </a>
                        ';

                    if (!$isReferenced) {
                        $buttonRemove =
                            '
                                <a href="javascript: void(0)" class="avtar avtar-xs  btn-light-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal-' . $row->id . '">
                                    <i class="ti ti-trash f-20"></i>
                                </a>
                            ';
                    } else {

                        $buttonRemove =
                            '
                                <a href="javascript: void(0)" class="avtar avtar-xs  btn-light-danger disabled-a" data-bs-toggle="modal"
                                    data-bs-target="#disableModal">
                                    <i class="ti ti-trash f-20"></i>
                                </a>
                            ';
                    }

                    return $buttonEdit . $buttonRemove;
                });

                $table->rawColumns(['student_status', 'action']);

                return $table->make(true);
            }
            return view('staff.supervision.student-management', [
                'title' => 'Student Management',
                'current_sem' => Semester::where('sem_status', 1)->first()->sem_label ?? 'N/A',
                'progs' => Programme::all()
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function addStudent(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'student_name' => 'required|string',
            'student_matricno' => 'required|string',
            'student_email' => 'required|email|unique:students,student_email',
            'student_password' => 'required|string|min:8|max:50',
            'student_address' => 'nullable|string',
            'student_phoneno' => 'nullable|string',
            'student_gender' => 'required|string|in:Male,Female',
            'student_status' => 'required|integer|in:1,2',
            'student_role' => 'required|integer',
            'student_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'student_directory' => 'nullable|string',
            'semester_id' => 'required|integer',
            'programme_id' => 'required|integer',
        ], [], [
            'student_name' => 'student name',
            'student_matricno' => 'student matric number',
            'student_email' => 'student email',
            'student_password' => 'password',
            'student_address' => 'student address',
            'student_phoneno' => 'student phone number',
            'student_gender' => 'student gender',
            'student_status' => 'student status',
            'student_role' => 'student role',
            'student_photo' => 'student photo',
            'student_directory' => 'student directory',
            'semester_id' => 'semester',
            'programme_id' => 'programme',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'addModal');
        }

        try {
            $validated = $validator->validated();
            $password = bcrypt("pg@". Str::lower($validated['student_matricno']));

            Student::create([
                'student_name' => Str::headline($validated['student_name']),
                'student_matricno' => Str::upper($validated['student_matricno']),
                'student_email' => $validated['student_email'],
                'student_password' => $password,
                'student_address' => $validated['student_address'] ?? null,
                'student_phoneno' => $validated['student_phoneno'] ?? null,
                'student_gender' => $validated['student_gender'],
                'student_status' => $validated['student_status'],
                'student_role' => $validated['student_role'],
                'student_photo' => $validated['student_photo'] ?? null,
                'student_directory' => $validated['student_directory'] ?? null,
                'semester_id' => $validated['semester_id'],
                'programme_id' => $validated['programme_id'],
            ]);

            return back()->with('success', 'Student added successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error adding student.');
        }
    }
}
