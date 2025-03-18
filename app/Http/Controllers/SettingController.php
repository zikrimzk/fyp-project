<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                    } else {
                        $status = '<span class="badge bg-light">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('action', function ($row) {

                    $isReferenced = DB::table('departments')->where('fac_id', $row->id)->exists();

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
                                <a href="javascript: void(0)" class="avtar avtar-xs  btn-light-warning" data-bs-toggle="modal"
                                    data-bs-target="#disableModal-' . $row->id . '">
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
            return abort(404);
        }
    }

    public function addFaculty(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fac_name' => 'required|string',
            'fac_code' => 'required|string|unique:faculties,fac_code,',
            'fac_status' => 'required|integer',
        ], [], [
            'fac_name' => 'faculty name',
            'fac_code' => 'faculty code',
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
            Faculty::create([
                'fac_name' => $validated['fac_name'],
                'fac_code' => $validated['fac_code'],
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
            'fac_status_up' => 'required|integer',
        ], [], [
            'fac_name_up' => 'faculty name',
            'fac_code_up' => 'faculty code',
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
            if ($opt == 1) {
                Faculty::where('id', $id)->delete();
                return redirect()->route('faculty-setting')->with('success', 'Faculty deleted successfully.');
            } elseif ($opt == 2) {
                Faculty::where('id', $id)->update(['fac_status' => 2]);
                return redirect()->route('faculty-setting')->with('success', 'Faculty disabled successfully.');
            }
        } catch (Exception $e) {
            return redirect()->route('faculty-setting')->with('error', 'Oops! Error deleting faculty.');
        }
    }

    /* Department Setting */
    public function departmentSetting()
    {
        try {
            return view('staff.setting.department-setting', [
                'title' => 'Department Setting'
            ]);
        } catch (Exception $e) {
            return abort(404);
        }
    }

    public function addDepartment(Request $request) {}

    public function updateDepartment(Request $request, $id) {}

    public function deleteDepartment(Request $request, $id, $opt) {}

    /* Programme Setting */
    public function programmeSetting()
    {
        try {
            return view('staff.setting.programme-setting', [
                'title' => 'Department Setting'
            ]);
        } catch (Exception $e) {
            return abort(404);
        }
    }

    public function addProgramme(Request $request) {}

    public function updateProgramme(Request $request, $id) {}

    public function deleteProgramme(Request $request, $id, $opt) {}

    /* Semester Setting */
    public function semesterSetting()
    {
        try {
            return view('staff.setting.semester-setting', [
                'title' => 'Semester Setting'
            ]);
        } catch (Exception $e) {
            return abort(404);
        }
    }

    public function addSemester(Request $request) {}

    public function updateSemester(Request $request, $id) {}

    public function deleteSemester(Request $request, $id, $opt) {}
}
