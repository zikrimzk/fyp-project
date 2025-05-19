<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Faculty;
use App\Models\Semester;
use App\Models\Programme;
use App\Models\Department;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\StudentSemester;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /* Faculty Setting */
    public function facultySetting(Request $req)
    {
        try {
            if ($req->ajax()) {

                $data = DB::table('faculties')
                    ->select('id', 'fac_name', 'fac_code', 'fac_status')
                    ->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('fac_status', function ($row) {
                    $status = '';
                    if ($row->fac_status == 1) {
                        $status = '<span class="badge bg-light-success">' . 'Active' . '</span>';
                    } elseif ($row->fac_status == 2) {
                        $status = '<span class="badge bg-light-secondary">' . 'Inactive' . '</span>';
                    } elseif ($row->fac_status == 3) {
                        $status = '<span class="badge bg-success">' . 'Default' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('action', function ($row) {

                    $isReferenced = DB::table('departments')->where('fac_id', $row->id)->exists() || DB::table('programmes')->where('fac_id', $row->id)->exists();

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

                $table->rawColumns(['fac_status', 'action']);

                return $table->make(true);
            }

            return view('staff.setting.faculty-setting', [
                'title' => 'Faculty Setting',
                'facs' => Faculty::all()
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    public function addFaculty(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fac_name' => 'required|string',
            'fac_code' => 'required|string|unique:faculties,fac_code,',
            'fac_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'fac_status' => 'required|integer',
        ], [], [
            'fac_name' => 'faculty name',
            'fac_code' => 'faculty code',
            'fac_logo' => 'faculty logo',
            'fac_status' => 'faculty status',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'addModal');
        }
        try {
            $validated = $validator->validated();

            /* SAVE FACULTY LOGO */
            $fileName = null;
            $filePath = null;
            if ($req->hasFile('fac_logo')) {

                // 1 - GET THE SPECIFIC DATA
                $fac_code = Str::upper($validated['fac_code']);

                // 2 - SET & DECLARE FILE ROUTE
                $fileName = Str::upper($fac_code . '_LOGO') . '.' . $req->file('fac_logo')->getClientOriginalExtension();
                $filePath = "faculty-logo";

                // 3 - SAVE THE FILE
                $file = $req->file('fac_logo');
                $filePath = $file->storeAs($filePath, $fileName, 'public');
            }

            Faculty::create([
                'fac_name' => $validated['fac_name'],
                'fac_code' => $validated['fac_code'],
                'fac_logo' => $filePath,
                'fac_status' => $validated['fac_status']
            ]);

            return back()->with('success', 'Faculty added successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error adding faculty.');
        }
    }

    public function updateFaculty(Request $req, $id)
    {
        $id = decrypt($id);

        $validator = Validator::make($req->all(), [
            'fac_name_up' => 'required|string',
            'fac_code_up' => 'required|string|unique:faculties,fac_code,' . $id,
            'fac_logo_up' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'fac_status_up' => 'required|integer',
        ], [], [
            'fac_name_up' => 'faculty name',
            'fac_code_up' => 'faculty code',
            'fac_logo_up' => 'faculty logo',
            'fac_status_up' => 'faculty status',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'updateModal-' . $id);
        }
        try {
            $validated = $validator->validated();

            /* UPDATE FACULTY LOGO */
            $fileName = null;
            $filePath = null;
            if ($req->hasFile('fac_logo_up')) {

                // 1 - GET THE SPECIFIC DATA
                $fac_code = Str::upper($validated['fac_code_up']);

                // 2 - SET & DECLARE FILE ROUTE
                $fileName = Str::upper($fac_code . '_LOGO') . '.' . $req->file('fac_logo_up')->getClientOriginalExtension();
                $filePath = "faculty-logo";

                // 3 - SAVE THE FILE
                $file = $req->file('fac_logo_up');
                $filePath = $file->storeAs($filePath, $fileName, 'public');

                // 4 - UPDATE the correct column!
                Faculty::find($id)->update([
                    'fac_logo' => $filePath
                ]);
            }
            Faculty::find($id)->update([
                'fac_name' => $validated['fac_name_up'],
                'fac_code' => $validated['fac_code_up'],
                'fac_status' => $validated['fac_status_up']
            ]);

            return back()->with('success', 'Faculty updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating faculty.');
        }
    }

    public function deleteFaculty($id, $opt)
    {
        try {
            $id = decrypt($id);
            $faculty = Faculty::whereId($id)->first();

            if (!$faculty) {
                return back()->with('error', 'Faculty not found.');
            }

            if ($opt == 1) {
                // Delete logo file if it exists
                if ($faculty->fac_logo && Storage::disk('public')->exists($faculty->fac_logo)) {
                    Storage::disk('public')->delete($faculty->fac_logo);
                }

                // Then delete the faculty record
                $faculty->delete();

                return back()->with('success', 'Faculty deleted successfully.');
            } elseif ($opt == 2) {

                $faculty->update(['fac_status' => 0]);

                return back()->with('success', 'Faculty disabled successfully.');
            } else {
                return back()->with('error', 'Invalid option.');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error deleting faculty.');
        }
    }

    public function setDefaultFaculty(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'faculty_id' => 'required|exists:faculties,id',
        ], [], [
            'faculty_id' => 'faculty',

        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'setdefaultModal');
        }
        try {
            $validated = $validator->validated();
            Faculty::where('fac_status', 3)->update([
                'fac_status' => 1
            ]);

            Faculty::where('id', $validated['faculty_id'])->update([
                'fac_status' => 3
            ]);

            return back()->with('success', 'Dafault Faculty updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating default faculty.');
        }
    }

    /* Department Setting */
    public function departmentSetting(Request $req)
    {
        try {
            if ($req->ajax()) {

                $data = DB::table('departments as a')
                    ->join('faculties as b', 'b.id', '=', 'a.fac_id')
                    ->select('a.id', 'a.dep_name', 'a.dep_status', 'a.dep_code', 'a.dep_status', 'b.fac_name', 'b.fac_code')
                    ->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('dep_status', function ($row) {
                    $status = '';
                    if ($row->dep_status == 1) {
                        $status = '<span class="badge bg-light-success">' . 'Active' . '</span>';
                    } elseif ($row->dep_status == 2) {
                        $status = '<span class="badge bg-light-secondary">' . 'Inactive' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('action', function ($row) {
                    $isReferenced = false;
                    $isReferenced = DB::table('staff')->where('department_id', $row->id)->exists();

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

                $table->rawColumns(['dep_status', 'action']);

                return $table->make(true);
            }
            return view('staff.setting.department-setting', [
                'title' => 'Department Setting',
                'deps' => Department::all(),
                'facs' => Faculty::all()
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function addDepartment(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'dep_name' => 'required|string',
            'dep_code' => 'required|string|max:15|unique:departments,dep_code,',
            'dep_status' => 'required|integer',
            'fac_id' => 'required|integer',
        ], [], [
            'dep_name' => 'department name',
            'dep_code' => 'department code',
            'dep_status' => 'department status',
            'fac_id' => 'faculty',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'addModal');
        }
        try {
            $validated = $validator->validated();
            Department::create([
                'dep_name' => $validated['dep_name'],
                'dep_code' => $validated['dep_code'],
                'dep_status' => $validated['dep_status'],
                'fac_id' => $validated['fac_id']
            ]);

            return back()->with('success', 'Department added successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error adding department.');
        }
    }

    public function updateDepartment(Request $req, $id)
    {
        $id = decrypt($id);

        $validator = Validator::make($req->all(), [
            'dep_name_up' => 'required|string',
            'dep_code_up' => 'required|string|max:15|unique:departments,dep_code,' . $id,
            'dep_status_up' => 'required|integer',
            'fac_id_up' => 'required|integer',
        ], [], [
            'dep_name_up' => 'department name',
            'dep_code_up' => 'department code',
            'dep_status_up' => 'department status',
            'fac_id_up' => 'faculty',
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'updateModal-' . $id);
        }
        try {
            $validated = $validator->validated();
            Department::find($id)->update([
                'dep_name' => $validated['dep_name_up'],
                'dep_code' => $validated['dep_code_up'],
                'dep_status' => $validated['dep_status_up'],
                'fac_id' => $validated['fac_id_up']
            ]);

            return back()->with('success', 'Department updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating department.');
        }
    }

    public function deleteDepartment($id, $opt)
    {
        try {
            $id = decrypt($id);
            if ($opt == 1) {
                Department::where('id', $id)->delete();
                return back()->with('success', 'Department deleted successfully.');
            } elseif ($opt == 2) {
                Department::where('id', $id)->update(['dep_status' => 0]);
                return back()->with('success', 'Department disabled successfully.');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error deleting department.');
        }
    }

    /* Programme Setting */
    public function programmeSetting(Request $req)
    {
        try {
            if ($req->ajax()) {

                $data = DB::table('programmes as a')
                    ->join('faculties as b', 'b.id', '=', 'a.fac_id')
                    ->select('a.id', 'a.prog_name', 'a.prog_code', 'a.prog_mode', 'a.prog_status', 'b.fac_name', 'b.fac_code')
                    ->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('prog_status', function ($row) {
                    $status = '';
                    if ($row->prog_status == 1) {
                        $status = '<span class="badge bg-light-success">' . 'Active' . '</span>';
                    } elseif ($row->prog_status == 2) {
                        $status = '<span class="badge bg-light-secondary">' . 'Inactive' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('action', function ($row) {
                    $isReferenced = false;
                    $isReferenced = DB::table('students')->where('programme_id', $row->id)->exists();

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

                $table->rawColumns(['prog_status', 'action']);

                return $table->make(true);
            }
            return view('staff.setting.programme-setting', [
                'title' => 'Programme Setting',
                'progs' => Programme::all(),
                'facs' => Faculty::all()
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function addProgramme(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'prog_code' => 'required|string',
            'prog_name' => 'required|string',
            'prog_mode' => 'required|string|max:5',
            'prog_status' => 'required|integer',
            'fac_id' => 'required|integer',
        ], [], [
            'prog_code' => 'programme code',
            'prog_name' => 'programme name',
            'prog_mode' => 'programme mode',
            'prog_status' => 'programme status',
            'fac_id' => 'faculty',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'addModal');
        }
        try {
            $validated = $validator->validated();
            Programme::create([
                'prog_code' => $validated['prog_code'],
                'prog_name' => $validated['prog_name'],
                'prog_mode' => $validated['prog_mode'],
                'prog_status' => $validated['prog_status'],
                'fac_id' => $validated['fac_id']
            ]);

            return back()->with('success', 'Programme added successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error adding programme.');
        }
    }

    public function updateProgramme(Request $req, $id)
    {
        $id = decrypt($id);
        $validator = Validator::make($req->all(), [
            'prog_code_up' => 'required|string',
            'prog_name_up' => 'required|string',
            'prog_mode_up' => 'required|string|max:5',
            'prog_status_up' => 'required|integer',
            'fac_id_up' => 'required|integer',
        ], [], [
            'prog_code_up' => 'programme code',
            'prog_name_up' => 'programme name',
            'prog_mode_up' => 'programme mode',
            'prog_status_up' => 'programme status',
            'fac_id_up' => 'faculty',
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'updateModal-' . $id);
        }

        try {
            $validated = $validator->validated();

            Programme::find($id)->update([
                'prog_code' => $validated['prog_code_up'],
                'prog_name' => $validated['prog_name_up'],
                'prog_mode' => $validated['prog_mode_up'],
                'prog_status' => $validated['prog_status_up'],
                'fac_id' => $validated['fac_id_up']
            ]);

            return back()->with('success', 'Programme updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating programme.');
        }
    }

    public function deleteProgramme($id, $opt)
    {
        try {
            $id = decrypt($id);
            if ($opt == 1) {
                Programme::where('id', $id)->delete();
                return back()->with('success', 'Programme deleted successfully.');
            } elseif ($opt == 2) {
                Programme::where('id', $id)->update(['prog_status' => 0]);
                return back()->with('success', 'Programme disabled successfully.');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error deleting programme.');
        }
    }

    /* Semester Setting */
    public function semesterSetting(Request $req)
    {
        try {
            if ($req->ajax()) {

                $data = DB::table('semesters')
                    ->select('id', 'sem_label', 'sem_startdate', 'sem_enddate', 'sem_status')
                    ->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('sem_startdate', function ($row) {
                    $startdate = Carbon::parse($row->sem_startdate)->format('d M Y');
                    return $startdate;
                });
                $table->addColumn('sem_enddate', function ($row) {
                    $enddate = Carbon::parse($row->sem_enddate)->format('d M Y');
                    return $enddate;
                });

                $table->addColumn('sem_duration', function ($row) {
                    $duration = Carbon::parse($row->sem_startdate)->diffInWeeks(Carbon::parse($row->sem_enddate)) . ' weeks';
                    return $duration;
                });

                $table->addColumn('sem_status', function ($row) {
                    $status = match ($row->sem_status) {
                        1 =>  '<span class="badge bg-light-success">' . 'Active (Current)' . '</span>',
                        2 => '<span class="badge bg-light-secondary">' . 'Upcoming' . '</span>',
                        3 => '<span class="badge bg-light-danger">' . 'Past' . '</span>',
                        default => '<span class="badge bg-light-danger">' . 'N/A' . '</span>',
                    };

                    return $status;
                });

                $table->addColumn('action', function ($row) {
                    $isReferenced = false;
                    $isReferenced = DB::table('students')->where('semester_id', $row->id)->exists();

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

                $table->rawColumns(['sem_startdate', 'sem_enddate', 'sem_duration', 'sem_status', 'action']);

                return $table->make(true);
            }
            return view('staff.setting.semester-setting', [
                'title' => 'Semester Setting',
                'sems' => Semester::all()
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function addSemester(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'sem_label' => 'required|string',
            'sem_startdate' => 'required|date',
            'sem_enddate' => 'required|date|after:sem_startdate',
        ], [], [
            'sem_label' => 'semester label',
            'sem_startdate' => 'semester start date',
            'sem_enddate' => 'semester end date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'addModal');
        }
        try {
            $validated = $validator->validated();

            $currDate = Carbon::now();
            $startDate = Carbon::parse($validated['sem_startdate']);

            if ($startDate->lt($currDate)) {
                $validated['sem_status'] = 3;
            } elseif ($startDate->gt($currDate)) {
                $validated['sem_status'] = 2;
            }

            Semester::create([
                'sem_label' => Str::upper($validated['sem_label']),
                'sem_startdate' => $validated['sem_startdate'],
                'sem_enddate' => $validated['sem_enddate'],
                'sem_status' => $validated['sem_status']
            ]);

            return back()->with('success', 'Semester added successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error adding semester.');
        }
    }

    public function updateSemester(Request $req, $id)
    {
        $id = decrypt($id);
        $validator = Validator::make($req->all(), [
            'sem_label_up' => 'required|string',
            'sem_startdate_up' => 'required|date',
            'sem_enddate_up' => 'required|date|after:sem_startdate_up',
        ], [], [
            'sem_label_up' => 'semester label',
            'sem_startdate_up' => 'semester start date',
            'sem_enddate_up' => 'semester end date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'updateModal-' . $id);
        }

        try {
            $validated = $validator->validated();

            $currDate = Carbon::now();
            $startDate = Carbon::parse($validated['sem_startdate_up']);

            if ($startDate->lt($currDate)) {
                $validated['sem_status'] = 3;
            } elseif ($startDate->gt($currDate)) {
                $validated['sem_status'] = 2;
            }

            Semester::find($id)->update([
                'sem_label' => Str::upper($validated['sem_label_up']),
                'sem_startdate' => $validated['sem_startdate_up'],
                'sem_enddate' => $validated['sem_enddate_up'],
                'sem_status' => $validated['sem_status']
            ]);

            return back()->with('success', 'Semester updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating semester.' . $e->getMessage());
        }
    }

    public function deleteSemester($id, $opt)
    {
        try {
            $id = decrypt($id);
            if ($opt == 1) {
                Semester::where('id', $id)->delete();
                return back()->with('success', 'Semester deleted successfully.');
            } elseif ($opt == 2) {
                Semester::where('id', $id)->update(['sem_status' => 0]);
                return back()->with('success', 'Semester disabled successfully.');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error deleting semester.');
        }
    }

    public function changeCurrentSemester(Request $req)
    {
        try {

            if ($req->semester_id === null) {
                return back()->with('error', 'Please select new semester to continue.');
            }
            // GET CURRENT SEMESTER
            $currsemester = Semester::where('sem_status', 1)->first();

            // UPDATE STUDENT PREVIOUS SEMESTER STATUS TO "Completed"
            StudentSemester::where('semester_id', $currsemester->id)->where('ss_status', 1)->update(['ss_status' => 4]);

            // UPDATE THE PREVIOUS SEM STATUS TO "Past"
            $currsemester->sem_status = 3;
            $currsemester->save();

            // UPDATE THE CURRENT SEM STATUS TO "Current"
            Semester::where('id', $req->semester_id)->update(['sem_status' => 1]);
            $newsem = Semester::where('sem_status', 1)->first();
            return back()->with('success', 'Current semester have been change to ' . $newsem->sem_label);
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Something went wrong. Please try again.' . $e->getMessage());
        }
    }
}
