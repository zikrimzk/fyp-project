<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Student;
use App\Models\Semester;
use App\Models\Programme;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Imports\StudentImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class SupervisionController extends Controller
{
    /* Student Management */
    public function studentManagement(Request $req)
    {
        try {
            if ($req->ajax()) {

                $data = DB::table('students as a')
                    ->join('semesters as b', 'b.id', '=', 'a.semester_id')
                    ->join('programmes as c', 'c.id', '=', 'a.programme_id')
                    ->select('a.*', 'b.sem_label', 'c.prog_code', 'c.prog_mode')
                    ->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('student_photo', function ($row) {
                    $photo = '
                            <div class="row align-items-center">
                                <div class="col-auto pe-0">
                                    <div class="avatar-sms">
                                        <img src="' . (empty($row->student_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . $row->student_photo)) . '" alt="user-image" />
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <h6 class="mb-0 text-truncate">' . $row->student_name . '</h6>
                                            <small class="text-muted
                                                text-truncate">' . $row->student_email . '</small>
                                        </div>
                                    </div>
                                   
                                </div>
                            </div>
                    ';

                    return $photo;
                });

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
                    // $isReferenced = DB::table('supervision')->where('student_id', $row->id)->exists();

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

                $table->rawColumns(['student_photo', 'student_status', 'action']);

                return $table->make(true);
            }
            return view('staff.supervision.student-management', [
                'title' => 'Student Management',
                'studs' => Student::all(),
                'current_sem' => Semester::where('sem_status', 1)->first()->sem_label ?? 'N/A',
                'progs' => Programme::all(),
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function addStudent(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'student_name' => 'required|string',
            'student_matricno' => 'required|string|unique:students,student_matricno,',
            'student_email' => 'required|email|unique:students,student_email,',
            'student_password' => 'nullable|string|min:8|max:50',
            'student_address' => 'nullable|string',
            'student_phoneno' => 'nullable|string',
            'student_gender' => 'required|string|in:male,female',
            'student_status' => 'required|integer|in:1,2',
            'student_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'student_directory' => 'nullable|string',
            'semester_id' => 'nullable',
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

            /* GET CURRENT SEMESTER */
            $curr_sem_id = Semester::where('sem_status', 1)->first()->id ?? 'N/A';
            $curr_sem = Semester::where('sem_status', 1)->first()->sem_label ?? 'N/A';

            /* GET STUDENT NAME */
            $student_name = Str::upper($validated['student_name']);

            /* SET STUDENT INITIAL PASSWORD */
            $password = bcrypt("pg@" . Str::lower($validated['student_matricno']));

            /* MAKE STUDENT DIRECTORY PATH */
            $validated['student_directory'] = "Student/" . str_replace('/', '', $curr_sem) . "/" . $validated['student_matricno'] . "_" . str_replace(' ', '_', $student_name);
            Storage::makeDirectory("{$validated['student_directory']}");

            /* SAVE STUDENT PHOTO */
            $fileName = null;
            $filePath = null;
            if ($req->hasFile('student_photo')) {

                // 1 - GET THE SPECIFIC DATA
                $student_matricno = Str::upper($validated['student_matricno']);

                // 2 - SET & DECLARE FILE ROUTE
                $fileName = Str::upper($student_matricno . '_' . time() . '_PHOTO') . '.' . $req->file('student_photo')->getClientOriginalExtension();
                $filePath = $validated['student_directory'] . "/photo";

                // 3 - SAVE THE FILE
                $file = $req->file('student_photo');
                $filePath = $file->storeAs($filePath, $fileName, 'public');
            }

            /* CREATE STUDENT DATA */
            Student::create([
                'student_name' => Str::headline($validated['student_name']),
                'student_matricno' => Str::upper($validated['student_matricno']),
                'student_email' => $validated['student_email'],
                'student_password' => $password,
                'student_address' => $validated['student_address'] ?? null,
                'student_phoneno' => $validated['student_phoneno'] ?? null,
                'student_gender' => $validated['student_gender'],
                'student_status' => $validated['student_status'],
                'student_photo' => $filePath ?? null,
                'student_directory' => $validated['student_directory'] ?? null,
                'semester_id' =>  $curr_sem_id,
                'programme_id' => $validated['programme_id'],
            ]);

            return back()->with('success', 'Student added successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error adding student.');
        }
    }

    public function updateStudent(Request $req, $id)
    {
        $id = Crypt::decrypt($id);
        $validator = Validator::make($req->all(), [
            'student_name_up' => 'required|string',
            'student_matricno_up' => 'required|string|unique:students,student_matricno,' . $id,
            'student_email_up' => 'required|email|unique:students,student_email,' . $id,
            'student_address_up' => 'nullable|string',
            'student_phoneno_up' => 'nullable|string',
            'student_gender_up' => 'required|string|in:male,female',
            'student_status_up' => 'required|integer|in:1,2',
            'student_photo_up' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'student_directory_up' => 'nullable|string',
            'programme_id_up' => 'required|integer',
        ], [], [
            'student_name_up' => 'student name',
            'student_matricno_up' => 'student matric number',
            'student_email_up' => 'student email',
            'student_address_up' => 'student address',
            'student_phoneno_up' => 'student phone number',
            'student_gender_up' => 'student gender',
            'student_status_up' => 'student status',
            'student_photo_up' => 'student photo',
            'student_directory_up' => 'student directory',
            'programme_id_up' => 'programme',
        ]);
        // dd($validator);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'updateModal-' . $id);
        }

        try {
            $validated = $validator->validated();
            $student = Student::where('id', $id)->first() ?? null;

            /* GET CURRENT SEMESTER */
            $curr_sem_id = $student->semester_id;
            $curr_sem = Semester::where('id', $curr_sem_id)->first()->sem_label ?? 'N/A';

            /* GET STUDENT NAME */
            $student_name = Str::upper($validated['student_name_up']);

            /* MAKE STUDENT DIRECTORY PATH */
            $oldDirectory = $student->student_directory;
            $validated['student_directory_up'] = "Student/" . str_replace('/', '', $curr_sem) . "/" . $validated['student_matricno_up'] . "_" . str_replace(' ', '_', $student_name);


            if ($oldDirectory !== $validated['student_directory_up']) {
                Storage::move($oldDirectory, $validated['student_directory_up']);
            } else {
                Storage::makeDirectory($validated['student_directory_up']);
            }
            /* SAVE STUDENT PHOTO */
            if ($req->hasFile('student_photo_up')) {
                // 1 - GET THE DATA
                $student_matricno = Str::upper($validated['student_matricno_up']);

                // 2 - SET & DECLARE FILE ROUTE
                $fileName = Str::upper($student_matricno . '_' . time() . '_PHOTO') . '.' . $req->file('student_photo_up')->getClientOriginalExtension();
                $filePath = $validated['student_directory_up'] . "/photo";

                // 3 - SAVE THE FILE
                $file = $req->file('student_photo_up');
                $filePath = $file->storeAs($filePath, $fileName, 'public');

                Student::where('id', $student->id)->update([
                    'student_photo' => $filePath
                ]);
            }
            Student::where('id', $student->id)->update([
                'student_name' => Str::headline($validated['student_name_up']),
                'student_matricno' => Str::upper($validated['student_matricno_up']),
                'student_email' => $validated['student_email_up'],
                'student_address' => $validated['student_address_up'] ?? null,
                'student_phoneno' => $validated['student_phoneno_up'] ?? null,
                'student_gender' => $validated['student_gender_up'],
                'student_status' => $validated['student_status_up'],
                'student_directory' => $validated['student_directory_up'] ?? null,
                'programme_id' => $validated['programme_id_up'],
            ]);

            return back()->with('success', 'Student updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating student.' . $e->getMessage());
        }
    }

    public function deleteStudent($id, $opt)
    {
        try {
            $id = decrypt($id);
            $student = Student::find($id);

            if (!$student) {
                return back()->with('error', 'Student not found.');
            }

            $dirPath = $student->student_directory;

            if ($opt == 1) {
                if (!empty($student->student_directory) && Storage::exists($dirPath)) {
                    Storage::deleteDirectory($dirPath);
                }
                $student->delete();

                return back()->with('success', 'Student deleted successfully.');
            } elseif ($opt == 2) {
                $student->update(['student_status' => 2]);
                return back()->with('success', 'Student set as inactive successfully.');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error deleting student.');
        }
    }

    public function importStudent(Request $req)
    {
        try {
            Excel::import(new StudentImport, $req->file('file'));
            return back()->with('success', 'Student imported successfully.');;
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error importing students.' . $e->getMessage());
        }
    }

    public function exportStudent()
    {
        try {
            return back()->with('success', 'Student exported successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error exporting students.' . $e->getMessage());
        }
    }
}
