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


    // public function storeSignature($actID, $student, $form, $signatureData, $documentName, $signatureRole, $status)
    // {
    //     try {
    //         if ($signatureData) {

    //             $signatureField = FormField::where([
    //                 ['af_id', $form->id],
    //                 ['ff_category', 6],
    //                 ['ff_signature_role', $signatureRole]
    //             ])->first();

    //             if ($signatureField) {
    //                 $signatureKey = $signatureField->ff_signature_key;
    //                 $dateKey = $signatureField->ff_signature_date_key;

    //                 $newSignatureData = [
    //                     $signatureKey => $signatureData,
    //                     $dateKey => now()->format('d M Y')
    //                 ];

    //                 // Retrieve or create StudentActivity
    //                 $studentActivity = StudentActivity::where([
    //                     ['activity_id', $actID],
    //                     ['student_id', $student->id]
    //                 ])->first();

    //                 // Decode existing signature JSON if exists
    //                 $existingSignatureData = [];
    //                 if ($studentActivity && $studentActivity->sa_signature_data) {
    //                     $existingSignatureData = json_decode($studentActivity->sa_signature_data, true);
    //                 }

    //                 // Merge new data into existing data
    //                 $mergedSignatureData = array_merge($existingSignatureData, $newSignatureData);

    //                 // Save or create StudentActivity record
    //                 if (!$studentActivity) {
    //                     StudentActivity::create([
    //                         'activity_id' => $actID,
    //                         'student_id' => $student->id,
    //                         'sa_final_submission' => $documentName,
    //                         'sa_signature_data' => json_encode($mergedSignatureData)
    //                     ]);
    //                 } else {
    //                     $studentActivity->sa_status = $status;
    //                     $studentActivity->sa_signature_data = json_encode($mergedSignatureData);
    //                     $studentActivity->save();
    //                 }
    //             }
    //         }
    //     } catch (Exception $e) {
    //         return back()->with('error', 'Oops! Error storing signature: ' . $e->getMessage());
    //     }
    // }

    // $data = DB::table('students as s')
    //     ->select([
    //         's.id as student_id',
    //         's.student_name',
    //         's.student_matricno',
    //         's.student_email',
    //         's.student_directory',
    //         's.student_photo',
    //         'b.sem_label',
    //         'c.prog_code',
    //         'c.prog_mode',
    //         'c.fac_id',
    //         's.student_semcount',
    //         'p.timeline_sem',
    //         'p.programme_id',
    //         'a.id as activity_id',
    //         'a.act_name as activity_name',
    //         'p.act_seq',
    //         'p.init_status',
    //         DB::raw(
    //             'CASE
    //             WHEN EXISTS (
    //                 SELECT 1 FROM student_activities sa_current
    //                 WHERE sa_current.student_id = s.id
    //                 AND sa_current.activity_id = p.activity_id
    //                 AND sa_current.sa_status = 3
    //             ) THEN 5
    //             WHEN EXISTS (
    //                 SELECT 1 FROM documents d
    //                 JOIN submissions sub ON sub.document_id = d.id
    //                 WHERE d.activity_id = p.activity_id
    //                 AND sub.student_id = s.id
    //                 AND sub.submission_status = 5
    //             ) THEN 6
    //             WHEN EXISTS (
    //                 SELECT 1 FROM student_activities sa_current
    //                 WHERE sa_current.student_id = s.id
    //                 AND sa_current.activity_id = p.activity_id
    //             ) THEN 4
    //             WHEN EXISTS (
    //                 SELECT 1 FROM documents d
    //                 JOIN submissions sub ON sub.document_id = d.id
    //                 WHERE d.activity_id = p.activity_id
    //                 AND sub.student_id = s.id
    //                 AND sub.submission_status IN (1, 4)
    //             ) 
    //             AND NOT EXISTS (
    //                 SELECT 1 FROM student_activities sa
    //                 WHERE sa.student_id = s.id
    //                 AND sa.activity_id = p.activity_id
    //             ) THEN 2
    //             WHEN EXISTS (
    //                 SELECT 1 FROM procedures p_prev
    //                 WHERE p_prev.programme_id = s.programme_id
    //                 AND p_prev.act_seq < p.act_seq
    //                 AND NOT EXISTS (
    //                     SELECT 1 FROM student_activities sa_prev
    //                     WHERE sa_prev.student_id = s.id
    //                     AND sa_prev.activity_id = p_prev.activity_id
    //                     AND sa_prev.sa_status = 3
    //                 )
    //             ) THEN 3
    //             ELSE 1
    //         END as suggestion_status'
    //         )
    //     ])
    //     ->join('student_semesters as ss', function ($join) {
    //         $join->on('s.id', '=', 'ss.student_id')
    //             ->where('ss.ss_status', '=', 1);
    //     })
    //     ->join('procedures as p', function ($join) {
    //         $join->on('s.programme_id', '=', 'p.programme_id')
    //             ->whereRaw('s.student_semcount >= p.timeline_sem')
    //             ->where('p.init_status', '=', 2);
    //     })
    //     ->join('activities as a', 'p.activity_id', '=', 'a.id')
    //     ->join('semesters as b', 'b.id', '=', 'ss.semester_id')
    //     ->join('programmes as c', 'c.id', '=', 's.programme_id')
    //     ->where('s.student_status', '=', 1)
    //     ->orderBy('s.student_matricno')
    //     ->orderBy('p.act_seq');



    // public function storeEvaluationSignature($student, $form, $signatureData, $evaluation, $signatureRole, $userData, $nomination)
    // {
    //     try {
    //         if (!$signatureData || !is_array($signatureData)) {
    //             throw new Exception('Invalid signature data');
    //         }

    //         // Get all signature fields for this form
    //         $signatureFields = FormField::where('af_id', $form->id)
    //             ->where('ff_category', 6) // Signature fields
    //             ->get();

    //         // Prepare existing signature data
    //         $existingData = $evaluation->evaluation_signature_data
    //             ? json_decode($evaluation->evaluation_signature_data, true)
    //             : [];

    //         foreach ($signatureFields as $signatureField) {
    //             $signatureKey = $signatureField->ff_signature_key;
    //             $dateKey = $signatureField->ff_signature_date_key;

    //             // Skip if no signature data for this field
    //             if (!isset($signatureData[$signatureKey])) {
    //                 continue;
    //             }

    //             // Skip if signature is empty
    //             if (empty($signatureData[$signatureKey])) {
    //                 continue;
    //             }

    //             // Determine role based on field properties
    //             $role = $this->determineSignatureRole(
    //                 $signatureField,
    //                 $userData,
    //                 $nomination
    //             );

    //             // Skip if role could not be determined
    //             if ($role === null) {
    //                 continue;
    //             }
    //             // Prepare new signature data
    //             $newSignatureData = [
    //                 $signatureKey => $signatureData[$signatureKey],
    //                 $dateKey => now()->format('d M Y'),
    //                 $signatureKey . '_is_cross_approval' => false,
    //                 $signatureKey . '_name' => $role === 'Student'
    //                     ? $student->student_name
    //                     : $userData->staff_name,
    //                 $signatureKey . '_role' => $role
    //             ];

    //             // Merge with existing data
    //             $existingData = array_merge($existingData, $newSignatureData);
    //         }

    //         // Save all signatures
    //         $evaluation->evaluation_signature_data = json_encode($existingData);
    //         $evaluation->save();
    //     } catch (Exception $e) {
    //         throw new Exception('Signature storage error: ' . $e->getMessage());
    //     }
    // }

    // protected function determineSignatureRole($signatureField, $userData, $nomination)
    // {
    //     // Student signature
    //     if ($signatureField->ff_signature_role == 1) {
    //         return 'Student';
    //     }

    //     // Try to determine from field label first
    //     $label = strtolower($signatureField->ff_label);

    //     // Check for chairman
    //     if (str_contains($label, 'chair') || str_contains($label, 'coordinator')) {
    //         return 'Chairman';
    //     }

    //     // Check for examiner with number (Examiner 1, Panel Member 2, etc.)
    //     if (preg_match('/(examiner|panel|reviewer)\s*(\d+)/i', $label, $matches)) {
    //         return ucfirst($matches[1]) . ' ' . $matches[2];
    //     }

    //     // Check for generic examiner
    //     if (str_contains($label, 'examiner') || str_contains($label, 'panel') || str_contains($label, 'reviewer')) {
    //         return 'Examiner';
    //     }

    //     // Fallback to evaluator table if label doesn't indicate role
    //     $evaluator = Evaluator::where('staff_id', $userData->id)
    //         ->where('nom_id', $nomination->id)
    //         ->first();

    //     if ($evaluator) {
    //         return $evaluator->eva_role == 1 ? 'Examiner' : 'Chairman';
    //     }

    //     // Default fallback
    //     return 'Evaluator';
    // }

    // public function studentActivitySubmissionApproval(Request $request, $stuActID, $option)
    // {
    //     $stuActID = Crypt::decrypt($stuActID);

    //     try {
    //         $studentActivity = StudentActivity::findOrFail($stuActID);
    //         $actID = Crypt::encrypt($studentActivity->activity_id);
    //         $student = Student::findOrFail($studentActivity->student_id);
    //         $activity = Activity::whereId($studentActivity->activity_id)->first();

    //         $authUser = auth()->user();
    //         $supervision = Supervision::where('student_id', $student->id)
    //             ->where('staff_id', $authUser->id)
    //             ->first();

    //         // Check if SV and CoSV signatures are required
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

    //         $signatureData = !empty($studentActivity->sa_signature_data)
    //             ? json_decode($studentActivity->sa_signature_data, true)
    //             : [];

    //         $hasSvSignature = isset($signatureData['sv_signature']);
    //         $hasCoSvSignature = isset($signatureData['cosv_signature']);

    //         if ($option == 1) {
    //             // === APPROVE === //
    //             [$role, $status] = $this->determineApprovalRoleStatus($supervision, $authUser->staff_role);

    //             // Step 1: Merge signature
    //             $this->mergeStudentSubmission($actID, $student, $request->input('signatureData'), $role, $authUser, $status);

    //             // Step 2: Save review comment if present
    //             if ($request->filled('comment')) {
    //                 SubmissionReview::create([
    //                     'student_activity_id' => $stuActID,
    //                     'sr_comment' => $request->input('comment'),
    //                     'sr_date' => now()->toDateString(),
    //                     'staff_id' => $authUser->id
    //                 ]);
    //             }

    //             // Step 3: Refresh activity and recheck signature
    //             $updatedActivity = StudentActivity::findOrFail($stuActID);
    //             $updatedSignatureData = !empty($updatedActivity->sa_signature_data)
    //                 ? json_decode($updatedActivity->sa_signature_data, true)
    //                 : [];

    //             $hasSvSignature = isset($updatedSignatureData['sv_signature']);
    //             $hasCoSvSignature = isset($updatedSignatureData['cosv_signature']);

    //             // Step 4: Final Status
    //             if (in_array($role, [2, 3])) {
    //                 $formFields = DB::table('activity_forms as a')
    //                     ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //                     ->where('a.activity_id', $studentActivity->activity_id)
    //                     ->where('b.ff_category', 6)
    //                     ->pluck('b.ff_signature_role')
    //                     ->toArray();

    //                 $requiredRoles = collect($formFields)->unique()->values()->toArray();

    //                 // Check if higher roles (4, 5, 6) are present
    //                 $hasHigherRoles = collect($requiredRoles)->intersect([4, 5, 6])->isNotEmpty();

    //                 if ($hasCoSv) {
    //                     $finalStatus = ($hasSvSignature && $hasCoSvSignature)
    //                         ? ($hasHigherRoles ? 2 : 3)
    //                         : 1;
    //                 } else {
    //                     $finalStatus = $hasSvSignature
    //                         ? ($hasHigherRoles ? 2 : 3)
    //                         : 1;
    //                 }

    //                 $updatedActivity->update(['sa_status' => $finalStatus]);

    //                 if ($finalStatus == 3) {
    //                     //COMMENT FOR TESTING PURPOSE
    //                     DB::table('submissions as a')
    //                         ->join('documents as b', 'a.document_id', '=', 'b.id')
    //                         ->join('activities as c', 'b.activity_id', '=', 'c.id')
    //                         ->where('a.student_id', $student->id)
    //                         ->where('c.id', $studentActivity->activity_id)
    //                         ->update(['a.submission_status' => 5]);
    //                     $this->sendSubmissionNotification($student, 1, $activity->act_name, 6, $role);
    //                 }
    //             } else {
    //                 // Committee / Deputy Dean / Dean
    //                 $formFields = DB::table('activity_forms as a')
    //                     ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //                     ->where('a.activity_id', $studentActivity->activity_id)
    //                     ->where('b.ff_category', 6)
    //                     ->pluck('b.ff_signature_role')
    //                     ->toArray();

    //                 $requiredRoles = collect($formFields)->unique()->values()->toArray();

    //                 $hasCommfield = in_array(4, $requiredRoles);
    //                 $hasDeputyDeanfield = in_array(5, $requiredRoles);
    //                 $hasDeanfield = in_array(6, $requiredRoles);

    //                 // Use updated signature data
    //                 $hasCommSignature = isset($updatedSignatureData['comm_signature_date']);
    //                 $hasDeputyDeanSignature = isset($updatedSignatureData['deputy_dean_signature_date']);
    //                 $hasDeanSignature = isset($updatedSignatureData['dean_signature_date']);

    //                 $roleSignatures = [
    //                     4 => $hasCommfield ? $hasCommSignature : true,
    //                     5 => $hasDeputyDeanfield ? $hasDeputyDeanSignature : true,
    //                     6 => $hasDeanfield ? $hasDeanSignature : true,
    //                 ];

    //                 // Final status: 3 if all required roles signed, else 2
    //                 $finalStatus = collect($roleSignatures)
    //                     ->only($requiredRoles)
    //                     ->every(fn($signed) => $signed) ? 3 : 2;

    //                 $updatedActivity->update(['sa_status' => $finalStatus]);

    //                 if ($finalStatus == 3) {
    //                     //COMMENT FOR TESTING PURPOSE
    //                     DB::table('submissions as a')
    //                         ->join('documents as b', 'a.document_id', '=', 'b.id')
    //                         ->join('activities as c', 'b.activity_id', '=', 'c.id')
    //                         ->where('a.student_id', $student->id)
    //                         ->where('c.id', $studentActivity->activity_id)
    //                         ->update(['a.submission_status' => 5]);
    //                     $this->sendSubmissionNotification($student, 1, $activity->act_name, 6, $role);
    //                 }
    //             }

    //             $this->sendSubmissionNotification($student, 1, $activity->act_name, 3, $role);
    //             return back()->with('success', 'Submission has been approved successfully.');
    //         } elseif ($option == 2) {
    //             // === REJECT === //
    //             [$role, $status] = $this->determineRejectionRoleStatus($supervision, $authUser->staff_role);

    //             $signatureData = json_decode($studentActivity->sa_signature_data ?? '[]', true);

    //             // List of keys to remove
    //             $keysToRemove = [
    //                 'sv_signature',
    //                 'sv_signature_date',
    //                 'cosv_signature',
    //                 'cosv_signature_date',
    //                 'comm_signature',
    //                 'comm_signature_date',
    //                 'deputy_dean_signature',
    //                 'deputy_dean_signature_date',
    //                 'dean_signature',
    //                 'dean_signature_date',
    //             ];

    //             // Remove the keys
    //             foreach ($keysToRemove as $key) {
    //                 unset($signatureData[$key]);
    //             }

    //             StudentActivity::whereId($stuActID)->update(['sa_status' => $status, 'sa_signature_data' => json_encode($signatureData)]);

    //             if ($request->filled('comment')) {
    //                 SubmissionReview::create([
    //                     'student_activity_id' => $stuActID,
    //                     'sr_comment' => $request->input('comment'),
    //                     'sr_date' => now()->toDateString(),
    //                     'staff_id' => $authUser->id
    //                 ]);
    //             }

    //             $this->sendSubmissionNotification($student, 1, $activity->act_name, 4, $role);

    //             return back()->with('success', 'Submission has been rejected successfully.');
    //         } elseif ($option == 3) {
    //             // === REVERT === //
    //             SubmissionReview::where('student_activity_id', $stuActID)->delete();
    //             StudentActivity::whereId($stuActID)->delete();

    //             $this->sendSubmissionNotification($student, 1, $activity->act_name, 5, 0);

    //             return back()->with('success', 'The student submission has been successfully reverted. Please notify the student to reconfirm their submission.');
    //         }

    //         return back()->with('error', 'Oops! Invalid option. Please try again.');
    //     } catch (Exception $e) {
    //         return back()->with('error', 'Oops! Error occurred: ' . $e->getMessage());
    //     }
    // }


    // public function storeCorrectionSignature($actID, $student, $semester, $form, $signatureData, $documentName, $signatureRole, $userData, $status, $preferredFieldId = null)
    // {
    //     try {
    //         if ($signatureData) {
    //             // Load or create activity correction entry
    //             $activityCorrection = ActivityCorrection::firstOrNew([
    //                 'activity_id' => $actID,
    //                 'student_id' => $student->id,
    //                 'semester_id' => $semester->id
    //             ]);

    //             // Decode existing signature data
    //             $existingSignatureData = [];
    //             if ($activityCorrection->ac_signature_data) {
    //                 $existingSignatureData = json_decode($activityCorrection->ac_signature_data, true);
    //             }

    //             // Load all signature fields for this role
    //             $allSignatureFields = FormField::where([
    //                 ['af_id', $form->id],
    //                 ['ff_category', 6],
    //                 ['ff_signature_role', $signatureRole]
    //             ])->get();

    //             $signatureField = null;

    //             // Pick the first empty field for this role (e.g. examiner_1, examiner_2, etc.)
    //             foreach ($allSignatureFields as $field) {
    //                 $key = $field->ff_signature_key;
    //                 if (empty($existingSignatureData[$key])) {
    //                     $signatureField = $field;
    //                     break;
    //                 }
    //             }

    //             // If no available field to sign, return
    //             if (!$signatureField) {
    //                 return back()->with('error', 'All required signatures for your role have already been completed.');
    //             }

    //             $signatureKey = $signatureField->ff_signature_key;
    //             $dateKey = $signatureField->ff_signature_date_key;

    //             // Create new signature block
    //             if ($signatureRole == 1) {
    //                 // Student role
    //                 $newSignatureData = [
    //                     $signatureKey => $signatureData,
    //                     $dateKey => now()->format('d M Y'),
    //                     $signatureKey . '_name' => $student->student_name,
    //                     $signatureKey . '_role' => 'Student',
    //                     $signatureKey . '_is_cross_approval' => false
    //                 ];
    //             } else {
    //                 // Staff roles
    //                 $roleName = match ($userData->staff_role) {
    //                     1 => "Committee",
    //                     2 => "Lecturer",
    //                     3 => "Deputy Dean",
    //                     4 => "Dean",
    //                     default => "Staff",
    //                 };

    //                 $newSignatureData = [
    //                     $signatureKey => $signatureData,
    //                     $dateKey => now()->format('d M Y'),
    //                     $signatureKey . '_name' => $userData->staff_name,
    //                     $signatureKey . '_role' => $roleName,
    //                     $signatureKey . '_is_cross_approval' => false
    //                 ];
    //             }

    //             // Merge with existing data
    //             $mergedSignatureData = array_merge($existingSignatureData, $newSignatureData);

    //             // Save updated data
    //             $activityCorrection->ac_signature_data = json_encode($mergedSignatureData);
    //             $activityCorrection->ac_final_submission = $documentName;
    //             $activityCorrection->ac_status = $status;
    //             $activityCorrection->save();
    //         }
    //     } catch (Exception $e) {
    //         return back()->with('error', 'Oops! Error storing signature: ' . $e->getMessage());
    //     }
    // }


    // private function handleSignatureApprovalStatus($student, $updatedActivity, $activity, $afID, $role, $hasCoSv, $updatedSignatureData, $isHaveEvaluation, $type)
    // {
    //     // type == 1 [Activity Form] , type == 2 [COrrection form]

    //     //[1] -- ACTIVITY FORM
    //     /** FIND FORM ROLES **/
    //     $formRoles = DB::table('activity_forms as a')
    //         ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //         ->where('a.activity_id', $updatedActivity->activity_id)
    //         ->where('a.id', $afID)
    //         ->where('a.af_target', 1)
    //         ->where('b.ff_category', 6)
    //         ->pluck('b.ff_signature_role')
    //         ->unique()
    //         ->toArray();

    //     if (in_array($role, [2, 3])) {
    //         /** HANDLE SUPERVISOR / CO-SUPERVISOR **/

    //         $hasHigherRoles = collect($formRoles)->intersect([4, 5, 6])->isNotEmpty();
    //         $hasSvSignature = isset($updatedSignatureData['sv_signature']);
    //         $hasCoSvSignature = isset($updatedSignatureData['cosv_signature']);

    //         $allSigned = $hasCoSv ? ($hasSvSignature && $hasCoSvSignature) : $hasSvSignature;

    //         if ($allSigned) {
    //             $finalStatus = !$hasHigherRoles
    //                 ? ($isHaveEvaluation ? 7 : 3)
    //                 : 2;
    //         } else {
    //             $finalStatus = 1;
    //         }
    //     } else {
    //         /** HANDLE COMMITTEE / DEPUTY DEAN / DEAN **/

    //         $roleSignatures = [
    //             4 => in_array(4, $formRoles) ? isset($updatedSignatureData['comm_signature_date']) : true,
    //             5 => in_array(5, $formRoles) ? isset($updatedSignatureData['deputy_dean_signature_date']) : true,
    //             6 => in_array(6, $formRoles) ? isset($updatedSignatureData['dean_signature_date']) : true,
    //         ];

    //         $allSigned = collect($roleSignatures)->only($formRoles)->every(fn($signed) => $signed);

    //         $finalStatus = $allSigned
    //             ? ($isHaveEvaluation ? 7 : 3)
    //             : 2;
    //     }

    //     /** UPDATE STUDENT ACTIVITY STATUS **/
    //     $updatedActivity->update(['sa_status' => $finalStatus]);

    //     /** HANDLE FINAL STATUS OF SUBMISSION **/
    //     if ($finalStatus == 3) {
    //         $this->finalizeSubmission($student, $updatedActivity->activity_id);
    //         $this->sendSubmissionNotification($student, 1, $activity->act_name, 6, $role);
    //     }

    //     //[2] -- CORRECTION FORM

    //     /* HANDLE SV / COSV LOGIC [SV: 2, COSV: 3] */
    //     if (in_array($role, [2, 3])) {
    //         $formRoles = DB::table('activity_forms as a')
    //             ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //             ->where('a.id', $afID)
    //             ->where('b.ff_category', 6)
    //             ->pluck('b.ff_signature_role')
    //             ->unique()->toArray();

    //         $hasHigherRoles = collect($formRoles)->intersect([4, 5, 6, 8])->isNotEmpty();

    //         $hasSvSignature = isset($updatedSignatureData['sv_signature']);
    //         $hasCoSvSignature = isset($updatedSignatureData['cosv_signature']);

    //         if ($hasCoSv) {
    //             $allSigned = $hasSvSignature && $hasCoSvSignature;
    //         } else {
    //             $allSigned = $hasSvSignature;
    //         }

    //         if ($allSigned) {
    //             if (!$hasHigherRoles) {
    //                 $finalStatus = 5;
    //             } else {
    //                 $finalStatus = 3;
    //             }
    //         } else {
    //             $finalStatus = 2;
    //         }

    //         $updatedCorrection->update(['ac_status' => $finalStatus]);

    //         if ($finalStatus == 5) {
    //             $this->finalizeCorrection($student, $updatedCorrection);
    //         }
    //     }
    //     /* HANDLE EXAMINER/PANEL LOGIC */ elseif ($role == 8) {
    //         // 1) Load all roles from the form
    //         $formRoles = DB::table('activity_forms as a')
    //             ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //             ->where('a.id', $afID)
    //             ->where('b.ff_category', 6)
    //             ->pluck('b.ff_signature_role')
    //             ->unique()
    //             ->toArray();

    //         // 2) Check if there *are* any higher‐level approvers
    //         $hasHigherRoles = collect($formRoles)
    //             ->intersect([4, 5, 6])   // Committee=4, Deputy Dean=5, Dean=6
    //             ->isNotEmpty();

    //         // 3) Check all examiners have signed
    //         $examKeys = DB::table('form_fields')
    //             ->where('af_id', $afID)
    //             ->where('ff_category', 6)
    //             ->where('ff_signature_role', 8)
    //             ->pluck('ff_signature_key')
    //             ->toArray();

    //         $allSigned = collect($examKeys)->every(
    //             fn($key) =>
    //             isset($updatedSignatureData[$key]) && !empty($updatedSignatureData[$key])
    //         );

    //         // 4) Determine new status
    //         if (! $allSigned) {
    //             // still waiting on at least one examiner
    //             $newStatus = 3;
    //         } elseif ($hasHigherRoles) {
    //             // examiners done, move to Committee/DD/Dean
    //             $newStatus = 4;
    //         } else {
    //             // examiners were the last approvers → complete
    //             $newStatus = 5;
    //         }

    //         // 5) Persist and finalize if complete
    //         $updatedCorrection->update(['ac_status' => $newStatus]);

    //         if ($newStatus === 5) {
    //             $this->finalizeCorrection($student, $updatedCorrection);
    //         }
    //     }
    //     /* HANDLE COMM/DD/DEAN LOGIC */ elseif (in_array($role, [4, 5, 6])) {

    //         $formRoles = DB::table('activity_forms as a')
    //             ->join('form_fields as b', 'a.id', '=', 'b.af_id')
    //             ->where('a.id', $afID)
    //             ->where('b.ff_category', 6)
    //             ->pluck('b.ff_signature_role')
    //             ->unique()->toArray();

    //         $roleSignatures = [
    //             4 => in_array(4, $formRoles) ? isset($updatedSignatureData['comm_signature_date']) : true,
    //             5 => in_array(5, $formRoles) ? isset($updatedSignatureData['deputy_dean_signature_date']) : true,
    //             6 => in_array(6, $formRoles) ? isset($updatedSignatureData['dean_signature_date']) : true,
    //         ];

    //         $allSigned = collect($roleSignatures)->only($formRoles)->every(fn($signed) => $signed);

    //         $finalStatus = $allSigned ? 5 : 4;
    //         $updatedCorrection->update(['ac_status' => $finalStatus]);

    //         if ($finalStatus == 5) {
    //             $this->finalizeCorrection($student, $updatedCorrection);
    //         }
    //     }
    // }

    // /* Document [Correction Form] Handler Function [Start] */
    // public function mergeStudentCorrection($actID, $student, $semester, $signatureData, $role, $userName, $status, $evaluatorIndex = null)
    // {
    //     try {
    //         $actID = decrypt($actID);

    //         if (!$student) {
    //             return back()->with('error', 'Unauthorized access : Student record is not found.');
    //         }

    //         $activity = Activity::where('id', $actID)->first()->act_name;
    //         $form = ActivityForm::where([
    //             ['activity_id', $actID],
    //             ['af_status', 1],
    //             ['af_target', 2],
    //         ])->first();

    //         if (!$form) {
    //             return back()->with('error', 'Activity form not found. Submission could not be confirmed. Please contact administrator for further assistance.');
    //         }

    //         $documentName = 'Correction-' . $student->student_matricno . '_' . str_replace(' ', '_', $activity) . '.pdf';

    //         //---------------------------------------------------------------------------//
    //         //------------------- SAVE SIGNATURE TO STUDENT_ACTIVITY --------------------//
    //         //---------------------------------------------------------------------------//

    //         // 1 - Signature Role [Student]
    //         // 1 - Document Status [Pending]
    //         $this->storeCorrectionSignature($actID, $student, $semester, $form, $signatureData, $documentName, $role, $userName, $status, $evaluatorIndex);

    //         //---------------------------------------------------------------------------//
    //         //--------------------------GENERATE ACTIVITY FORM CODE----------------------//
    //         //---------------------------------------------------------------------------//

    //         // RETRIEVE ACTIVITY PATH
    //         $progcode = strtoupper($student->programmes->prog_code);
    //         $basePath = storage_path("app/public/{$student->student_directory}/{$progcode}/{$activity}");

    //         if (!File::exists($basePath)) {
    //             return back()->with('error', 'Activity folder not found.');
    //         }

    //         // CREATE A NEW FOLDER (CORRECTION)
    //         $rawLabel = $semester->sem_label;
    //         $semesterlabel = str_replace('/', '', $rawLabel);
    //         $semesterlabel = trim($semesterlabel);
    //         $finalDocPath = $basePath . '/Correction/' . $semesterlabel;

    //         if (!File::exists($finalDocPath)) {
    //             File::makeDirectory($finalDocPath, 0755, true);
    //         }

    //         $relativePath = "{$student->student_directory}/{$progcode}/{$activity}/";

    //         $this->generateCorrectionForm($actID, $student, $semester, $form, $relativePath);

    //         //---------------------------------------------------------------------------//
    //         //--------------------------MERGE PDF DOCUMENTS CODE-------------------------//
    //         //---------------------------------------------------------------------------//

    //         // RETRIEVE PDF FILES
    //         $pdfFiles = File::files($basePath);

    //         $pdfFiles = array_filter($pdfFiles, function ($file) {
    //             return strtolower($file->getExtension()) === 'pdf';
    //         });

    //         if (empty($pdfFiles)) {
    //             return back()->with('error', 'No PDF documents found in the activity folder.' .  $basePath);
    //         }

    //         $pdf = new Fpdi();

    //         foreach ($pdfFiles as $file) {
    //             $pageCount = $pdf->setSourceFile(StreamReader::createByFile($file->getPathname()));
    //             for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
    //                 $template = $pdf->importPage($pageNo);
    //                 $size = $pdf->getTemplateSize($template);

    //                 $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
    //                 $pdf->useTemplate($template);
    //             }
    //         }

    //         // SAVE THE MERGED PDF
    //         $mergedPath =  $finalDocPath . '/' . $documentName;
    //         return $pdf->Output($mergedPath, 'F');
    //     } catch (Exception $e) {
    //         return back()->with('error', 'Oops! Error confirming submission: ' . $e->getMessage());
    //     }
    // }

    // public function generateCorrectionForm($actID, $student, $semester, $form, $finalDocRelativePath)
    // {
    //     try {

    //         $act = Activity::where('id', $actID)->first();

    //         if (!$act) {
    //             return back()->with('error', 'Activity not found.');
    //         }

    //         $formfields = FormField::where('af_id', $form->id)
    //             ->orderBy('ff_order')
    //             ->get();

    //         $faculty = Faculty::where('fac_status', 3)->first();
    //         $signatures = $formfields->where('ff_category', 6);

    //         $signatureRecord = ActivityCorrection::where([
    //             ['activity_id', $actID],
    //             ['student_id', $student->id],
    //             ['semester_id', $semester->id],
    //         ])->select('ac_signature_data')->first();

    //         $signatureData = $signatureRecord ? json_decode($signatureRecord->ac_signature_data) : null;

    //         $userData = [];

    //         $fhc = new FormHandlerController();
    //         $userData = $fhc->joinMap($formfields, $student, $act);

    //         $pdf = Pdf::loadView('student.programme.form-template.activity-document', [
    //             'title' => "Correction-" . $act->act_name . " Document",
    //             'act' => $act,
    //             'form_title' => $form->af_title,
    //             'formfields' => $formfields,
    //             'userData' => $userData,
    //             'faculty' => $faculty,
    //             'signatures' => $signatures,
    //             'signatureData' => $signatureData
    //         ]);

    //         $fileName = 'Activity_Correction_Form_' . $student->student_matricno . '_' . '.pdf';
    //         $relativePath = $finalDocRelativePath . '/' . $fileName;

    //         Storage::disk('public')->put($relativePath, $pdf->output());

    //         return $pdf->stream($fileName . '.pdf');
    //     } catch (Exception $e) {
    //         return back()->with('error', 'Oops! Error generating correction form: ' . $e->getMessage());
    //     }
    // }


    // public function storeCorrectionSignature($actID, $student, $semester, $form, $signatureData, $documentName, $signatureRole, $userData, $status, $evaluatorIndex = null)
    // {
    //     try {
    //         if (! $signatureData) return;

    //         $correction = ActivityCorrection::firstOrNew([
    //             'activity_id' => $actID,
    //             'student_id' => $student->id,
    //             'semester_id' => $semester->id,
    //         ]);

    //         $existing = $correction->ac_signature_data
    //             ? json_decode($correction->ac_signature_data, true)
    //             : [];

    //         // 1) Load all signature fields for this role, ordered by ff_order:
    //         $fields = FormField::where('af_id', $form->id)
    //             ->where('ff_category', 6)
    //             ->where('ff_signature_role', $signatureRole)
    //             ->orderBy('ff_order')
    //             ->get();  // [Field1, Field2, ...]

    //         // 2) Pick your field:
    //         if ($signatureRole === 8 && is_int($evaluatorIndex)) {
    //             // Examiner: use your slot
    //             $signatureField = $fields->get($evaluatorIndex);
    //         } else {
    //             // SV/CoSV/Committee/etc: first empty
    //             $signatureField = null;
    //             foreach ($fields as $f) {
    //                 if (empty($existing[$f->ff_signature_key])) {
    //                     $signatureField = $f;
    //                     break;
    //                 }
    //             }
    //         }

    //         if (! $signatureField) {
    //             return back()->with(
    //                 'error',
    //                 'All required signatures for your role are already completed.'
    //             );
    //         }

    //         $sigKey  = $signatureField->ff_signature_key;
    //         $dateKey = $signatureField->ff_signature_date_key;

    //         // 3) Build new block
    //         if ($signatureRole === 1) {
    //             $block = [
    //                 $sigKey        => $signatureData,
    //                 $dateKey       => now()->format('d M Y'),
    //                 "{$sigKey}_name" => $student->student_name,
    //                 "{$sigKey}_role" => 'Student',
    //                 "{$sigKey}_is_cross_approval" => false,
    //             ];
    //         } else {
    //             $names = [
    //                 1 => 'Committee',
    //                 2 => 'Lecturer',
    //                 3 => 'Deputy Dean',
    //                 4 => 'Dean'
    //             ];
    //             $roleName = $names[$userData->staff_role] ?? 'Staff';
    //             $block = [
    //                 $sigKey        => $signatureData,
    //                 $dateKey       => now()->format('d M Y'),
    //                 "{$sigKey}_name" => $userData->staff_name,
    //                 "{$sigKey}_role" => $roleName,
    //                 "{$sigKey}_is_cross_approval" => false,
    //             ];
    //         }

    //         // 4) Merge + save
    //         $merged = array_merge($existing, $block);
    //         $correction->ac_signature_data   = json_encode($merged);
    //         $correction->ac_final_submission  = $documentName;
    //         $correction->ac_status            = $status;
    //         $correction->save();
    //     } catch (Exception $e) {
    //         return back()->with('error', 'Error storing signature: ' . $e->getMessage());
    //     }
    // }
    /* Document [Correction Form] Handler Function [End] */


    /* INDICATOR [STATUS] */
    // 1: CORRECTION : PENDING STUDENT ACTION
    // 2: CORRECTION : PENDING SUPERVISION ACTION
    // 3: CORRECTION : PENDING EXAMINER/PANEL ACTION
    // 4: CORRECTION : PENDING COMM/DD/DEAN ACTION
    // 5: CORRECTION : APPROVE & COMPLETED


    /* INDICATOR [ROLE] */
    // 1: STUDENT [NOT APPLICABLE FOR THIS LOGIC]
    // 2: MAIN SUPERVISOR
    // 3: COSV
    // 8: EXAMINER/PANEL (EXAMINER 1 / PANEL 1 / EXAMINER 2 / PANEL 2 .. etc)
    // 4: COMMITTEE
    // 5: DEPUTY DEAN
    // 6: DEAN

    /* INDICATOR [ROLE] AND STATUS AFTER APPROVAL */
    // 2: MAIN SUPERVISOR [3]
    // 3: COSV [3]
    // 8: EXAMINER/PANEL (EXAMINER 1 / PANEL 1 / EXAMINER 2 / PANEL 2 .. etc) [4]
    // 4: COMMITTEE [5]
    // 5: DEPUTY DEAN [5]
    // 6: DEAN [5]


    /* Store Activity Form Signature [Staff] - Function */
    // public function storeEvaluationSignature($student, $form, $signatureData, $evaluation, $nomination, $mode)
    // {
    //     try {
    //         if ($signatureData) {

    //             /* LOAD SIGNATURE FIELD DATA */
    //             $signatureFields = FormField::where('af_id', $form->id)
    //                 ->where('ff_category', 6)
    //                 ->get();

    //             /* LOAD EXISTING SIGNATURE FIELD */
    //             $existingData = $evaluation->evaluation_signature_data
    //                 ? json_decode($evaluation->evaluation_signature_data, true)
    //                 : [];

    //             /* LOAD EVALUATOR DATA */
    //             $evaluators = Evaluator::where('nom_id', $nomination->id)
    //                 ->where('eva_status', 3)
    //                 ->with('staff')
    //                 ->orderBy('id')
    //                 ->get();

    //             /* LOAD CHAIRMAN DATA */
    //             $chairman = $evaluators->where('eva_role', 2)->first();

    //             /* LOAD EXAMINER/PANEL DATA */
    //             $otherEvaluators = $evaluators->where('eva_role', 1)->values();

    //             /* STORE SIGNATURE LOGIC */
    //             foreach ($signatureFields as $signatureField) {
    //                 $signatureKey = $signatureField->ff_signature_key;
    //                 $dateKey = $signatureField->ff_signature_date_key;

    //                 if (!isset($signatureData[$signatureKey]) || empty($signatureData[$signatureKey])) {
    //                     continue;
    //                 }

    //                 $role = null;
    //                 $signerName = null;

    //                 if ($signatureField->ff_signature_role == 1) {
    //                     /* STUDENT SIGNATURE LOGIC */
    //                     $role = 'Student';
    //                     $signerName = $student->student_name;
    //                 } else {
    //                     if ($mode == 6) {
    //                         /* CHAIRMAN FORM MODE [MASS SIGN MODE] */

    //                         if (str_contains(strtolower($signatureKey), 'chair')) {
    //                             if ($chairman && $chairman->staff) {
    //                                 $role = $signatureField->ff_label;
    //                                 $signerName = $chairman->staff->staff_name;
    //                             } else {
    //                                 continue;
    //                             }
    //                         } elseif (preg_match('/(examiner|panel|reviewer|evaluator|assessor)(?:_(\d+))?/i', $signatureKey, $matches)) {
    //                             $keyword = strtolower($matches[1]);
    //                             $index = isset($matches[2]) ? intval($matches[2]) - 1 : 0;

    //                             if (isset($otherEvaluators[$index]) && $otherEvaluators[$index]->staff) {
    //                                 $role = $signatureField->ff_label;
    //                                 $signerName = $otherEvaluators[$index]->staff->staff_name;
    //                             } else {
    //                                 continue;
    //                             }
    //                         } else {
    //                             continue;
    //                         }
    //                     } elseif ($mode == 5) {
    //                         /* EXAMINER/PANEL FORM MODE [INDIVIDUAL SIGN MODE] */

    //                         $currentStaff = auth()->user();

    //                         /* CHECK STAFF IS ASSIGNED TO THE EVALUATION */
    //                         $matchedEvaluator = $evaluators->first(function ($eva) use ($currentStaff) {
    //                             return $eva->staff_id == $currentStaff->id;
    //                         });

    //                         if (!$matchedEvaluator) {
    //                             continue; // not assigned → skip
    //                         }

    //                         // Allow chairman also to sign in his own form part
    //                         if (str_contains(strtolower($signatureKey), 'chair') && $matchedEvaluator->eva_role == 2) {
    //                             $role = $signatureField->ff_label;
    //                             $signerName = $matchedEvaluator->staff->staff_name;
    //                         }
    //                         // For examiner/panel fields
    //                         elseif (preg_match('/(examiner|panel|reviewer|evaluator|assessor)/i', $signatureKey)) {
    //                             if ($matchedEvaluator->eva_role == 1) {
    //                                 $role = $signatureField->ff_label;
    //                                 $signerName = $matchedEvaluator->staff->staff_name;
    //                             } else {
    //                                 continue;
    //                             }
    //                         } else {
    //                             continue;
    //                         }
    //                     }

    //                     // Other unknown mode → ignore
    //                     else {
    //                         continue;
    //                     }
    //                 }

    //                 $newSignatureData = [
    //                     $signatureKey => $signatureData[$signatureKey],
    //                     $dateKey => now()->format('d M Y'),
    //                     $signatureKey . '_is_cross_approval' => false,
    //                     $signatureKey . '_name' => $signerName,
    //                     $signatureKey . '_role' => $role
    //                 ];

    //                 $existingData = array_merge($existingData, $newSignatureData);
    //             }

    //             $evaluation->evaluation_signature_data = json_encode($existingData);
    //             $evaluation->save();
    //         }
    //     } catch (Exception $e) {
    //         throw new Exception('Signature storage error: ' . $e->getMessage());
    //     }
    // }


     // public function storeEvaluationSignature($student, $form, $signatureData, $evaluation, $nomination, $mode)
    // {
    //     try {
    //         if (!$signatureData) {
    //             return;
    //         }

    //         // 1) LOAD SIGNATURE FIELD DEFINITIONS (all signatures on this form)
    //         $signatureFields = FormField::where('af_id', $form->id)
    //             ->where('ff_category', 6)
    //             ->get();

    //         // 2) LOAD EXISTING SIGNATURE PAYLOAD (JSON → array)
    //         $existingData = $evaluation->evaluation_signature_data
    //             ? json_decode($evaluation->evaluation_signature_data, true)
    //             : [];

    //         // 3) LOAD EVALUATORS (examiners & chairman) FOR THIS NOMINATION
    //         $evaluators = Evaluator::where('nom_id', $nomination->id)
    //             ->where('eva_status', 3)
    //             ->with('staff')
    //             ->orderBy('id')
    //             ->get();

    //         $chairman        = $evaluators->where('eva_role', 2)->first();
    //         $otherEvaluators = $evaluators->where('eva_role', 1)->values();

    //         // Helper: do not overwrite an already-signed field
    //         $isAlreadySigned = function ($key) use ($existingData) {
    //             return isset($existingData[$key]) && !empty($existingData[$key]);
    //         };

    //         // Helper: readable role labels (fallback if you don't want ff_label)
    //         $roleLabelMap = [
    //             1 => 'Student',
    //             2 => 'Supervisor',
    //             3 => 'Co-Supervisor',
    //             4 => 'Committee',
    //             5 => 'Deputy Dean',
    //             6 => 'Dean',
    //             8 => 'Examiner/Panel',
    //         ];

    //         if ($mode == 6) {
    //             // ===== KEEP YOUR EXISTING MODE 6 LOGIC AS-IS =====
    //             foreach ($signatureFields as $signatureField) {
    //                 $signatureKey = $signatureField->ff_signature_key;
    //                 $dateKey      = $signatureField->ff_signature_date_key;

    //                 if (empty($signatureData[$signatureKey])) {
    //                     continue;
    //                 }
    //                 if ($isAlreadySigned($signatureKey)) {
    //                     continue;
    //                 }

    //                 $role       = null;
    //                 $signerName = null;

    //                 if ($signatureField->ff_signature_role == 1) {
    //                     $role       = 'Student';
    //                     $signerName = $student->student_name;
    //                 } else {
    //                     // Mass sign: chairman & all examiners (your original patterns)
    //                     if (str_contains(strtolower($signatureKey), 'chair')) {
    //                         if ($chairman && $chairman->staff) {
    //                             $role       = $signatureField->ff_label ?: 'Chairman';
    //                             $signerName = $chairman->staff->staff_name;
    //                         } else {
    //                             continue;
    //                         }
    //                     } elseif (preg_match('/(examiner|panel|reviewer|evaluator|assessor)(?:_(\d+))?/i', $signatureKey, $m)) {
    //                         $index = isset($m[2]) ? max(0, intval($m[2]) - 1) : 0;
    //                         if (isset($otherEvaluators[$index]) && $otherEvaluators[$index]->staff) {
    //                             $role       = $signatureField->ff_label ?: 'Examiner/Panel';
    //                             $signerName = $otherEvaluators[$index]->staff->staff_name;
    //                         } else {
    //                             continue;
    //                         }
    //                     } else {
    //                         // Allow committee/DD/Dean too in mass mode, driven by role id
    //                         if (in_array($signatureField->ff_signature_role, [4, 5, 6])) {
    //                             // No specific person lookup was provided here. If you
    //                             // maintain role holders, inject lookups. Otherwise skip.
    //                             continue;
    //                         }
    //                         continue;
    //                     }
    //                 }

    //                 $newSignatureData = [
    //                     $signatureKey                         => $signatureData[$signatureKey],
    //                     $dateKey                              => now()->format('d M Y'),
    //                     $signatureKey . '_is_cross_approval'  => false,
    //                     $signatureKey . '_name'               => $signerName,
    //                     $signatureKey . '_role'               => $role,
    //                 ];
    //                 $existingData = array_merge($existingData, $newSignatureData);
    //             }

    //             $evaluation->evaluation_signature_data = json_encode($existingData);
    //             $evaluation->save();
    //             return;
    //         }

    //         // ===== MODE 5: INDIVIDUAL SIGN (ROBUST) =====

    //         $currentStaff = auth()->user();

    //         // 4) Determine current staff’s relationship to this student/evaluation

    //         // 4a) Is current staff one of the assigned evaluators?
    //         $matchedEvaluator = $evaluators->first(fn($eva) => $eva->staff_id == $currentStaff->id);
    //         $isChairman       = $matchedEvaluator && $matchedEvaluator->eva_role == 2;
    //         $isExaminer       = $matchedEvaluator && $matchedEvaluator->eva_role == 1;

    //         // 4b) Is current staff a Supervisor or Co-Supervisor of this student?
    //         // Adjust the column name used to store SV type if needed.
    //         $supervision = Supervision::where('student_id', $student->id)
    //             ->where('staff_id', $currentStaff->id)
    //             ->first();

    //         $svTypeRaw = $supervision->supervisor_role ?? null;

    //         $isSV   = $supervision && intval($svTypeRaw) === 1;
    //         $isCoSV = $supervision && intval($svTypeRaw) === 2;
    //         $isAnySV = $supervision !== null;

    //         // 4c) Committee / DD / Dean via staff_role (match your mapping)
    //         // If your app uses a different attribute, adjust here.
    //         $staffRole = intval($currentStaff->staff_role ?? 0); // 1=Committee, 3=DD, 4=Dean (per your note)
    //         $isCommittee  = $staffRole === 1;
    //         $isDeputyDean = $staffRole === 3;
    //         $isDean       = $staffRole === 4;

    //         // Helper: can current staff sign a field of role R?
    //         $canSignByRole = function (int $sigRole, string $sigKey) use (
    //             $isExaminer,
    //             $isChairman,
    //             $otherEvaluators,
    //             $currentStaff,
    //             $isSV,
    //             $isCoSV,
    //             $isAnySV,
    //             $isCommittee,
    //             $isDeputyDean,
    //             $isDean
    //         ) {
    //             switch ($sigRole) {
    //                 case 1: // Student – not a staff; block here
    //                     return [false, null, null, false];

    //                 case 2: // Supervisor
    //                     if ($isSV)   return [true, $currentStaff->staff_name, 'Supervisor', false];
    //                     if ($isCoSV) return [true, $currentStaff->staff_name, 'Co-Supervisor', true]; // cross (CoSV signing SV slot)
    //                     return [false, null, null, false];

    //                 case 3: // Co-Supervisor
    //                     if ($isCoSV) return [true, $currentStaff->staff_name, 'Co-Supervisor', false];
    //                     if ($isSV)   return [true, $currentStaff->staff_name, 'Supervisor', true];    // cross (SV signing CoSV slot)
    //                     return [false, null, null, false];

    //                 case 4: // Committee
    //                     return $isCommittee ? [true, $currentStaff->staff_name, 'Committee', false] : [false, null, null, false];

    //                 case 5: // Deputy Dean
    //                     return $isDeputyDean ? [true, $currentStaff->staff_name, 'Deputy Dean', false] : [false, null, null, false];

    //                 case 6: // Dean
    //                     return $isDean ? [true, $currentStaff->staff_name, 'Dean', false] : [false, null, null, false];

    //                 case 8: // Examiner/Panel (with possible index in key)
    //                     if (!$isExaminer && !$isChairman) {
    //                         return [false, null, null, false];
    //                     }

    //                     // Examiner field usually comes as examiner_1, panel_2, etc.
    //                     if (preg_match('/(?:examiner|panel|reviewer|evaluator|assessor)_(\d+)/i', $sigKey, $m)) {
    //                         $idx = max(0, intval($m[1]) - 1);
    //                         if (!isset($otherEvaluators[$idx])) {
    //                             return [false, null, null, false];
    //                         }
    //                         $eva = $otherEvaluators[$idx];
    //                         // Only the matching examiner can sign this indexed field
    //                         if ($eva->staff_id == $currentStaff->id) {
    //                             return [true, $currentStaff->staff_name, 'Examiner/Panel', false];
    //                         }
    //                         return [false, null, null, false];
    //                     } else {
    //                         // Non-indexed examiner key: allow any assigned examiner
    //                         if ($isExaminer) {
    //                             return [true, $currentStaff->staff_name, 'Examiner/Panel', false];
    //                         }
    //                         return [false, null, null, false];
    //                     }

    //                 default:
    //                     return [false, null, null, false];
    //             }
    //         };

    //         // 5) Iterate fields & store signatures the user actually provided
    //         foreach ($signatureFields as $signatureField) {
    //             $signatureKey = $signatureField->ff_signature_key;
    //             $dateKey      = $signatureField->ff_signature_date_key;
    //             $sigRoleId    = intval($signatureField->ff_signature_role);

    //             // Skip if user didn't submit this field
    //             if (empty($signatureData[$signatureKey])) {
    //                 continue;
    //             }

    //             // Do not overwrite an existing signature
    //             if ($isAlreadySigned($signatureKey)) {
    //                 continue;
    //             }

    //             // Student signatures (role 1) – allow if the form collects it here.
    //             if ($sigRoleId === 1) {
    //                 $newSignatureData = [
    //                     $signatureKey                        => $signatureData[$signatureKey],
    //                     $dateKey                             => now()->format('d M Y'),
    //                     $signatureKey . '_is_cross_approval' => false,
    //                     $signatureKey . '_name'              => $student->student_name,
    //                     $signatureKey . '_role'              => $roleLabelMap[1],
    //                 ];
    //                 $existingData = array_merge($existingData, $newSignatureData);
    //                 continue;
    //             }

    //             // Staff roles: verify permission to sign this exact slot
    //             [$allowed, $signerName, $roleText, $isCross] = $canSignByRole($sigRoleId, $signatureKey);

    //             // Additionally, keep your chairman self-sign on MODE 5 (if field key mentions chair)
    //             if (!$allowed && $isChairman && str_contains(strtolower($signatureKey), 'chair')) {
    //                 $allowed    = true;
    //                 $signerName = $currentStaff->staff_name;
    //                 $roleText   = 'Chairman';
    //                 $isCross    = false;
    //             }

    //             if (!$allowed) {
    //                 continue;
    //             }

    //             // Use field label if you prefer exact form wording; otherwise roleText fallback
    //             $roleLabel = $signatureField->ff_label ?: ($roleLabelMap[$sigRoleId] ?? $roleText);

    //             $newSignatureData = [
    //                 $signatureKey                        => $signatureData[$signatureKey],
    //                 $dateKey                             => now()->format('d M Y'),
    //                 $signatureKey . '_is_cross_approval' => (bool) $isCross,
    //                 $signatureKey . '_name'              => $signerName,
    //                 $signatureKey . '_role'              => $roleLabel,
    //             ];

    //             $existingData = array_merge($existingData, $newSignatureData);
    //         }

    //         // 6) Persist
    //         $evaluation->evaluation_signature_data = json_encode($existingData);
    //         $evaluation->save();
    //     } catch (Exception $e) {
    //         throw new Exception('Signature storage error: ' . $e->getMessage());
    //     }
    // }


     // if (in_array($role, [2, 3])) {
                //     /* HANDLE SUPERVISOR LOGIC */
                //     $formRoles = DB::table('activity_forms as a')
                //         ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                //         ->where('a.activity_id', $updatedActivity->activity_id)
                //         ->where('b.ff_category', 6)
                //         ->where('a.id', $afID)
                //         ->where('a.af_target', 1)
                //         ->pluck('b.ff_signature_role')
                //         ->unique()
                //         ->toArray();

                //     $hasHigherRoles = collect($formRoles)->intersect([4, 5, 6])->isNotEmpty();

                //     $hasSvSignature = isset($updatedSignatureData['sv_signature']);
                //     $hasCoSvSignature = isset($updatedSignatureData['cosv_signature']);

                //     if ($hasCoSv) {
                //         $allSigned = $hasSvSignature && $hasCoSvSignature;
                //     } else {
                //         $allSigned = $hasSvSignature;
                //     }

                //     if ($allSigned) {
                //         if (!$hasHigherRoles) {
                //             $finalStatus = $isHaveEvaluation ? 7 : 3;
                //         } else {
                //             $finalStatus = 2;
                //         }
                //     } else {
                //         $finalStatus = 1;
                //     }

                //     $updatedActivity->update(['sa_status' => $finalStatus]);

                //     if ($finalStatus == 3) {
                //         $this->finalizeSubmission($student, $updatedActivity->activity_id);
                //         $this->sendSubmissionNotification($student, 1, $activity->act_name, 6, $role);
                //     }
                // } else {
                //     /* HANDLE COMMITTEE/ DEPUTY DEAN / DEAN LOGIC */
                //     $formRoles = DB::table('activity_forms as a')
                //         ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                //         ->where('a.activity_id', $updatedActivity->activity_id)
                //         ->where('b.ff_category', 6)
                //         ->pluck('b.ff_signature_role')
                //         ->where('a.id', $afID)
                //         ->where('a.af_target', 1)
                //         ->unique()->toArray();

                //     $roleSignatures = [
                //         4 => in_array(4, $formRoles) ? isset($updatedSignatureData['comm_signature_date']) : true,
                //         5 => in_array(5, $formRoles) ? isset($updatedSignatureData['deputy_dean_signature_date']) : true,
                //         6 => in_array(6, $formRoles) ? isset($updatedSignatureData['dean_signature_date']) : true,
                //     ];

                //     $allSigned = collect($roleSignatures)->only($formRoles)->every(fn($signed) => $signed);

                //     $finalStatus = $allSigned ? ($isHaveEvaluation ? 7 : 3) : 2;
                //     $updatedActivity->update(['sa_status' => $finalStatus]);

                //     if ($finalStatus == 3) {
                //         $this->finalizeSubmission($student, $updatedActivity->activity_id);
                //         $this->sendSubmissionNotification($student, 1, $activity->act_name, 6, $role);
                //     }
                // }
}
