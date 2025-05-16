<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /* Activity Setting ORIGINAL CODE [Backup] */
    // public function activitySetting(Request $req)
    // {
    //     try {
    //         if ($req->ajax()) {

    //             $data = DB::table('activities')
    //                 ->select('id', 'act_name')
    //                 ->get();

    //             $table = DataTables::of($data)->addIndexColumn();

    //             $table->addColumn('action', function ($row) {

    //                 $isReferenced = false;
    //                 // $isReferenced = DB::table('procedures')->where('act_id', $row->id)->exists();

    //                 $buttonEdit =
    //                     '
    //                         <a href="javascript: void(0)" class="btn-light-primary avtar avtar-xs" data-bs-toggle="modal"
    //                             data-bs-target="#updateModal-' . $row->id . '">
    //                             <i class="f-20 ti ti-edit"></i>
    //                         </a>
    //                     ';

    //                 if (!$isReferenced) {
    //                     $buttonRemove =
    //                         '
    //                             <a href="javascript: void(0)" class="btn-light-danger avtar avtar-xs" data-bs-toggle="modal"
    //                                 data-bs-target="#deleteModal-' . $row->id . '">
    //                                 <i class="f-20 ti ti-trash"></i>
    //                             </a>
    //                         ';
    //                 } else {


    //                     $buttonRemove =
    //                         '
    //                             <a href="javascript: void(0)" class="btn-light-danger avtar avtar-xs disabled-a" data-bs-toggle="modal"
    //                                 data-bs-target="#disableModal">
    //                                 <i class="f-20 ti ti-trash"></i>
    //                             </a>
    //                         ';
    //                 }

    //                 return $buttonEdit . $buttonRemove;
    //             });

    //             $table->rawColumns(['fac_status', 'action']);

    //             return $table->make(true);
    //         }
    //         return view('staff.sop.activity-setting', [
    //             'title' => 'Activity Setting',
    //             'acts' => Activity::all()
    //         ]);
    //     } catch (Exception $e) {
    //         return abort(500);
    //     }
    // }

    // public function addActivity(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'act_name' => 'required|string|unique:activities,act_name,',
    //     ], [], [
    //         'act_name' => 'activity name',
    //     ]);

    //     if ($validator->fails()) {
    //         return redirect()->back()
    //             ->withErrors($validator)
    //             ->withInput()
    //             ->with('modal', 'addModal');
    //     }
    //     try {
    //         $validated = $validator->validated();
    //         Activity::create([
    //             'act_name' => $validated['act_name'],
    //         ]);

    //         return back()->with('success', 'Activity added successfully.');
    //     } catch (Exception $e) {
    //         return back()->with('error', 'Oops! Error adding activity.');
    //     }
    // }

    // public function updateActivity(Request $req, $id)
    // {
    //     $id = decrypt($id);
    //     $validator = Validator::make($req->all(), [
    //         'act_name_up' => 'required|string|unique:activities,act_name,' . $id,
    //     ], [], [
    //         'act_name_up' => 'activity name',
    //     ]);


    //     if ($validator->fails()) {
    //         return redirect()->back()
    //             ->withErrors($validator)
    //             ->withInput()
    //             ->with('modal', 'updateModal-' . $id);
    //     }

    //     try {
    //         $validated = $validator->validated();
    //         Activity::find($id)->update([
    //             'act_name' => $validated['act_name_up'],
    //         ]);

    //         return back()->with('success', 'Activity updated successfully.');
    //     } catch (Exception $e) {
    //         return back()->with('error', 'Oops! Error updating activity.');
    //     }
    // }

    // public function deleteActivity($id, $opt)
    // {
    //     try {
    //         $id = decrypt($id);
    //         if ($opt == 1) {
    //             Activity::where('id', $id)->delete();
    //             return  back()->with('success', 'Activity deleted successfully.');
    //         } elseif ($opt == 2) {
    //             return abort(500);
    //         }
    //     } catch (Exception $e) {
    //         return  back()->with('error', 'Oops! Error deleting activity.');
    //     }
    // }


    // public function viewActivityTemplate()
    // {
    //     try {
    //         return view('staff.sop.template.activity-template', [
    //             'title' => 'Activity Template'
    //         ]);
    //     } catch (Exception $e) {
    //         return abort(500, $e->getMessage());
    //     }
    // }

    // public function updateMultipleSubmission(Request $req)
    // {
    //     $submissionIds = $req->input('selectedIds');

    //     $rules = [];
    //     $attributes = [];

    //     if ($req->has('submission_status_ups') && !empty($req->input('submission_status_ups'))) {
    //         $rules['submission_status_ups'] = 'integer|in:1,2,3,4,5';
    //         $attributes['submission_status_ups'] = 'submission status';
    //     }

    //     if ($req->has('submission_duedate_ups') && !empty($req->input('submission_duedate_ups'))) {
    //         $rules['submission_duedate_ups'] = 'nullable';
    //         $attributes['submission_duedate_ups'] = 'submission due date';
    //     }

    //     if (!empty($rules)) {
    //         $validator = Validator::make($req->all(), $rules, [], $attributes);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'errors' => $validator->errors(),
    //                 'message' => 'Validation failed.',
    //             ], 422);
    //         }
    //     }

    //     try {
    //         $updateData = [];

    //         if ($req->has('submission_status_ups') && !empty($req->input('submission_status_ups'))) {
    //             $updateData['submission_status'] = $req->input('submission_status_ups');
    //         }

    //         if ($req->has('submission_duedate_ups') && !empty($req->input('submission_duedate_ups'))) {
    //             $updateData['submission_duedate'] = $req->input('submission_duedate_ups');
    //         }

    //         if (!empty($updateData)) {
    //             Submission::whereIn('id', $submissionIds)->update($updateData);
    //         }

    //         return response()->json([
    //             'message' => 'All selected submissions have been updated successfully!',
    //         ], 200);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'message' => 'Oops! Error updating submissions: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }


    // $table->addColumn('action', function ($row) {

    //     if ($row->sa_status == 1 && $row->supervision_role == 1) {
    //         return
    //             '
    //             <button type="button" class="btn btn-light-success btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#approveModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-circle-check me-2"></i>
    //                 <span class="me-2">Approve</span>
    //             </button>

    //             <button type="button" class="btn btn-light-danger btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#rejectModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-circle-x me-2"></i>
    //                 <span class="me-2">Reject</span>
    //             </button>

    //             <button type="button" class="btn btn-light-warning btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#revertModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-rotate me-2"></i>
    //                 <span class="me-2">Revert</span>
    //             </button>
    //         ';
    //     } elseif ($row->sa_status == 1 && $row->supervision_role == 2) {
    //         return '<div class="fst-italic text-muted">No permission to proceed</div>';
    //     } elseif ($row->sa_status == 2 || $row->sa_status == 3 || $row->sa_status == 4 || $row->sa_status == 5) {
    //         return
    //             '
    //             <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#reviewModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-eye me-2"></i>
    //                 <span class="me-2">Review</span>
    //             </button>
    //         ';
    //     }
    // });



    // $table->addColumn('action', function ($row) {

    //     $hasSvfield = DB::table('activity_forms as a')
    //         ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //         ->where('a.activity_id', $row->activity_id)
    //         ->where('b.ff_category', 6)
    //         ->where('b.ff_signature_role', 2)
    //         ->exists();

    //     $hasCoSvfield = DB::table('activity_forms as a')
    //         ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //         ->where('a.activity_id', $row->activity_id)
    //         ->where('b.ff_category', 6)
    //         ->where('b.ff_signature_role', 3)
    //         ->exists();

    //     $hasCoSv = ($hasSvfield && $hasCoSvfield);

    //     // return $hasCoSv;

    //     $hasSvSignature = false;
    //     $hasCoSvSignature = false;

    //     if (!empty($row->sa_signature_data)) {
    //         $signatureData = json_decode($row->sa_signature_data, true);
    //         $hasSvSignature = isset($signatureData['sv_signature']);
    //         $hasCoSvSignature = isset($signatureData['cosv_signature']);
    //     }

    //     $signatureExists = ($hasCoSv && $hasSvSignature && $hasCoSvSignature);

    //     $svNoBtn = ($row->supervision_role == 1 && $hasSvSignature);
    //     $cosvNoBtn = ($row->supervision_role == 2 && $hasCoSvSignature);

    //     $svnopermision = $row->supervision_role == 1 && !$hasSvfield;
    //     $cosvnopermision = $row->supervision_role == 2 && !$hasCoSvfield;

    //     // Case: Signature exists, show review
    //     if ($signatureExists) {
    //         return '
    //             <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#reviewModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-eye me-2"></i>
    //                 <span class="me-2">Review</span>
    //             </button>
    //               <p class="mb-2">SV sign: ' . ($hasSvSignature ? 'true' : 'false') .
    //             ' | CoSV sign: ' . ($hasCoSvSignature ? 'true' : 'false') . '</p>

    //              <p class="mb-3">Have SV sign: ' . ($hasSvfield ? 'true' : 'false') .
    //             ' | Have CoSV sign: ' . ($hasCoSvfield ? 'true' : 'false') . '</p> 

    //              <p class="mb-2">SV NO BUTTON: ' . ($svNoBtn ? 'true' : 'false') .
    //             ' | CoSV NO BUTTON: ' . ($cosvNoBtn ? 'true' : 'false') . '</p> 

    //              <p class="mb-2">SV NO PERMISSION: ' . ($svnopermision ? 'true' : 'false') .
    //             ' | CoSV NO PERMISSION: ' . ($cosvnopermision ? 'true' : 'false') . '</p> 
    //         ';
    //     } elseif (!$signatureExists && $row->sa_status == 1) {


    //         if ($svNoBtn || $svnopermision ) {
    //             return '<div class="fst-italic text-muted">No action to proceed</div>
    //               <p class="mb-2">SV sign: ' . ($hasSvSignature ? 'true' : 'false') .
    //             ' | CoSV sign: ' . ($hasCoSvSignature ? 'true' : 'false') . '</p>

    //              <p class="mb-3">Have SV sign: ' . ($hasSvfield ? 'true' : 'false') .
    //             ' | Have CoSV sign: ' . ($hasCoSvfield ? 'true' : 'false') . '</p> 

    //              <p class="mb-2">SV NO BUTTON: ' . ($svNoBtn ? 'true' : 'false') .
    //             ' | CoSV NO BUTTON: ' . ($cosvNoBtn ? 'true' : 'false') . '</p> 

    //              <p class="mb-2">SV NO PERMISSION: ' . ($svnopermision ? 'true' : 'false') .
    //             ' | CoSV NO PERMISSION: ' . ($cosvnopermision ? 'true' : 'false') . '</p> 
    //             ';
    //         }

    //         if ($cosvNoBtn || $cosvnopermision) {
    //             return '<div class="fst-italic text-muted">No action to proceed</div>
    //              <p class="mb-2">SV sign: ' . ($hasSvSignature ? 'true' : 'false') .
    //             ' | CoSV sign: ' . ($hasCoSvSignature ? 'true' : 'false') . '</p>

    //              <p class="mb-3">Have SV sign: ' . ($hasSvfield ? 'true' : 'false') .
    //             ' | Have CoSV sign: ' . ($hasCoSvfield ? 'true' : 'false') . '</p> 

    //              <p class="mb-2">SV NO BUTTON: ' . ($svNoBtn ? 'true' : 'false') .
    //             ' | CoSV NO BUTTON: ' . ($cosvNoBtn ? 'true' : 'false') . '</p> 

    //              <p class="mb-2">SV NO PERMISSION: ' . ($svnopermision ? 'true' : 'false') .
    //             ' | CoSV NO PERMISSION: ' . ($cosvnopermision ? 'true' : 'false') . '</p> 
    //             ';
    //         }


    //         return '
    //             <button type="button" class="btn btn-light-success btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#approveModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-circle-check me-2"></i>
    //                 <span class="me-2">Approve</span>
    //             </button>

    //             <button type="button" class="btn btn-light-danger btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#rejectModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-circle-x me-2"></i>
    //                 <span class="me-2">Reject</span>
    //             </button>

    //             <button type="button" class="btn btn-light-warning btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#revertModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-rotate me-2"></i>
    //                 <span class="me-2">Revert</span>
    //             </button>
    //              <p class="mb-2">SV sign: ' . ($hasSvSignature ? 'true' : 'false') .
    //             ' | CoSV sign: ' . ($hasCoSvSignature ? 'true' : 'false') . '</p>

    //              <p class="mb-3">Have SV sign: ' . ($hasSvfield ? 'true' : 'false') .
    //             ' | Have CoSV sign: ' . ($hasCoSvfield ? 'true' : 'false') . '</p> 

    //              <p class="mb-2">SV NO BUTTON: ' . ($svNoBtn ? 'true' : 'false') .
    //             ' | CoSV NO BUTTON: ' . ($cosvNoBtn ? 'true' : 'false') . '</p> 

    //              <p class="mb-2">SV NO PERMISSION: ' . ($svnopermision ? 'true' : 'false') .
    //             ' | CoSV NO PERMISSION: ' . ($cosvnopermision ? 'true' : 'false') . '</p> 
    //         ';
    //     } else {
    //         return '
    //             <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#reviewModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-eye me-2"></i>
    //                 <span class="me-2">Review</span>
    //             </button>
    //              <p class="mb-2">SV sign: ' . ($hasSvSignature ? 'true' : 'false') .
    //             ' | CoSV sign: ' . ($hasCoSvSignature ? 'true' : 'false') . '</p>

    //              <p class="mb-3">Have SV sign: ' . ($hasSvfield ? 'true' : 'false') .
    //             ' | Have CoSV sign: ' . ($hasCoSvfield ? 'true' : 'false') . '</p> 

    //              <p class="mb-2">SV NO BUTTON: ' . ($svNoBtn ? 'true' : 'false') .
    //             ' | CoSV NO BUTTON: ' . ($cosvNoBtn ? 'true' : 'false') . '</p> 

    //              <p class="mb-2">SV NO PERMISSION: ' . ($svnopermision ? 'true' : 'false') .
    //             ' | CoSV NO PERMISSION: ' . ($cosvnopermision ? 'true' : 'false') . '</p> 
    //         ';
    //     }
    // });

    // $table->addColumn('action', function ($row) {

    //     $hasSvfield = DB::table('activity_forms as a')
    //         ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //         ->where('a.activity_id', $row->activity_id)
    //         ->where('b.ff_category', 6)
    //         ->where('b.ff_signature_role', 2)
    //         ->exists();

    //     $hasCoSvfield = DB::table('activity_forms as a')
    //         ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //         ->where('a.activity_id', $row->activity_id)
    //         ->where('b.ff_category', 6)
    //         ->where('b.ff_signature_role', 3)
    //         ->exists();

    //     $hasCoSv = ($hasSvfield && $hasCoSvfield);

    //     // return $hasCoSv;

    //     $hasSvSignature = false;
    //     $hasCoSvSignature = false;

    //     if (!empty($row->sa_signature_data)) {
    //         $signatureData = json_decode($row->sa_signature_data, true);
    //         $hasSvSignature = isset($signatureData['sv_signature']);
    //         $hasCoSvSignature = isset($signatureData['cosv_signature']);
    //     }

    //     $signatureExists = ($hasCoSv && $hasSvSignature && $hasCoSvSignature);

    //     $svNoBtn = ($row->supervision_role == 1 && $hasSvSignature);
    //     $cosvNoBtn = ($row->supervision_role == 2 && $hasCoSvSignature);

    //     $svnopermision = $row->supervision_role == 1 && !$hasSvfield;
    //     $cosvnopermision = $row->supervision_role == 2 && !$hasCoSvfield;

    //     if ($signatureExists) {
    //         return '
    //             <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#reviewModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-eye me-2"></i>
    //                 <span class="me-2">Review</span>
    //             </button>
    //         ';
    //     } elseif (!$signatureExists && $row->sa_status == 1) {


    //         if ($svNoBtn || $svnopermision) {
    //             return '<div class="fst-italic text-muted">No action to proceed</div>';
    //         }

    //         if ($cosvNoBtn || $cosvnopermision) {
    //             return '<div class="fst-italic text-muted">No action to proceed</div>';
    //         }


    //         return '
    //             <button type="button" class="btn btn-light-success btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#approveModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-circle-check me-2"></i>
    //                 <span class="me-2">Approve</span>
    //             </button>

    //             <button type="button" class="btn btn-light-danger btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#rejectModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-circle-x me-2"></i>
    //                 <span class="me-2">Reject</span>
    //             </button>

    //             <button type="button" class="btn btn-light-warning btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#revertModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-rotate me-2"></i>
    //                 <span class="me-2">Revert</span>
    //             </button>
    //         ';
    //     } else {
    //         return '
    //             <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
    //                 data-bs-toggle="modal" data-bs-target="#reviewModal-' . $row->student_activity_id . '">
    //                 <i class="ti ti-eye me-2"></i>
    //                 <span class="me-2">Review</span>
    //             </button>
    //         ';
    //     }
    // });

    // public function studentActivitySubmissionApproval(Request $request, $stuActID, $option)
    // {
    //     $stuActID = Crypt::decrypt($stuActID);
    //     try {
    //         $studentActivity = StudentActivity::whereId($stuActID)->first();
    //         $actID = Crypt::encrypt($studentActivity->activity_id);
    //         $student = Student::whereId($studentActivity->student_id)->first();
    //         $supervision = Supervision::where('student_id', $student->id)->where('staff_id', auth()->user()->id)->first();

    //         $hasSvfield = DB::table('activity_forms as a')
    //             ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //             ->where('a.activity_id', $studentActivity->activity_id)
    //             ->where('b.ff_category', 6)
    //             ->where('b.ff_signature_role', 2)
    //             ->exists();

    //         $hasCoSvfield = DB::table('activity_forms as a')
    //             ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //             ->where('a.activity_id', $studentActivity->activity_id)
    //             ->where('b.ff_category', 6)
    //             ->where('b.ff_signature_role', 3)
    //             ->exists();

    //         $hasCoSv = $hasSvfield && $hasCoSvfield;

    //         $hasSvSignature = false;
    //         $hasCoSvSignature = false;

    //         if (!empty($studentActivity->sa_signature_data)) {
    //             $signatureData = json_decode($studentActivity->sa_signature_data, true);

    //             if (isset($signatureData['sv_signature'])) {
    //                 $hasSvSignature = true;
    //             }
    //             if (isset($signatureData['cosv_signature'])) {
    //                 $hasCoSvSignature = true;
    //             }
    //         }

    //         if ($option == 1) {
    //             //Approving Student Activity
    //             $role = 2;
    //             $status = 1;

    //             if (!$supervision && auth()->user()->staff_role == 1) {
    //                 // COMMITTEE
    //                 $role = 4;
    //                 $status = 3;
    //             } elseif (!$supervision &&  auth()->user()->staff_role == 3) {
    //                 // DEPUTY DEAN
    //                 $role = 5;
    //                 $status = 3;
    //             } elseif (!$supervision && auth()->user()->staff_role == 4) {
    //                 // DEAN
    //                 $role = 6;
    //                 $status = 3;
    //             } elseif ($supervision->supervision_role == 1) {
    //                 $role = 2;
    //             } elseif ($supervision->supervision_role == 2) {
    //                 $role = 3;
    //             }

    //             // dd($role, $status);
    //             $signatureData = $request->input('signatureData');

    //             // 1. Call the merge function first – handles and stores signature data
    //             $this->mergeStudentSubmission($actID, $student, $signatureData, $role, $status);

    //             if ($request->input('comment') != null) {
    //                 SubmissionReview::create([
    //                     'student_activity_id' => $stuActID,
    //                     'sr_comment' => $request->input('comment'),
    //                     'sr_date' => date('Y-m-d'),
    //                     'staff_id' => auth()->user()->id
    //                 ]);
    //             }

    //             // 2. Re-fetch updated studentActivity to get the latest sa_signature_data
    //             $updatedActivity = StudentActivity::whereId($stuActID)->first();

    //             $hasSvSignature = false;
    //             $hasCoSvSignature = false;

    //             if (!empty($updatedActivity->sa_signature_data)) {
    //                 $updatedSignatureData = json_decode($updatedActivity->sa_signature_data, true);
    //                 $hasSvSignature = isset($updatedSignatureData['sv_signature']);
    //                 $hasCoSvSignature = isset($updatedSignatureData['cosv_signature']);
    //             }

    //             // 3. Re-evaluate the final status now that signatures are stored
    //             if ($hasCoSv) {
    //                 $status = ($hasSvSignature && $hasCoSvSignature) ? 2 : 1;
    //             } else {
    //                 $status = 2;
    //             }

    //             // 4. Update the student activity with the final status
    //             $updatedActivity->update([
    //                 'sa_status' => $status
    //             ]);


    //             // $this->sendSubmissionNotification($student, 1, $activity->act_name, 3, $role);

    //             return back()->with('success', 'Submission has been approved successfully.');
    //         } else if ($option == 2) {
    //             //Rejecting Student Activity
    //             $role = 2;
    //             $status = 1;

    //             if (!$supervision && auth()->user()->staff_role == 1) {
    //                 // COMMITTEE
    //                 $role = 4;
    //                 $status = 5;
    //             } elseif (!$supervision &&  auth()->user()->staff_role == 3) {
    //                 // DEPUTY DEAN
    //                 $role = 5;
    //                 $status = 5;
    //             } elseif (!$supervision && auth()->user()->staff_role == 4) {
    //                 // DEAN
    //                 $role = 6;
    //                 $status = 5;
    //             } elseif ($supervision->supervision_role == 1) {
    //                 $role = 2;
    //                 $status = 4;
    //             } elseif ($supervision->supervision_role == 2) {
    //                 $role = 3;
    //                 $status = 4;
    //             }

    //             StudentActivity::whereId($stuActID)->update([
    //                 'sa_status' => $status,
    //             ]);

    //             if ($request->input('comment') != null) {
    //                 SubmissionReview::create([
    //                     'student_activity_id' => $stuActID,
    //                     'sr_comment' => $request->input('comment'),
    //                     'sr_date' => date('Y-m-d'),
    //                     'staff_id' => auth()->user()->id
    //                 ]);
    //             }

    //             // $this->sendSubmissionNotification($student, 1, $activity->act_name, 4, $role);

    //             return back()->with('success', 'Submission has been rejected successfully.');
    //         } else if ($option == 3) {
    //             //Reverting Student Activity
    //             SubmissionReview::where('student_activity_id', $stuActID)->delete();
    //             StudentActivity::whereId($stuActID)->delete();

    //             // $this->sendSubmissionNotification($student, 1, $activity->act_name, 5, 0);

    //             return back()->with('success', 'The student submission has been successfully reverted. Please notify the student to reconfirm their submission.');
    //         } else {
    //             return back()->with('error', 'Oops! Invalid option. Please try again.');
    //         }
    //     } catch (Exception $e) {
    //         return back()->with('error', 'Oops! Error occurred: ' . $e->getMessage());
    //     }
    // }

    // $table->addColumn('sa_status', function ($row) {

    //     $hasSvfield = DB::table('activity_forms as a')
    //         ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //         ->where('a.activity_id', $row->activity_id)
    //         ->where('b.ff_category', 6)
    //         ->where('b.ff_signature_role', 2)
    //         ->exists();

    //     $hasCoSvfield = DB::table('activity_forms as a')
    //         ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //         ->where('a.activity_id', $row->activity_id)
    //         ->where('b.ff_category', 6)
    //         ->where('b.ff_signature_role', 3)
    //         ->exists();

    //     $hasCoSv = ($hasSvfield && $hasCoSvfield);

    //     $hasSvSignature = false;
    //     $hasCoSvSignature = false;

    //     if (!empty($row->sa_signature_data)) {
    //         $signatureData = json_decode($row->sa_signature_data, true);
    //         $hasSvSignature = isset($signatureData['sv_signature']);
    //         $hasCoSvSignature = isset($signatureData['cosv_signature']);
    //     }
    //     $status = '';

    //     if ($row->sa_status == 1) {

    //         if ($hasCoSv) {
    //             if ($hasSvSignature) {
    //                 $status .= '<span class="badge bg-light-success mb-2 text-start">' . 'Approved (SV)' . '</span> <br>';
    //             } else {
    //                 $status .= '<span class="badge bg-light-danger mb-2  text-start">' . 'Required: <br> Approval (Sv)' . '</span> <br>';
    //             }
    //             if ($hasCoSvSignature) {
    //                 $status .= '<span class="badge bg-light-success mb-2  text-start">' . 'Approved (CoSv)' . '</span> <br>';
    //             } else {
    //                 $status .= '<span class="badge bg-light-danger mb-2  text-start">' . 'Required: <br> Approval (CoSv)' . '</span> <br>';
    //             }
    //         } else {
    //             $status = '<span class="badge bg-light-warning  text-start">' . 'Pending Approval' . '</span>';
    //         }
    //     } elseif ($row->sa_status == 2) {
    //         $status = '<span class="badge bg-success">' . 'Approved (SV)' . '</span>';
    //     } elseif ($row->sa_status == 3) {
    //         $status = '<span class="badge bg-success">' . 'Approved <br> (Comm/DD/Dean)' . '</span>';
    //     } elseif ($row->sa_status == 4) {
    //         $status = '<span class="badge bg-danger">' . 'Rejected (SV)' . '</span>';
    //     } elseif ($row->sa_status == 5) {
    //         $status = '<span class="badge bg-danger">' . 'Rejected <br> (Comm/DD/Dean)' . '</span>';
    //     } else {
    //         $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
    //     }

    //     return $status;
    // });
}
