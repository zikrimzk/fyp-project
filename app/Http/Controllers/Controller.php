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

}
