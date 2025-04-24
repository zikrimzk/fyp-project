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

}
