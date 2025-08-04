<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\Activity;
use App\Models\Document;
use App\Models\FormField;
use App\Models\Procedure;
use App\Models\Programme;
use Illuminate\Support\Str;
use App\Models\ActivityForm;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class SOPController extends Controller
{

    /* Activity Setting (Activity + Document) [Checked : 04/04/2025] */
    public function activitySetting()
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
            $data = Activity::withCount('documents')->get();
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
            'act_name' => 'required|string|unique:activities,act_name',
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
                'message' => 'Oops! Error adding activity: ' . $e->getMessage()
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
                'message' => 'Oops! Error updating activity: ' . $e->getMessage()
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
                'message' => 'Oops! Error deleting activity: ' . $e->getMessage()
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
                    'message' => 'Oops! Error viewing documents: ' . $e->getMessage()
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
            return response()->json([
                'success' => true,
                'document' => $document,
                'message' => 'Document added successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Oops! Error adding documents: ' . $e->getMessage()
            ], 500);
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
            return response()->json([
                'success' => true,
                'document' => $document,
                'message' => 'Document updated successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Oops! Error updating documents: ' . $e->getMessage()

            ], 500);
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
                'message' => 'Oops! Error deleting document: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Procedure Setting [Checked : 04/04/2025] */
    public function procedureSetting(Request $req)
    {
        try {
            if ($req->ajax()) {

                $data = DB::table('procedures as a')
                    ->join('activities as b', 'b.id', '=', 'a.activity_id')
                    ->join('programmes as c', 'c.id', '=', 'a.programme_id')
                    ->select('a.*', 'b.*', 'c.*')
                    ->selectRaw('CONCAT(c.prog_code, " (", c.prog_mode, ")") as prog_code_mode')
                    ->orderBy('prog_code_mode')
                    ->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('programme', function ($row) {
                    return $row->prog_code . ' (' . $row->prog_mode . ')';
                });

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

                $table->addColumn('material', function ($row) {
                    $material = '';
                    if ($row->material != null) {
                        $material =
                            '
                            <a href="' . URL::signedRoute('view-material-get', ['filename' => Crypt::encrypt($row->material)]) . '" target="_blank" class="btn">
                                <i class="fas fa-file-pdf f-20 text-danger"></i>
                            </a>
                  
                        ';
                    }
                    return $material;
                });

                $table->addColumn('action', function ($row) {
                    $isReferenced = false;
                    $buttonEdit =
                        '
                            <a href="javascript: void(0)" class="avtar avtar-xs btn-light-primary mb-2" data-bs-toggle="modal"
                                data-bs-target="#updateModal-' . $row->activity_id . '-' . $row->programme_id . '">
                                <i class="ti ti-edit f-20"></i>
                            </a>
                        ';

                    if (!$isReferenced) {
                        $buttonRemove =
                            '
                                <a href="javascript: void(0)" class="avtar avtar-xs  btn-light-danger mb-2" data-bs-toggle="modal"
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

                $table->rawColumns(['programme', 'is_haveEva', 'init_status', 'material', 'action']);

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
        $validator = Validator::make($req->all(), [
            'activity_id'       => 'required|integer|exists:activities,id',
            'programme_id'      => 'required|integer|exists:programmes,id',
            'act_seq'           => 'required|integer|min:1',
            'timeline_sem'      => 'required|integer|min:1',
            'timeline_week'     => 'required|integer|min:1|max:52',
            'init_status'       => 'required|integer|in:1,2',
            'is_repeatable'     => 'required|integer|in:0,1',
            'is_haveEva'        => 'required|boolean|in:0,1',
            'is_haveCorrection' => 'required|boolean|in:0,1',
            'material'          => 'nullable|file|mimes:pdf|max:5120',
        ], [], [
            'activity_id'       => 'activity',
            'programme_id'      => 'programme',
            'act_seq'           => 'activity sequence',
            'timeline_sem'      => 'semester timeline',
            'timeline_week'     => 'week timeline',
            'init_status'       => 'initial status',
            'is_repeatable'     => 'repeatable',
            'is_haveEva'        => 'evaluation',
            'is_haveCorrection' => 'correction',
            'material'          => 'material',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'addModal');
        }
        try {

            $validated = $validator->validated();
            $fileName = "";
            $filePath = "";
            $checkExists = Procedure::where('activity_id', $validated['activity_id'])
                ->where('programme_id', $validated['programme_id'])
                ->exists();

            if ($checkExists) {
                return back()->with('error', 'Procedure already exists for this activity and programme.');
            }

            if ($req->hasFile('material')) {
                // 1 - GET THE DATA
                $activity = Activity::findOrFail($validated['activity_id']);
                $programme = Programme::findOrFail($validated['programme_id']);

                // 2 - CALL THE DATA
                $act_name = $activity->act_name ?? 'N/A';
                $prog_code = $programme->prog_code ?? 'N/A';
                $prog_mode = $programme->prog_mode ?? 'N/A';

                // 3 - SET & DECLARE FILE ROUTE
                $fileName = Str::upper(str_replace(' ', '', $act_name) . '_' . $prog_code . '(' . $prog_mode . ')') . '.pdf';
                $filePath = "Activity/{$activity->act_name}/Material/";

                // 4 - SAVE THE FILE

                $file = $req->file('material');
                $file->storeAs($filePath, $fileName, 'public');
            }

            Procedure::create([
                'activity_id'   => $validated['activity_id'],
                'programme_id'  => $validated['programme_id'],
                'act_seq'       => $validated['act_seq'],
                'timeline_sem'  => $validated['timeline_sem'],
                'timeline_week' => $validated['timeline_week'],
                'init_status'   => $validated['init_status'],
                'is_repeatable'   => $validated['is_repeatable'],
                'is_haveEva'    => $validated['is_haveEva'],
                'is_haveCorrection' => $validated['is_haveCorrection'],
                'material'      => $filePath . $fileName,
            ]);

            return back()->with('success', 'Procedure added successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error adding procedure: ' . $e->getMessage());
        }
    }

    public function updateProcedure(Request $req, $actID, $progID)
    {
        $actID = decrypt($actID);
        $progID = decrypt($progID);

        $validator = Validator::make($req->all(), [
            'act_seq_up'            => 'required|integer|min:1',
            'timeline_sem_up'       => 'required|integer|min:1',
            'timeline_week_up'      => 'required|integer|min:1|max:52',
            'init_status_up'        => 'required|integer|in:1,2',
            'is_repeatable_up'      => 'required|integer|in:0,1',
            'is_haveEva_up'         => 'required|boolean|in:0,1',
            'is_haveCorrection_up'  => 'required|boolean|in:0,1',
            'material_up'           => 'nullable|file|mimes:pdf|max:5120',
        ], [], [
            'act_seq_up'            => 'activity sequence',
            'timeline_sem_up'       => 'semester timeline',
            'timeline_week_up'      => 'week timeline',
            'init_status_up'        => 'initial status',
            'is_repeatable_up'      => 'repeatable',
            'is_haveEva_up'         => 'evaluation',
            'is_haveCorrection_up'  => 'correction',
            'material_up'           => 'material',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'updateModal-' . $actID . '-' . $progID);
        }

        try {

            $validated = $validator->validated();

            if ($req->hasFile('material_up')) {

                // 1 - GET THE DATA
                $activity = Activity::findOrFail($actID);
                $programme = Programme::findOrFail($progID);

                // 2 - CALL THE DATA
                $act_name = $activity->act_name ?? 'N/A';
                $prog_code = $programme->prog_code ?? 'N/A';
                $prog_mode = $programme->prog_mode ?? 'N/A';

                // 3 - SET & DECLARE FILE ROUTE
                $fileName = Str::upper(str_replace(' ', '', $act_name) . '_' . $prog_code . '(' . $prog_mode . ')') . '.pdf';
                $filePath = "Activity/{$activity->act_name}/Material/";

                // 4 - CHECK FOR OLD FILE
                $oldMaterial = Procedure::where('activity_id', $actID)->where('programme_id', $progID)->value('material');
                if ($oldMaterial && Storage::exists($oldMaterial)) {
                    Storage::delete($oldMaterial);
                }

                // 5 - SAVE NEW FILE
                $filepath = $req->file('material_up')->storeAs($filePath, $fileName, 'public');
                Procedure::where('activity_id', $actID)->where('programme_id', $progID)->update([
                    'material' => $filepath
                ]);
            }

            Procedure::where('activity_id', $actID)->where('programme_id', $progID)->update([
                'act_seq'       => $validated['act_seq_up'],
                'timeline_sem'  => $validated['timeline_sem_up'],
                'timeline_week' => $validated['timeline_week_up'],
                'init_status'   => $validated['init_status_up'],
                'is_repeatable'   => $validated['is_repeatable_up'],
                'is_haveEva'    => $validated['is_haveEva_up'],
                'is_haveCorrection' => $validated['is_haveCorrection_up'],
            ]);

            return back()->with('success', 'Procedure updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating procedure: ' . $e->getMessage());
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
            return back()->with('error', 'Oops! Error deleting procedure: ' . $e->getMessage());
        }
    }

    public function viewMaterialFile($filename)
    {
        $filename = Crypt::decrypt($filename);
        $path = storage_path("app/public/{$filename}");

        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }

        return response()->file($path);
    }

    /* Form Setting [Checked : 9/5/2025] */
    public function formSetting(Request $req)
    {
        $data = DB::table('activities as a')
            ->leftJoin('activity_forms as b', 'a.id', '=', 'b.activity_id')
            ->select(
                'a.act_name',
                'b.id as af_id',
                'b.af_title as form_title',
                'b.af_target as form_target',
                'b.af_status as form_status',
                'a.id as activity_id',
                DB::raw('COUNT(b.id) as form_count')
            )
            ->groupBy(
                'a.act_name',
                'b.id',
                'b.af_title',
                'b.af_target',
                'b.af_status',
                'a.id'
            )
            ->orderByRaw('COUNT(b.id) IS NULL ASC, COUNT(b.id) DESC, a.act_name ASC');

        if ($req->ajax()) {

            $data = $data->get();

            $table = DataTables::of($data);

            $table->addColumn('form_target', function ($row) {
                if (is_null($row->form_target)) {
                    return null;
                }
                $target = '';

                if ($row->form_target == 1) {
                    $target = '<span class="badge bg-yellow-900">' . 'Submission' . '</span>';
                } elseif ($row->form_target == 2) {
                    $target = '<span class="badge bg-yellow-700">' . 'Correction' . '</span>';
                } elseif ($row->form_target == 3) {
                    $target = '<span class="badge bg-yellow-300">' . 'Nomination' . '</span>';
                } elseif ($row->form_target == 4) {
                    $target = '<span class="badge bg-yellow-600">' . 'Evaluation - Chairman' . '</span>';
                } elseif ($row->form_target == 5) {
                    $target = '<span class="badge bg-yellow-500">' . 'Evaluation - Examiner/Panel' . '</span>';
                } else {
                    $target = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                }

                return $target;
            });

            $table->addColumn('form_status', function ($row) {
                if (is_null($row->form_status)) {
                    return null;
                }
                $status = '';

                if ($row->form_status == 1) {
                    $status = '<span class="badge bg-success">' . 'Active' . '</span>';
                } elseif ($row->form_status == 2) {
                    $status = '<span class="badge bg-secondary">' . 'Inactive' . '</span>';
                } else {
                    $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                }

                return $status;
            });

            $table->addColumn('action', function ($row) {
                if (is_null($row->af_id)) {
                    return null;
                }
                $html =
                    '
                    <div class="d-flex justify-content-end align-items-center gap-2">
                        <a href="' . route('form-editor', ['formID' => Crypt::encrypt($row->af_id), 'afTarget' => $row->form_target]) . '" class="avtar avtar-xs btn-light-primary">
                            <i class="ti ti-edit f-20"></i>
                        </a>
                        <a href="javascript: void(0)" class="avtar avtar-xs  btn-light-danger" data-bs-toggle="modal"
                            data-bs-target="#deleteModal-' . $row->af_id . '">
                            <i class="ti ti-trash f-20"></i>
                        </a>
                    </div>
                    ';
                return $html;
            });

            $table->rawColumns(['form_target', 'form_status', 'action']);

            return $table->make(true);
        }
        try {
            return view('staff.sop.form-setting', [
                'title' => 'Form Setting',
                'acts' => Activity::all(),
                'actForms' => ActivityForm::all(),
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    public function addActivityForm(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'actid' => 'required|integer|exists:activities,id',
            'formTitle'  => 'required',
            'formTarget' => 'required|in:1,2,3,4,5',
            'formStatus' => 'required',
        ], [], [
            'actid' => 'activity',
            'formTitle'  => 'form title',
            'formTarget' => 'form target',
            'formStatus' => 'form status',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        try {

            $validated = $validator->validated();

            $checkExists = ActivityForm::where([
                ['activity_id', $validated['actid']],
                ['af_target', $validated['formTarget']],
            ])->exists();
            $message = '';

            if ($checkExists) {
                ActivityForm::where('id', $req->af_id)->update([
                    'af_title' => $validated['formTitle'],
                    'af_target' => $validated['formTarget'],
                    'af_status' => $validated['formStatus'],
                    'activity_id' => $validated['actid'],
                ]);

                $message = 'Form updated successfully.';
            } else {
                ActivityForm::create([
                    'af_title' => $validated['formTitle'],
                    'af_target' => $validated['formTarget'],
                    'af_status' => $validated['formStatus'],
                    'activity_id' => $validated['actid'],
                ]);
                $message = 'Form added successfully.';
            }

            $activityForm = ActivityForm::where('activity_id', $validated['actid'])->where('af_target', $validated['formTarget'])->first();

            return response()->json([
                'success' => true,
                'message' => $message,
                'activityForm' => $activityForm,
                'url' => route('form-editor', ['formID' => Crypt::encrypt($activityForm->id), 'afTarget' => $activityForm->af_target]),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteActivityForm($afID)
    {
        try {
            $afID = decrypt($afID);
            FormField::where('af_id', $afID)->delete();
            ActivityForm::where('id', $afID)->delete();
            return back()->with('success', 'Form and all related setting deleted successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error deleting activity forms: ' . $e->getMessage());
        }
    }

    /* Form Editor [Checked : 9/5/2025] [Notes : Require Enhancement and Update] */
    public function formGetStarted(Request $req)
    {
        try {
            $af_id = $req->af_id;
            $form_target = $req->form_target;

            // ================================
            // Submission Form (form_target == 1)
            // ================================
            if ($form_target == 1 || $form_target == 2) {
                $fields = [
                    [
                        'ff_category' => 3,
                        'ff_label' => 'Student Details',
                        'ff_order' => 1
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Student Name',
                        'ff_order' => 2,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'student_name'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Matric No.',
                        'ff_order' => 3,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'student_matricno'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Programme Of Study',
                        'ff_order' => 4,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'prog_code [prog_mode]'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Title Of Research',
                        'ff_order' => 5,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'student_titleOfResearch'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Main Supervisor',
                        'ff_order' => 6,
                        'ff_component_required' => 1,
                        'ff_table' => 'staff',
                        'ff_datakey' => 'staff_name',
                        'ff_extra_datakey' => 'supervision_role',
                        'ff_extra_condition' => '1'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Co Supervisor',
                        'ff_order' => 7,
                        'ff_component_required' => 1,
                        'ff_table' => 'staff',
                        'ff_datakey' => 'staff_name',
                        'ff_extra_datakey' => 'supervision_role',
                        'ff_extra_condition' => '2'
                    ],
                    [
                        'ff_category' => 3,
                        'ff_label' => 'Approvals',
                        'ff_order' => 9
                    ],
                    [
                        'ff_category' => 6,
                        'ff_label' => 'Student Signature',
                        'ff_order' => 10,
                        'ff_component_required' => 1,
                        'ff_signature_role' => 1,
                        'ff_signature_key' => 'student_signature',
                        'ff_signature_date_key' => 'student_signature_date'
                    ],
                    [
                        'ff_category' => 6,
                        'ff_label' => 'Supervisor Signature',
                        'ff_order' => 11,
                        'ff_component_required' => 1,
                        'ff_signature_role' => 2,
                        'ff_signature_key' => 'sv_signature',
                        'ff_signature_date_key' => 'sv_signature_date'
                    ]
                ];
            }

            // ================================
            // Nomination Form (form_target == 3)
            // ================================
            elseif ($form_target == 3) {
                $fields = [
                    [
                        'ff_category' => 3,
                        'ff_label' => 'Student Details',
                        'ff_order' => 1
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Student Name',
                        'ff_order' => 2,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'student_name'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Matric No.',
                        'ff_order' => 3,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'student_matricno'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Programme Of Study',
                        'ff_order' => 4,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'prog_code [prog_mode]'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Title Of Research',
                        'ff_order' => 5,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'student_titleOfResearch'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Main Supervisor',
                        'ff_order' => 6,
                        'ff_component_required' => 1,
                        'ff_table' => 'staff',
                        'ff_datakey' => 'staff_name',
                        'ff_extra_datakey' => 'supervision_role',
                        'ff_extra_condition' => '1'
                    ],
                    [
                        'ff_category' => 3,
                        'ff_label' => 'Nomination Details',
                        'ff_order' => 7
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'Examiner 1',
                        'ff_order' => 8,
                        'ff_component_type' => 'select',
                        'ff_component_required' => 1,
                        'ff_component_required_role' => 1,
                        'ff_value_options' => json_encode(['table' => 'staff', 'column' => 'staff_name'])
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'Examiner 2',
                        'ff_order' => 9,
                        'ff_component_type' => 'select',
                        'ff_component_required' => 1,
                        'ff_component_required_role' => 1,
                        'ff_value_options' => json_encode(['table' => 'staff', 'column' => 'staff_name'])
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'Examiner 3',
                        'ff_order' => 10,
                        'ff_component_type' => 'select',
                        'ff_component_required' => 1,
                        'ff_component_required_role' => 1,
                        'ff_value_options' => json_encode(['table' => 'staff', 'column' => 'staff_name'])
                    ],
                    [
                        'ff_category' => 3,
                        'ff_label' => 'Result JKPPS - Committee Use',
                        'ff_order' => 11
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'Chairman',
                        'ff_order' => 12,
                        'ff_component_type' => 'select',
                        'ff_component_required' => 1,
                        'ff_component_required_role' => 2,
                        'ff_value_options' => json_encode(['table' => 'staff', 'column' => 'staff_name'])
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'Final Examiner 1',
                        'ff_order' => 13,
                        'ff_component_type' => 'select',
                        'ff_component_required' => 1,
                        'ff_component_required_role' => 2,
                        'ff_value_options' => json_encode(['table' => 'staff', 'column' => 'staff_name'])
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'Final Examiner 2',
                        'ff_order' => 14,
                        'ff_component_type' => 'select',
                        'ff_component_required' => 1,
                        'ff_component_required_role' => 2,
                        'ff_value_options' => json_encode(['table' => 'staff', 'column' => 'staff_name'])
                    ],
                    [
                        'ff_category' => 6,
                        'ff_label' => 'Main Supervisor Signature',
                        'ff_order' => 15,
                        'ff_component_required' => 1,
                        'ff_signature_role' => 2,
                        'ff_signature_key' => 'sv_signature',
                        'ff_signature_date_key' => 'sv_signature_date'
                    ]
                ];
            }

            // =================================
            // Evaluation Chairman (form_target == 4)
            // =================================
            elseif ($form_target == 4) {
                $fields = [
                    [
                        'ff_category' => 3,
                        'ff_label' => 'Student Details',
                        'ff_order' => 1
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Student Name',
                        'ff_order' => 2,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'student_name'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Matric No.',
                        'ff_order' => 3,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'student_matricno'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Programme Of Study',
                        'ff_order' => 4,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'prog_code [prog_mode]'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Title Of Research',
                        'ff_order' => 5,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'student_titleOfResearch'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Main Supervisor',
                        'ff_order' => 6,
                        'ff_component_required' => 1,
                        'ff_table' => 'staff',
                        'ff_datakey' => 'staff_name',
                        'ff_extra_datakey' => 'supervision_role',
                        'ff_extra_condition' => '1'
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'Decision',
                        'ff_order' => 8,
                        'ff_component_type' => 'radio',
                        'ff_component_required' => 1,
                        'ff_value_options' => json_encode([
                            "Pass",
                            "Pass with minor correction",
                            "Pass with major correction",
                            "Resubmit / Represent",
                            "Fail"
                        ])
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'Maximum Duration for Proposal Defence Corrections',
                        'ff_order' => 9,
                        'ff_component_type' => 'radio',
                        'ff_component_required' => 2,
                        'ff_value_options' => json_encode(["Within 3 month", "Within 6 month"])
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'Details Of Amendments Of The Research Proposal',
                        'ff_order' => 10,
                        'ff_component_type' => 'longtextarea',
                        'ff_component_required' => 2
                    ],
                    [
                        'ff_category' => 4,
                        'ff_label' => '<h4>Score</h4>',
                        'ff_order' => 11,
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'Examiner 1 Score',
                        'ff_order' => 12,
                        'ff_component_type' => 'text',
                        'ff_component_required' => 1
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'Examiner 2 Score',
                        'ff_order' => 13,
                        'ff_component_type' => 'text',
                        'ff_component_required' => 1
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'Average Score',
                        'ff_order' => 14,
                        'ff_component_type' => 'text',
                        'ff_component_required' => 1
                    ],
                    [
                        'ff_category' => 4,
                        'ff_label' => '<h4>Student</h4>',
                        'ff_order' => 15,
                    ],
                    [
                        'ff_category' => 6,
                        'ff_label' => 'Student Signature',
                        'ff_order' => 16,
                        'ff_component_required' => 1,
                        'ff_signature_role' => 1,
                        'ff_signature_key' => 'student_signature',
                        'ff_signature_date_key' => 'student_signature_date'
                    ],
                    [
                        'ff_category' => 4,
                        'ff_label' => '<h4>Chairman & Examiner Member</h4>',
                        'ff_order' => 17,
                    ],
                    [
                        'ff_category' => 6,
                        'ff_label' => 'Chairman',
                        'ff_order' => 18,
                        'ff_component_required' => 1,
                        'ff_signature_role' => 7,
                        'ff_signature_key' => 'chairman_signature',
                        'ff_signature_date_key' => 'chairman_signature_date'
                    ],
                    [
                        'ff_category' => 6,
                        'ff_label' => 'Examiner 1',
                        'ff_order' => 19,
                        'ff_component_required' => 1,
                        'ff_signature_role' => 8,
                        'ff_signature_key' => 'examiner_1_signature',
                        'ff_signature_date_key' => 'examiner_1_signature_date'
                    ],
                    [
                        'ff_category' => 6,
                        'ff_label' => 'Examiner 2',
                        'ff_order' => 20,
                        'ff_component_required' => 1,
                        'ff_signature_role' => 8,
                        'ff_signature_key' => 'examiner_2_signature',
                        'ff_signature_date_key' => 'examiner_2_signature_date'
                    ],
                ];
            }

            // ===================================
            // Evaluation Examiner/Panel (form_target == 5)
            // ===================================
            elseif ($form_target == 5) {
                $fields = [
                    [
                        'ff_category' => 3,
                        'ff_label' => 'Student Details',
                        'ff_order' => 1
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Student Name',
                        'ff_order' => 2,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'student_name'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Matric No.',
                        'ff_order' => 3,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'student_matricno'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Programme Of Study',
                        'ff_order' => 4,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'prog_code [prog_mode]'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Title Of Research',
                        'ff_order' => 5,
                        'ff_component_required' => 1,
                        'ff_table' => 'students',
                        'ff_datakey' => 'student_titleOfResearch'
                    ],
                    [
                        'ff_category' => 2,
                        'ff_label' => 'Main Supervisor',
                        'ff_order' => 6,
                        'ff_component_required' => 1,
                        'ff_table' => 'staff',
                        'ff_datakey' => 'staff_name',
                        'ff_extra_datakey' => 'supervision_role',
                        'ff_extra_condition' => '1'
                    ],
                    [
                        'ff_category' => 4,
                        'ff_label' => '<h4>C1: Student demonstrates ability to state problem statements and research objectives clearly and well-conceptualized.</h4><ul><li><strong>Poor [0-2]:</strong> Demonstrates no or little ability to describe research objective and problem.</li><li><strong>Fair [3-5]:</strong> Demonstrates a fair ability describe research objective and problem.</li><li><strong>Good [6-7]:</strong> Demonstrates a good ability to describe research objective and problem</li><li><strong>Excellent [8-10]:</strong> Demonstrates an excellent ability describe research objective and problem.</li></ul>',
                        'ff_order' => 9,
                        'ff_component_required' => 1,
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'C1 : Evaluation Score',
                        'ff_order' => 10,
                        'ff_component_type' => 'select',
                        'ff_component_required' => 1,
                        'ff_value_options' => json_encode(["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10"]),
                        'ff_append_text' => '[EL] Evaluation Level - 0 to 10'
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'C1: Marks (EL x W)',
                        'ff_order' => 11,
                        'ff_component_type' => 'text',
                        'ff_component_required' => 1,
                        'ff_append_text' => '[W] Weightage = 2'
                    ],
                    [
                        'ff_category' => 4,
                        'ff_label' => '<h4>C2: Student demonstrates ability to integrate relevant literature.</h4><ul><li><strong>Poor [0-2]:</strong> Demonstrates no or little understanding of relevant literature.</li><li><strong>Fair [3-5]:</strong> Demonstrates a fair understanding of relevant literature.</li><li><strong>Good [6-7]:</strong> Demonstrates a good understanding of relevant literature</li><li><strong>Excellent [8-10]:</strong> Demonstrates an excellent understanding of relevant literature.</li></ul>',
                        'ff_order' => 12,
                        'ff_component_required' => 1,
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'C2 : Evaluation Score',
                        'ff_order' => 13,
                        'ff_component_type' => 'select',
                        'ff_component_required' => 1,
                        'ff_value_options' => json_encode(["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10"]),
                        'ff_append_text' => '[EL] Evaluation Level - 0 to 10'
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'C2: Marks (EL x W)',
                        'ff_order' => 14,
                        'ff_component_type' => 'text',
                        'ff_component_required' => 1,
                        'ff_append_text' => '[W] Weightage = 3'
                    ],
                    [
                        'ff_category' => 4,
                        'ff_label' => '<h4>C3: Student demonstrates knowledge of appropriate research strategies and methods.</h4><ul><li><strong>Poor [0-2]:</strong> Demonstrates no or little knowledge of research and methods.</li><li><strong>Fair [3-5]:</strong> Demonstrates a fair knowledge of research and methods.</li><li><strong>Good [6-7]:</strong> Demonstrates a good knowledge of research and methods</li><li><strong>Excellent [8-10]:</strong> Demonstrates an excellent knowledge of research and methods.</li></ul>',
                        'ff_order' => 15,
                        'ff_component_required' => 1,
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'C3 : Evaluation Score',
                        'ff_order' => 16,
                        'ff_component_type' => 'select',
                        'ff_component_required' => 1,
                        'ff_value_options' => json_encode(["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10"]),
                        'ff_append_text' => '[EL] Evaluation Level - 0 to 10'
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'C3: Marks (EL x W)',
                        'ff_order' => 17,
                        'ff_component_type' => 'text',
                        'ff_component_required' => 1,
                        'ff_append_text' => '[W] Weightage = 4'
                    ],
                    [
                        'ff_category' => 4,
                        'ff_label' => '<h4>C4: Student demonstrates ability to orally present problem, objectives, approach and plan for dissertation research.</h4><ul><li><strong>Poor [0-2]:</strong> Demonstrates no or little ability to present orally the proposed dissertation research.</li><li><strong>Fair [3-5]:</strong> Demonstrates a fair ability to present orally the proposed dissertation research.</li><li><strong>Good [6-7]:</strong> Demonstrates a good ability to present orally the proposed dissertation research</li><li><strong>Excellent [8-10]:</strong> Demonstrates an excellent ability to present orally the proposed dissertation research.</li></ul>',
                        'ff_order' => 18,
                        'ff_component_required' => 1,
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'C4 : Evaluation Score',
                        'ff_order' => 19,
                        'ff_component_type' => 'select',
                        'ff_component_required' => 1,
                        'ff_value_options' => json_encode(["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10"]),
                        'ff_append_text' => '[EL] Evaluation Level - 0 to 10'
                    ],
                    [
                        'ff_category' => 1,
                        'ff_label' => 'C4: Marks (EL x W)',
                        'ff_order' => 20,
                        'ff_component_type' => 'text',
                        'ff_component_required' => 1,
                        'ff_append_text' => '[W] Weightage = 1'
                    ],
                    [
                        'ff_category' => 6,
                        'ff_label' => 'Examiner',
                        'ff_order' => 21,
                        'ff_component_required' => 1,
                        'ff_signature_role' => 8,
                        'ff_signature_key' => 'examiner_signature',
                        'ff_signature_date_key' => 'examiner_signature_date'
                    ]
                ];
            }


            // ============ INSERT TO DATABASE ============
            foreach ($fields as $fieldData) {
                $fieldData['af_id'] = $af_id;
                $fieldData['created_at'] = now();
                $fieldData['updated_at'] = now();
                FormField::insert($fieldData);
            }

            return back()->with('success', 'Form template has been generated successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error getting started: ' . $e->getMessage());
        }
    }


    public function formEditor($formID, $afTarget)
    {
        try {
            $formID = decrypt($formID);
            $formdata = ActivityForm::where('id', $formID)->where('af_target', $afTarget)->first();
            if (!$formdata) {
                return abort(404, 'Form not found.');
            }

            $actdata = Activity::where('id', $formdata->activity_id)->first();

            if (!$actdata) {
                return abort(404, 'Activity not found.');
            }

            return view('staff.sop.form-editor', [
                'title' => 'Form Editor',
                'formdata' => $formdata,
                'acts' => $actdata,
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    public function previewActivityDocument(Request $req)
    {
        try {
            $act = Activity::where('id', $req->actid)->first();
            $actform = ActivityForm::where('id',  $req->af_id)->first();
            $formfield = FormField::where('af_id',  $req->af_id)->orderby('ff_order')->get();
            $signatures = $formfield->where('ff_category', 6);
            $faculty = Faculty::where('fac_status', 3)->first();
            $pdf = Pdf::loadView('staff.sop.template.activity-document', [
                'title' => $actform->af_title,
                'act' => $act,
                'form_title' => $req->title,
                'actform' => $actform,
                'formfields' => $formfield,
                'faculty' => $faculty,
                'signatures' => $signatures

            ]);

            $pdf->set_option('isPhpEnabled', true);
            $pdf->set_option('isHtml5ParserEnabled', true);
            $pdf->set_option('isFontSubsettingEnabled', true);
            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream(strtoupper(str_replace(' ', '_', $actform->af_title)) . '.pdf');
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    // public function previewActivityDocumentbyHTMLDynamic(Request $req)
    // {
    //     try {

    //         $act = Activity::where('id', $req->actid)->first();
    //         $actform = ActivityForm::where('id',  $req->af_id)->first();
    //         $formfield = FormField::where('af_id',  $req->af_id)->orderby('ff_order')->get();
    //         $signatures = $formfield->where('ff_category', 6);
    //         $faculty = Faculty::where('fac_status', 3)->first();
    //         $student = Student::where('id', $req->studentid)->first();

    //         $html = view('staff.sop.template.activity-document-dynamic', [
    //             'title' => $act->act_name . " Document",
    //             'act' => $act,
    //             'form_title' => $req->title,
    //             'actform' => $actform,
    //             'formfields' => $formfield,
    //             'faculty' => $faculty,
    //             'signatures' => $signatures,
    //             'student' => $student
    //         ])->render();

    //         return response()->json(['html' => $html]);

    //     } catch (Exception $e) {
    //         return response()->json(['error' => $e->getMessage()]);
    //     }
    // }

    public function previewActivityDocumentbyHTML(Request $req)
    {
        try {

            $act = Activity::where('id', $req->actid)->first();
            $actform = ActivityForm::where('id',  $req->af_id)->first();
            $formfield = FormField::where('af_id',  $req->af_id)->orderby('ff_order')->get();
            $signatures = $formfield->where('ff_category', 6);
            $faculty = Faculty::where('fac_status', 3)->first();

            return view('staff.sop.template.activity-document', [
                'title' => $act->act_name . " Document",
                'act' => $act,
                'form_title' => $req->title,
                'actform' => $actform,
                'formfields' => $formfield,
                'faculty' => $faculty,
                'signatures' => $signatures

            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getActivityFormData(Request $req)
    {
        try {
            $actform = ActivityForm::where('id', $req->af_id)->where('activity_id', $req->actid)->first();

            if (!$actform) {
                return response()->json([
                    'success' => false,
                    'message' => 'No form found for this activity.',
                ]);
            }

            return response()->json([
                'success' => true,
                'formID' => $actform->id,
                'formTitle' => $actform->af_title,
                'formTarget' => $actform->af_target,
                'formStatus' => $actform->af_status,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting the activity form data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function addFormField(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'ff_label' => 'required|string',
            'ff_category' => 'required|string',
            'ff_component_type' => 'nullable|string',
            'ff_placeholder' => 'nullable|string',
            'ff_component_required' => 'nullable|in:1,2',
            'ff_component_required_role' => 'nullable|in:0,1,2,3,4,5',
            'ff_value_options' => 'nullable|json',
            'ff_append_text' => 'nullable|string',
            'ff_table' => 'nullable|string',
            'ff_datakey' => 'nullable|string',
            'ff_extra_datakey' => 'nullable|string',
            'ff_extra_condition' => 'nullable|string',
            'ff_signature_role' => 'nullable|integer',
            'actid' => 'required|integer|exists:activities,id',
            'af_id' => 'required|integer|exists:activity_forms,id',
        ], [], [
            'ff_label' => 'label',
            'ff_category' => 'category',
            'ff_component_type' => 'component type',
            'ff_placeholder' => 'placeholder',
            'ff_component_required' => 'required status',
            'ff_component_required_role' => 'required role',
            'ff_value_options' => 'value options',
            'ff_append_text' => 'append text',
            'ff_table' => 'source table',
            'ff_datakey' => 'data key',
            'ff_extra_datakey' => 'extra data key',
            'ff_extra_condition' => 'extra condition',
            'ff_signature_role' => 'user role',
            'actid' => 'Activity',
            'af_id' => 'Activity Form',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $af_id = $validated['af_id'];
            $checkExists = FormField::where('ff_label', $validated['ff_label'])->where('af_id', $af_id)->exists();

            if ($checkExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field label already exists within the same form. Please make the field label unique.',
                ], 200);
            }

            // Get the next order number
            // $af_count = FormField::where('af_id', $af_id)->count();
            $nextOrder = 0;

            // CHECK WHETHER USER ROLE EXIST IN ff_signature_role
            $signature_key = null;
            $signature_date_key = null;
            if ($validated['ff_signature_role'] != null) {
                $userRole = $validated['ff_signature_role'];

                if ($userRole == 1) {
                    $signature_key = 'student_signature';
                    $signature_date_key = 'student_signature_date';
                } else if ($userRole == 2) {
                    $signature_key = 'sv_signature';
                    $signature_date_key = 'sv_signature_date';
                } else if ($userRole == 3) {
                    $signature_key = 'cosv_signature';
                    $signature_date_key = 'cosv_signature_date';
                } else if ($userRole == 4) {
                    $signature_key = 'comm_signature';
                    $signature_date_key = 'comm_signature_date';
                } else if ($userRole == 5) {
                    $signature_key = 'deputy_dean_signature';
                    $signature_date_key = 'deputy_dean_signature_date';
                } else if ($userRole == 6) {
                    $signature_key = 'dean_signature';
                    $signature_date_key = 'dean_signature_date';
                } else if ($userRole == 7) {
                    $signature_key = 'chairman_signature';
                    $signature_date_key = 'chairman_signature_date';
                } else if ($userRole == 8) {
                    $name = strtolower(str_replace(' ', '_', $validated['ff_label']));
                    $signature_key =  $name . '_signature';
                    $signature_date_key = $name . '_signature_date';
                }
            }


            $formfield = FormField::create([
                'ff_label' => $validated['ff_label'],
                'ff_order' => $nextOrder,
                'ff_category' => $validated['ff_category'],
                'ff_component_type' => $validated['ff_component_type'],
                'ff_placeholder' => $validated['ff_placeholder'] ?? null,
                'ff_component_required' => $validated['ff_component_required'] ?? '2',
                'ff_component_required_role' => $validated['ff_component_required_role'] ?? '0',
                'ff_value_options' => $validated['ff_value_options'] ?? null,
                'ff_append_text' => $validated['ff_append_text'] ?? null,
                'ff_table' => $validated['ff_table'] ?? null,
                'ff_datakey' => $validated['ff_datakey'],
                'ff_extra_datakey' => $validated['ff_extra_datakey'] ?? null,
                'ff_extra_condition' => $validated['ff_extra_condition'] ?? null,
                'ff_signature_role' => $validated['ff_signature_role'] ?? null,
                'ff_signature_key' => $signature_key,
                'ff_signature_date_key' => $signature_date_key,
                'af_id' => $af_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Field added successfully.',
                'formfield' => $formfield,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding the form field: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateFormField(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'ff_id' => 'required|integer|exists:form_fields,id',
            'ff_label' => 'required|string',
            'ff_component_type' => 'nullable|string',
            'ff_placeholder' => 'nullable|string',
            'ff_component_required' => 'nullable|in:1,2',
            'ff_component_required_role' => 'nullable|in:0,1,2,3,4,5',
            'ff_value_options' => 'nullable|json',
            'ff_append_text' => 'nullable|string',
            'ff_table' => 'nullable|string',
            'ff_datakey' => 'nullable|string',
            'ff_extra_datakey' => 'nullable|string',
            'ff_extra_condition' => 'nullable|string',
            'ff_signature_role' => 'nullable|integer',
        ], [], [
            'ff_id' => 'form field',
            'ff_label' => 'label',
            'ff_component_type' => 'component type',
            'ff_placeholder' => 'placeholder',
            'ff_component_required' => 'required status',
            'ff_component_required_role' => 'required role',
            'ff_value_options' => 'value options',
            'ff_append_text' => 'append text',
            'ff_table' => 'source table',
            'ff_datakey' => 'data key',
            'ff_extra_datakey' => 'extra data key',
            'ff_extra_condition' => 'extra condition',
            'ff_signature_role' => 'user role',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $validated = $validator->validated();
            $getAfId = FormField::where('id', $validated['ff_id'])->value('af_id');

            $checkExists = FormField::where('ff_label', $validated['ff_label'])
                ->where('af_id', $getAfId)
                ->where('id', '!=', $validated['ff_id'])
                ->exists();

            if ($checkExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field label already exists within the same form. Please make the field label unique.',
                ], 200);
            }
            // CHECK WHETHER USER ROLE EXIST IN ff_signature_role
            $signature_key = null;
            $signature_date_key = null;
            if ($validated['ff_signature_role'] != null) {
                $userRole = $validated['ff_signature_role'];

                if ($userRole == 1) {
                    $signature_key = 'student_signature';
                    $signature_date_key = 'student_signature_date';
                } else if ($userRole == 2) {
                    $signature_key = 'sv_signature';
                    $signature_date_key = 'sv_signature_date';
                } else if ($userRole == 3) {
                    $signature_key = 'cosv_signature';
                    $signature_date_key = 'cosv_signature_date';
                } else if ($userRole == 4) {
                    $signature_key = 'comm_signature';
                    $signature_date_key = 'comm_signature_date';
                } else if ($userRole == 5) {
                    $signature_key = 'deputy_dean_signature';
                    $signature_date_key = 'deputy_dean_signature_date';
                } else if ($userRole == 6) {
                    $signature_key = 'dean_signature';
                    $signature_date_key = 'dean_signature_date';
                } else if ($userRole == 7) {
                    $signature_key = 'chairman_signature';
                    $signature_date_key = 'chairman_signature_date';
                } else if ($userRole == 8) {
                    $name = strtolower(str_replace(' ', '_', $validated['ff_label']));
                    $signature_key =  $name . '_signature';
                    $signature_date_key = $name . '_signature_date';
                }
            }

            $formfield = FormField::where('id', $validated['ff_id'])->update([
                'ff_label' => $validated['ff_label'],
                'ff_component_type' => $validated['ff_component_type'],
                'ff_placeholder' => $validated['ff_placeholder'] ?? null,
                'ff_component_required' => $validated['ff_component_required'] ?? '2',
                'ff_component_required_role' => $validated['ff_component_required_role'] ?? '0',
                'ff_value_options' => $validated['ff_value_options'] ?? null,
                'ff_append_text' => $validated['ff_append_text'] ?? null,
                'ff_table' => $validated['ff_table'] ?? null,
                'ff_datakey' => $validated['ff_datakey'],
                'ff_extra_datakey' => $validated['ff_extra_datakey'] ?? null,
                'ff_extra_condition' => $validated['ff_extra_condition'] ?? null,
                'ff_signature_role' => $validated['ff_signature_role'] ?? null,
                'ff_signature_key' => $signature_key,
                'ff_signature_date_key' => $signature_date_key,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Field updated successfully.',
                'formfield' => $formfield,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating the form field: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateFormFieldOrder(Request $req)
    {
        try {
            $fields = $req->input('fields', []);

            foreach ($fields as $field) {
                FormField::where('id', $field['id'])->update([
                    'ff_order' => $field['order']
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Field order updated successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating the form field order: ' . $e->getMessage(),

            ], 500);
        }
    }

    public function deleteFormField(Request $req)
    {
        try {
            $checkExists = FormField::where('id', $req->ff_id)->exists();

            if (!$checkExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attribute not found.',
                ], 200);
            }
            FormField::where('id', $req->ff_id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Field deleted successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting the form field: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getFormFieldData(Request $req)
    {
        try {
            $fields = FormField::where('af_id', $req->af_id)
                ->orderBy('ff_order')
                ->get(['id', 'ff_label', 'ff_category', 'ff_order']);

            $categoryMap = [
                1 => 'Input',
                2 => 'Output',
                3 => 'Section',
                4 => 'Text',
                5 => 'Table',
                6 => 'Signature'
            ];

            $fields = $fields->map(function ($field) use ($categoryMap) {
                $categoryLabel = isset($categoryMap[$field->ff_category]) ? $categoryMap[$field->ff_category] : 'Unknown';

                return [
                    'id' => $field->id,
                    'ff_label' => $field->ff_label,
                    'ff_category' => $categoryLabel,
                    'ff_order' => $field->ff_order
                ];
            });

            return response()->json([
                'success' => true,
                'fields' => $fields
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting the form field data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSingleFormFieldData(Request $req)
    {
        try {
            $fields = FormField::where('id', $req->ff_id)->first();

            return response()->json([
                'success' => true,
                'fields' => $fields
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting the selected form field data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getTableColumnData(Request $req)
    {
        try {
            $table = $req->get('table');

            $columns = Schema::getColumnListing($table);

            $filteredColumns = array_filter($columns, function ($col) {
                return in_array($col, [
                    'student_name',
                    'staff_name',
                    'student_email',
                    'staff_email',
                    'student_matricno',
                    'staff_id',
                ]);
            });

            return response()->json([
                'success' => true,
                'columns' => array_values($filteredColumns)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting the selected form field data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
