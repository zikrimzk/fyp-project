<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Semester;
use App\Models\Programme;
use App\Models\Department;
use App\Models\Supervision;
use Illuminate\Support\Str;
use App\Exports\StaffExport;
use App\Imports\StaffImport;
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
                                        <img src="' . (empty($row->student_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . $row->student_directory . '/photo/' . $row->student_photo)) . '" alt="user-image" />
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

                $table->addColumn('student_programme', function ($row) {
                    $mode = null;
                    if ($row->prog_mode == "FT") {
                        $mode = "Full-Time";
                    } elseif ($row->prog_mode == "PT") {
                        $mode = "Part-Time";
                    } else {
                        $mode = "N/A";
                    }
                    $programme = '
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <p class="mb-0 text-truncate">' . $row->prog_code . '</p>
                            <small class="text-muted text-truncate">' . $mode . '</small>
                        </div>
                    </div>              
                    ';
                    return $programme;
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
                                <a href="javascript: void(0)" class="avtar avtar-xs  btn-light-danger ' . ($row->student_status == 2 ? 'disabled-a' : '') . '" data-bs-toggle="modal"
                                    data-bs-target="#disableModal-' . $row->id . '">
                                    <i class="ti ti-trash f-20"></i>
                                </a>
                            ';
                    }

                    return $buttonEdit . $buttonRemove;
                });

                $table->rawColumns(['student_photo', 'student_programme', 'student_status', 'action']);

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
                'student_photo' => $fileName ?? null,
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

                // 1 - REMOVE OLD PHOTO
                if ($student->student_photo && Storage::exists($student->student_directory . '/photo/' . $student->student_photo)) {
                    Storage::delete($student->student_directory . '/photo/' . $student->student_photo);
                }

                // 2 - GET THE DATA
                $student_matricno = Str::upper($validated['student_matricno_up']);

                // 3 - SET & DECLARE FILE ROUTE
                $fileName = Str::upper($student_matricno . '_' . time() . '_PHOTO') . '.' . $req->file('student_photo_up')->getClientOriginalExtension();
                $filePath = $validated['student_directory_up'] . "/photo";

                // 4 - SAVE THE FILE
                $file = $req->file('student_photo_up');
                $filePath = $file->storeAs($filePath, $fileName, 'public');

                Student::where('id', $student->id)->update([
                    'student_photo' => $fileName
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

    /* Staff Management */
    public function staffManagement(Request $req)
    {
        try {
            if ($req->ajax()) {

                $data = DB::table('staff as a')
                    ->join('departments as b', 'b.id', '=', 'a.department_id')
                    ->select('a.*', 'b.dep_name')
                    ->orderBy('staff_name', 'asc')
                    ->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="user-checkbox form-check-input" value="' . $row->id . '">';
                });

                $table->addColumn('staff_photo', function ($row) {
                    $photo = '
                             <div class="row align-items-center">
                                 <div class="col-auto pe-0">
                                     <div class="avatar-sms">
                                         <img src="' . (empty($row->staff_photo) ? asset('assets/images/user/default-profile-1.jpg') : asset('storage/' . $row->staff_photo)) . '" alt="user-image" />
                                     </div>
                                 </div>
                                 <div class="col">
                                     <div class="row align-items-center">
                                         <div class="col-auto">
                                             <h6 class="mb-0 text-truncate">' . $row->staff_name . '</h6>
                                             <small class="text-muted
                                                 text-truncate">' . $row->staff_email . '</small>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                     ';

                    return $photo;
                });

                $table->addColumn('staff_role', function ($row) {
                    $role = '';

                    if ($row->staff_role == 1) {
                        $role = '<span class="badge bg-danger">' . 'Committee' . '</span>';
                    } elseif ($row->staff_role == 2) {
                        $role = '<span class="badge bg-light-info">' . 'Lecturer' . '</span>';
                    } elseif ($row->staff_role == 3) {
                        $role = '<span class="badge bg-light-success">' . 'TDP' . '</span>';
                    } elseif ($row->staff_role == 4) {
                        $role = '<span class="badge bg-success">' . 'Dekan' . '</span>';
                    } else {
                        $role = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $role;
                });

                $table->addColumn('staff_status', function ($row) {
                    $status = '';

                    if ($row->staff_status == 1) {
                        $status = '<span class="badge bg-light-success">' . 'Active' . '</span>';
                    } elseif ($row->staff_status == 2) {
                        $status = '<span class="badge bg-light-secondary">' . 'Inactive' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('action', function ($row) {
                    $isReferenced = false;
                    // $isReferenced = DB::table('supervision')->where('staff_id', $row->id)->exists();

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
                                 <a href="javascript: void(0)" class="avtar avtar-xs  btn-light-danger ' . ($row->staff_status == 2 ? 'disabled-a' : '') . '" data-bs-toggle="modal"
                                     data-bs-target="#disableModal-' . $row->id . '">
                                     <i class="ti ti-trash f-20"></i>
                                 </a>
                             ';
                    }

                    return $buttonEdit . $buttonRemove;
                });

                $table->rawColumns(['checkbox', 'staff_role', 'staff_photo', 'staff_status', 'action']);

                return $table->make(true);
            }
            return view('staff.supervision.staff-management', [
                'title' => 'Staff Management',
                'staffs' => Staff::all(),
                'deps' => Department::all(),
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function addStaff(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'staff_name' => 'required|string|max:255',
            'staff_id' => 'required|string|unique:staff,staff_id',
            'staff_email' => 'required|email|unique:staff,staff_email',
            'staff_password' => 'nullable|string|min:8|max:50',
            'staff_phoneno' => 'nullable|string|max:20',
            'staff_role' => 'required|integer|in:1,2,3,4',
            'staff_status' => 'required|integer|in:1,2',
            'staff_photo' => 'nullable|image|mimes:jpg,jpeg,png',
            'department_id' => 'required|integer|exists:departments,id',
        ], [], [
            'staff_name' => 'staff name',
            'staff_id' => 'staff ID',
            'staff_email' => 'staff email',
            'staff_password' => 'password',
            'staff_phoneno' => 'staff phone number',
            'staff_role' => 'staff role',
            'staff_status' => 'staff status',
            'staff_photo' => 'staff photo',
            'department_id' => 'department',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'addModal');
        }

        try {
            $validated = $validator->validated();

            /* SET STAFF INITIAL PASSWORD */
            $password = bcrypt("pg@" . Str::lower($validated['staff_id']));

            /* MAKE STAFF DIRECTORY PATH */
            $staffDir = "Staff-Photo";

            /* SAVE STAFF PHOTO */
            $fileName = null;
            $filePath = null;
            if ($req->hasFile('staff_photo')) {

                // 1 - GET THE SPECIFIC DATA
                $staff_id = Str::upper($validated['staff_id']);

                // 2 - SET & DECLARE FILE ROUTE
                $fileName = Str::upper($staff_id . '_' . time() . '_PHOTO') . '.' . $req->file('staff_photo')->getClientOriginalExtension();
                $filePath = $staffDir;

                // 3 - SAVE THE FILE
                $file = $req->file('staff_photo');
                $filePath = $file->storeAs($filePath, $fileName, 'public');
            }

            /* CREATE STAFF DATA */
            Staff::create([
                'staff_name' => Str::headline($validated['staff_name']),
                'staff_id' => Str::upper($validated['staff_id']),
                'staff_email' => $validated['staff_email'],
                'staff_password' => $password,
                'staff_phoneno' => $validated['staff_phoneno'] ?? null,
                'staff_role' => $validated['staff_role'],
                'staff_status' => $validated['staff_status'],
                'staff_photo' => $filePath ?? null,
                'department_id' => $validated['department_id'],
            ]);

            return back()->with('success', 'Staff added successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error adding staff.' . $e->getMessage());
        }
    }

    public function updateStaff(Request $req, $id)
    {
        $id = Crypt::decrypt($id);
        $validator = Validator::make($req->all(), [
            'staff_name_up' => 'required|string|max:255',
            'staff_id_up' => 'required|string|unique:staff,staff_id,' . $id,
            'staff_email_up' => 'required|email|unique:staff,staff_email,' . $id,
            'staff_phoneno_up' => 'nullable|string|max:20',
            'staff_role_up' => 'required|integer|in:1,2,3,4',
            'staff_status_up' => 'required|integer|in:1,2',
            'staff_photo_up' => 'nullable|image|mimes:jpg,jpeg,png',
            'department_id_up' => 'required|integer|exists:departments,id',
        ], [], [
            'staff_name_up' => 'staff name',
            'staff_id_up' => 'staff ID',
            'staff_email_up' => 'staff email',
            'staff_phoneno_up' => 'staff phone number',
            'staff_role_up' => 'staff role',
            'staff_status_up' => 'staff status',
            'staff_photo_up' => 'staff photo',
            'department_id_up' => 'department',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'updateModal-' . $id);
        }

        try {
            $validated = $validator->validated();
            $staff = Staff::where('id', $id)->first() ?? null;


            /* MAKE STAFF DIRECTORY PATH */
            $staffDir = "Staff-Photo";

            /* SAVE STAFF PHOTO */
            if ($req->hasFile('staff_photo_up')) {

                // 1 - REMOVE OLD PHOTO
                if ($staff->staff_photo && Storage::exists($staff->staff_photo)) {
                    Storage::delete($staff->staff_photo);
                }

                // 2 - GET THE SPECIFIC DATA
                $staff_id = Str::upper($validated['staff_id_up']);

                // 3 - SET & DECLARE FILE ROUTE
                $fileName = Str::upper($staff_id . '_' . time() . '_PHOTO') . '.' . $req->file('staff_photo_up')->getClientOriginalExtension();
                $filePath = $staffDir;

                // 4 - SAVE THE FILE
                $file = $req->file('staff_photo_up');
                $filePath = $file->storeAs($filePath, $fileName, 'public');

                // 5 - UPDATE PHOTO PATH
                Staff::where('id', $staff->id)->update([
                    'staff_photo' => $filePath
                ]);
            }

            /* UPDATE STAFF DATA */
            Staff::where('id', $staff->id)->update([
                'staff_name' => Str::headline($validated['staff_name_up']),
                'staff_id' => Str::upper($validated['staff_id_up']),
                'staff_email' => $validated['staff_email_up'],
                'staff_phoneno' => $validated['staff_phoneno_up'] ?? null,
                'staff_role' => $validated['staff_role_up'],
                'staff_status' => $validated['staff_status_up'],
                'department_id' => $validated['department_id_up'],
            ]);

            return back()->with('success', 'Staff updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating staff.' . $e->getMessage());
        }
    }

    public function deleteStaff($id, $opt)
    {
        try {
            $id = decrypt($id);
            $staff = Staff::find($id);

            if (!$staff) {
                return back()->with('error', 'Staff not found.');
            }

            if ($opt == 1) {
                // 1 - REMOVE OLD PHOTO
                if ($staff->staff_photo && Storage::exists($staff->staff_photo)) {
                    Storage::delete($staff->staff_photo);
                }

                // 2 - DELETE STAFF
                $staff->delete();

                return back()->with('success', 'Staff deleted successfully.');
            } elseif ($opt == 2) {
                $staff->update(['staff_status' => 2]);
                return back()->with('success', 'Staff set as inactive successfully.');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error deleting staff.');
        }
    }

    public function importStaff(Request $request)
    {
        try {
            $request->validate([
                'staff_file' => 'required|mimes:xlsx,csv'
            ]);

            $import = new StaffImport();
            Excel::import($import, $request->file('staff_file'));

            return back()->with(
                'success',
                "{$import->insertedCount} staff successfully inserted. {$import->skippedCount} data were not inserted."
            )->with('skippedRows', $import->skippedRows);
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error importing staff.' . $e->getMessage());
        }
    }

    public function exportStaff(Request $req)
    {
        try {
            $selectedIds = $req->query('ids');
            return Excel::download(new StaffExport($selectedIds), 'e-PGS_STAFF_LIST_' . date('dMY') . '.xlsx');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error exporting students.' . $e->getMessage());
        }
    }

    /* Supervision Arrangement */
    public function supervisionArrangement(Request $req)
    {
        try {
            if ($req->ajax()) {

                $data = DB::table('students as a')
                    ->leftJoin('supervisions as s', 's.student_id', '=', 'a.id')
                    ->join('programmes as c', 'c.id', '=', 'a.programme_id')
                    ->select('a.*', 'c.prog_code', 'c.prog_mode', DB::raw('COUNT(s.staff_id) as supervision_count'))
                    ->groupBy('a.id', 'c.prog_code', 'c.prog_mode')
                    ->orderByRaw('supervision_count < 2 DESC')
                    ->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('student_photo', function ($row) {
                    $mode = match ($row->prog_mode) {
                        "FT" => "Full-Time",
                        "PT" => "Part-Time",
                        default => "N/A",
                    };

                    $photoUrl = empty($row->student_photo)
                        ? asset('assets/images/user/default-profile-1.jpg')
                        : asset('storage/' . $row->student_directory . '/photo/' . $row->student_photo);

                    return '
                        <div class="d-flex align-items-center" >
                            <div class="me-3">
                                <img src="' . $photoUrl . '" alt="user-image" class="rounded-circle border" style="width: 50px; height: 50px; object-fit: cover;">
                            </div>
                            <div style="max-width: 200px;">
                                <span class="mb-0 fw-medium">' . $row->student_name . '</span>
                                <small class="text-muted d-block fw-medium">' . $row->student_email . '</small>
                                <small class="text-muted d-block fw-medium">' . $row->student_matricno . '</small>
                                <small class="text-muted d-block fw-medium">' . $row->prog_code . ' (' . $mode . ')</small>
                            </div>
                        </div>
                    ';
                });

                $table->addColumn('student_title', function ($row) {

                    if (empty($row->student_titleOfResearch)) {
                        $titleResearch = '
                        <div class="d-flex align-items-center">
                            <span class="text-truncate text-muted fst-italic" style="max-width: 200px;">No Title Of Research</span>
                            <button type="button" class="ms-2 btn btn-white d-inline-flex align-items-center" 
                                data-bs-toggle="modal" data-bs-target="#updateTitleOfResearchModal-' . $row->id . '">
                                <i class="ti ti-plus f-18"></i>
                            </button>
                        </div>
                    ';
                    } else {
                        $titleResearch = '
                        <div class="d-flex align-items-center">
                            <span class="fst-italic" style="max-width: 200px;">' . htmlspecialchars($row->student_titleOfResearch) . '</span>
                            <button type="button" class="ms-2 btn btn-white d-inline-flex align-items-center" 
                                data-bs-toggle="modal" data-bs-target="#updateTitleOfResearchModal-' . $row->id . '">
                                <i class="ti ti-edit f-18"></i>
                            </button>
                        </div>
                    ';
                    }


                    return $titleResearch;
                });

                $table->addColumn('supervisor', function ($row) {
                    $supervisors = DB::table('supervisions as a')
                        ->join('staff as b', 'b.id', '=', 'a.staff_id')
                        ->where('a.student_id', $row->id)
                        ->select('b.staff_name', 'a.staff_id', 'a.student_id', 'a.supervision_role')
                        ->get();

                    if ($supervisors->isEmpty()) {
                        return '
                            <span class="fst-italic text-muted d-block mb-2">No Supervisor Assigned</span>
                        ';
                    }

                    $mainSupervisors = $supervisors->where('supervision_role', 1);
                    $coSupervisors = $supervisors->where('supervision_role', 2);

                    $output = '<div class="p-2 border-0">';

                    // Main Supervisor Section
                    if ($mainSupervisors->isNotEmpty()) {
                        $output .= '<small class="text-muted">Main Supervisor:</small>';
                        foreach ($mainSupervisors as $sv) {
                            $output .= '
                                <div class="d-grid justify-content-between align-items-center rounded mb-1">
                                    <span class="fw-medium">' . htmlspecialchars($sv->staff_name) . '</span>
                                    <div class="mt-2 mb-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal" data-bs-target="#editSupervisorModal-' . $sv->staff_id . '">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteSupervisor(' . $sv->staff_id . ')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </div>';
                        }
                    }

                    // Co-Supervisor Section
                    if ($coSupervisors->isNotEmpty()) {
                        $output .= '<small class="text-muted">Co-Supervisor:</small>';
                        foreach ($coSupervisors as $sv) {
                            $output .= '
                                <div class="d-grid justify-content-between align-items-center rounded mb-1">
                                    <span class="fw-medium">' . htmlspecialchars($sv->staff_name) . '</span>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal" data-bs-target="#editSupervisorModal-' . $sv->staff_id . '">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteSupervisor(' . $sv->staff_id . ')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </div>';
                        }
                    }

                    $output .= '</div>';


                    return $output;
                });

                $table->addColumn('action', function ($row) {
                    $supervisors = DB::table('supervisions as a')
                        ->join('staff as b', 'b.id', '=', 'a.staff_id')
                        ->where('a.student_id', $row->id)
                        ->select('a.staff_id')
                        ->count();

                    if ($supervisors < 2) {
                        return '
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                data-bs-toggle="modal" data-bs-target="#addSupervisionModal-' . $row->id . '">
                                <i class="ti ti-plus"></i>
                            </button>
                        ';
                    }
                });

                $table->rawColumns(['student_photo', 'student_title', 'supervisor', 'action']);

                return $table->make(true);
            }
            return view('staff.supervision.supervision-arrangement', [
                'title' => 'Supervision Arrangement',
                'studs' => Student::all(),
                'staffs' => Staff::where('staff_status', 1)->whereIn('staff_role', [1, 2])->orderBy('staff_name', 'asc')->get(),
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function updateTitleOfResearch(Request $req, $id)
    {
        $id = Crypt::decrypt($id);

        $validator = Validator::make($req->all(), [
            'student_titleOfResearch' => 'required|string|max:150',
        ], [], [
            'student_titleOfResearch' => 'title of research',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'updateTitleOfResearchModal-' . $id);
        }

        try {
            $validated = $validator->validated();

            /* UPDATE STUDENT TITLE OF RESEARCH */
            Student::where('id', $id)->update([
                'student_titleOfResearch' => Str::headline($validated['student_titleOfResearch']),
            ]);

            return back()->with('success', 'Title of research updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating title of research.' . $e->getMessage());
        }
    }

    public function addSupervision(Request $req, $id)
    {
        $id = Crypt::decrypt($id);
        $validator = Validator::make($req->all(), [
            'staff_id' => 'required|integer|exists:staff,id',
            'supervision_role' => 'required|integer|in:1,2',
        ], [], [
            'staff_id' => 'staff',
            'supervision_role' => 'staff role',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'addSupervisionModal-' . $id);
        }

        try {

            $validated = $validator->validated();
            $checkExist = Supervision::where('student_id', $id)->where('staff_id', $validated['staff_id'])->exists() ?? false;

            if ($checkExist) {
                return back()->with('error', 'Oops! The selected staff is already assigned to student. Please check and select a different staff.');
            }

            /* CREATE SUPERVISION DATA */
            Supervision::create([
                'student_id' => $id,
                'staff_id' => $validated['staff_id'],
                'supervision_role' => $validated['supervision_role']
            ]);

            return back()->with('success', 'Supervision added successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error adding supervision.' . $e->getMessage());
        }
    }

    public function updateSupervision(Request $req, $studID, $staffID)
    {
        $studID = Crypt::decrypt($studID);
        $staffID = Crypt::decrypt($staffID);

        $validator = Validator::make($req->all(), [
            'staff_id_up' => 'required|integer|exists:staff,id',
            'supervision_role_up' => 'required|integer|in:1,2',
        ], [], [
            'staff_id_up' => 'staff',
            'supervision_role_up' => 'staff role',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'updateSupervisionModal-' . $studID . '-' . $staffID);
        }

        try {
            $validated = $validator->validated();

            /* UPDATE SUPERVISION DATA */
            Supervision::where('student_id', $studID)->where('staff_id', $staffID)->update([
                'staff_id' => $validated['staff_id_up'],
                'supervision_role' => $validated['supervision_role_up']
            ]);

            return back()->with('success', 'Supervision updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating supervision.' . $e->getMessage());
        }
    }
}
