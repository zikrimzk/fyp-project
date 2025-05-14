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

}
