<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Faculty;
use App\Models\Activity;
use App\Models\Document;
use App\Models\FormField;
use App\Models\Procedure;
use App\Models\Programme;
use Illuminate\Support\Str;
use App\Models\ActivityForm;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use function Laravel\Prompts\form;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;
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

                $table->rawColumns(['is_haveEva', 'init_status', 'material', 'action']);

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
            'activity_id'   => 'required|integer|exists:activities,id',
            'programme_id'  => 'required|integer|exists:programmes,id',
            'act_seq'       => 'required|integer|min:1',
            'timeline_sem'  => 'required|integer|min:1',
            'timeline_week' => 'required|integer|min:1|max:52',
            'init_status'   => 'required|integer|in:1,2',
            'is_haveEva'    => 'required|boolean|in:0,1',
            'material'      => 'nullable|file|mimes:pdf|max:5120',
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
                'is_haveEva'    => $validated['is_haveEva'],
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
            'act_seq_up'       => 'required|integer|min:1',
            'timeline_sem_up'  => 'required|integer|min:1',
            'timeline_week_up' => 'required|integer|min:1|max:52',
            'init_status_up'   => 'required|integer|in:1,2',
            'is_haveEva_up'    => 'required|boolean|in:0,1',
            'material_up'      => 'nullable|file|mimes:pdf|max:5120',
        ], [], [
            'act_seq_up'       => 'activity sequence',
            'timeline_sem_up'  => 'semester timeline',
            'timeline_week_up' => 'week timeline',
            'init_status_up'   => 'initial status',
            'is_haveEva_up'    => 'evaluation',
            'material_up'      => 'material',
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
                'is_haveEva'    => $validated['is_haveEva_up'],
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

    /* Form Setting */
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
            ->groupBy('a.act_name', 'b.id', 'b.af_title', 'b.af_target', 'b.af_status', 'a.id')
            ->orderByRaw('form_count IS NULL ASC, form_count DESC, a.act_name ASC');

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
                    $target = '<span class="badge bg-yellow-600">' . 'Evaluation' . '</span>';
                } elseif ($row->form_target == 3) {
                    $target = '<span class="badge bg-yellow-300">' . 'Nomination' . '</span>';
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
                        <a href="' . route('form-generator', ['formID' => $row->af_id, 'afTarget' => $row->form_target]) . '" class="avtar avtar-xs btn-light-primary">
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
            'formTarget' => 'required',
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
            return back()->with('success', 'Form deleted successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error deleting forms: ' . $e->getMessage());
        }
    }

    public function formGenerator($formID, $afTarget)
    {
        try {
            $formdata = ActivityForm::where('id', $formID)->where('af_target', $afTarget)->first();
            if (!$formdata) {
                return abort(404, 'Form not found.');
            }

            return view('staff.sop.form-generator', [
                'title' => 'Form Generator',
                'formdata' => $formdata,
            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
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
                'title' => $act->act_name . " Document",
                'act' => $act,
                'form_title' => $req->title,
                'actform' => $actform,
                'formfields' => $formfield,
                'faculty' => $faculty,
                'signatures' => $signatures

            ]);

            return $pdf->stream('preview.pdf');
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    // [Debug Function]
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
                'message' => 'Error: ' . $e->getMessage(),
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
                    'message' => 'Field already exists.',
                ], 200);
            }

            // Get the next order number
            $af_count = FormField::where('af_id', $af_id)->count();
            $nextOrder = $af_count + 1;

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
                }
            }


            $formfield = FormField::create([
                'ff_label' => $validated['ff_label'],
                'ff_order' => $nextOrder,
                'ff_category' => $validated['ff_category'],
                'ff_component_type' => $validated['ff_component_type'],
                'ff_placeholder' => $validated['ff_placeholder'] ?? null,
                'ff_component_required' => $validated['ff_component_required'] ?? '2',
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
                'message' => $e->getMessage(),
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
            // $getAfId = FormField::where('id', $validated['ff_id'])->first()->af_id;
            // $checkExists = FormField::where('ff_label', $validated['ff_label'])->where('af_id', $getAfId)->exists();

            // if ($checkExists) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Field already exists.',
            //     ], 200);
            // }
            
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
                }
            }

            $formfield = FormField::where('id', $validated['ff_id'])->update([
                'ff_label' => $validated['ff_label'],
                'ff_component_type' => $validated['ff_component_type'],
                'ff_placeholder' => $validated['ff_placeholder'] ?? null,
                'ff_component_required' => $validated['ff_component_required'] ?? '2',
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
                'message' => $e->getMessage(),
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
                'message' => $e->getMessage(),
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
                'message' => $e->getMessage(),
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
                'message' => 'Error: ' . $e->getMessage(),
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
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
