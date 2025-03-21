<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Activity;
use App\Models\Document;
use App\Models\Procedure;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class SOPController extends Controller
{

    /* Activity Setting (Activity + Document) */
    public function activitySetting(Request $req)
    {
        try {
            return view('staff.sop.activity-setting', [
                'title' => 'Activity Setting',
                'docs' => Document::all(),
                'acts' => Activity::all()
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function viewActivity()
    {
        try {
            $data = Activity::all();
            return response()->json($data);
        } catch (Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ],
                500
            );
        }
    }

    public function addActivity(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'act_name' => 'required|string|unique:activities,act_name,',
        ], [], [
            'act_name' => 'activity name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validated = $validator->validated();
            $activity = Activity::create([
                'act_name' => $validated['act_name'],
            ]);

            return response()->json([
                'success' => true,
                'activity' => $activity,
                'message' => 'Activity added successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateActivity(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'act_name_up' => 'required|string|unique:activities,act_name,' . $req->id,
        ], [], [
            'act_name_up' => 'activity name',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $activity = Activity::findOrFail($req->id);
            $activity->update([
                'act_name' => $validated['act_name_up'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Activity updated successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteActivity($id)
    {
        try {

            Activity::where('id', $id)->delete();
            return response()->json([
                'success' => true,
                'message' => 'Activity deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Oops! Error deleting activity.'
            ], 500);
        }
    }

    public function viewDocumentByActivity($id)
    {
        try {
            $data = DB::table('documents')
                ->where('activity_id', $id)
                ->get();
            return response()->json(
                [
                    'success' => true,
                    'data' => $data,
                ],
                200
            );
        } catch (Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ],
                500
            );
        }
    }

    public function addDocument(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'doc_name' => 'required|string',
            'isRequired' => 'required|integer',
            'isShowDoc' => 'required|integer',
            'doc_status' => 'required|integer',
            'act_id' => 'required|integer',
        ], [], [
            'doc_name' => 'document name',
            'isRequired' => 'document required',
            'isShowDoc' => 'document appear in form',
            'doc_status' => 'document status',
            'act_id' => 'activity',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $document = Document::create([
                'doc_name' => $req->doc_name,
                'isRequired' => $req->isRequired,
                'isShowDoc' => $req->isShowDoc,
                'doc_status' => $req->doc_status,
                'activity_id' => $req->act_id,
            ]);
            return response()->json(['success' => true, 'document' => $document, 'message' => 'Document added successfully.'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateDocument(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'doc_name_up' => 'required|string',
            'isRequired_up' => 'required|integer',
            'isShowDoc_up' => 'required|integer',
            'doc_status_up' => 'required|integer',
        ], [], [
            'doc_name_up' => 'document name',
            'isRequired_up' => 'document required',
            'isShowDoc_up' => 'document appear in form',
            'doc_status_up' => 'document status',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $document = Document::findOrFail($req->doc_id_up);
            $document->update([
                'doc_name' => $req->doc_name_up,
                'isRequired' => $req->isRequired_up,
                'isShowDoc' => $req->isShowDoc_up,
                'doc_status' => $req->doc_status_up,
            ]);
            return response()->json(['success' => true, 'document' => $document, 'message' => 'Document updated successfully.'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteDocument($id)
    {
        try {

            Document::where('id', $id)->delete();
            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Oops! Error deleting document.'
            ], 500);
        }
    }

    /* Procedure Setting  */
    public function procedureSetting(Request $req)
    {
        $data = DB::table('procedures as a')
            ->join('activities as b', 'b.id', '=', 'a.activity_id')
            ->join('programmes as c', 'c.id', '=', 'a.programme_id')
            ->select('a.*', 'b.*', 'c.*')
            ->get();

        // dd($data);
        try {
            if ($req->ajax()) {

                $data = DB::table('procedures as a')
                    ->join('activities as b', 'b.id', '=', 'a.activity_id')
                    ->join('programmes as c', 'c.id', '=', 'a.programme_id')
                    ->select('a.*', 'b.*', 'c.*')
                    ->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('is_haveEva', function ($row) {
                    $status = '';
                    if ($row->is_haveEva == 1) {
                        $status = '<span class="badge bg-success">' . 'Yes' . '</span>';
                    } elseif ($row->is_haveEva == 0) {
                        $status = '<span class="badge bg-danger">' . 'No' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }
                    return $status;
                });

                $table->addColumn('init_status', function ($row) {
                    $status = '';
                    if ($row->init_status == 1) {
                        $status = '<span class="badge bg-success">' . 'O' . '</span>';
                    } elseif ($row->init_status == 2) {
                        $status = '<span class="badge bg-danger">' . 'L' . '</span>';
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
                                data-bs-target="#updateModal-' . $row->activity_id . '-' . $row->programme_id . '">
                                <i class="ti ti-edit f-20"></i>
                            </a>
                        ';

                    if (!$isReferenced) {
                        $buttonRemove =
                            '
                                <a href="javascript: void(0)" class="avtar avtar-xs  btn-light-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal-' . $row->activity_id . '-' . $row->programme_id . '">
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

                $table->rawColumns(['is_haveEva', 'init_status', 'action']);

                return $table->make(true);
            }
            return view('staff.sop.procedure-setting', [
                'title' => 'Procedure Setting',
                'acts' => Activity::all(),
                'progs' => Programme::all(),
                'pros' => Procedure::all()
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function addProcedure(Request $req)
    {
        // dd($req->all());
        $validator = Validator::make($req->all(), [
            'activity_id'   => 'required|integer|exists:activities,id',
            'programme_id'  => 'required|integer|exists:programmes,id',
            'act_seq'       => 'required|integer|min:1',
            'timeline_sem'  => 'required|integer|min:1',
            'timeline_week' => 'required|integer|min:1|max:52',
            'init_status'   => 'required|integer|in:1,2',
            'is_haveEva'    => 'required|boolean',
            'material'      => 'nullable|string|max:255',
        ], [], [
            'activity_id'   => 'activity',
            'programme_id'  => 'programme',
            'act_seq'       => 'activity sequence',
            'timeline_sem'  => 'semester timeline',
            'timeline_week' => 'week timeline',
            'init_status'   => 'initial status',
            'is_haveEva'    => 'evaluation',
            'material'      => 'material',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'addModal');
        }
        try {
            $validated = $validator->validated();
            Procedure::create([
                'activity_id'   => $validated['activity_id'],
                'programme_id'  => $validated['programme_id'],
                'act_seq'       => $validated['act_seq'],
                'timeline_sem'  => $validated['timeline_sem'],
                'timeline_week' => $validated['timeline_week'],
                'init_status'   => $validated['init_status'],
                'is_haveEva'    => $validated['is_haveEva'],
                'material'      => $validated['material'],
            ]);

            return back()->with('success', 'Procedure added successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error adding procedure.');
        }
    }

    public function updateProcedure(Request $req, $actID, $progID)
    {
        $actID = decrypt($actID);
        $progID = decrypt($progID);
        dd($req->all());

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
                ->with('modal', 'updateModal-' . $actID . '-' . $progID);
        }

        try {
            $validated = $validator->validated();

            Procedure::where('activity_id', $actID)->where('programme_id', $progID)->update([
                'sem_startdate' => $validated['sem_startdate_up'],
                'sem_enddate' => $validated['sem_enddate_up'],
                'sem_status' => $validated['sem_status']
            ]);

            return back()->with('success', 'Procedure updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating procedure.');
        }
    }

    public function deleteProcedure($actID, $progID)
    {
        try {
            $actID = decrypt($actID);
            $progID = decrypt($progID);
            Procedure::where('activity_id', $actID)->where('programme_id', $progID)->delete();
            return back()->with('success', 'Procedure deleted successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error deleting procedure.');
        }
    }
}
