<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Activity;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class SOPController extends Controller
{
    /* Activity Setting */
    public function activitySetting(Request $req)
    {
        try {
            if ($req->ajax()) {

                $data = DB::table('activities')
                    ->select('id', 'act_name')
                    ->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('action', function ($row) {

                    $isReferenced = false;
                    // $isReferenced = DB::table('procedures')->where('act_id', $row->id)->exists();

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
            return view('staff.sop.activity-setting', [
                'title' => 'Activity Setting',
                'acts' => Activity::all()
            ]);
        } catch (Exception $e) {
            return abort(500);
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
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'addModal');
        }
        try {
            $validated = $validator->validated();
            Activity::create([
                'act_name' => $validated['act_name'],
            ]);

            return back()->with('success', 'Activity added successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error adding activity.');
        }
    }

    public function updateActivity(Request $req, $id)
    {
        $id = decrypt($id);
        $validator = Validator::make($req->all(), [
            'act_name_up' => 'required|string|unique:activities,act_name,' . $id,
        ], [], [
            'act_name_up' => 'activity name',
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'updateModal-' . $id);
        }

        try {
            $validated = $validator->validated();
            Activity::find($id)->update([
                'act_name' => $validated['act_name_up'],
            ]);

            return back()->with('success', 'Activity updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating activity.');
        }
    }

    public function deleteActivity($id, $opt)
    {
        try {
            $id = decrypt($id);
            if ($opt == 1) {
                Activity::where('id', $id)->delete();
                return  back()->with('success', 'Activity deleted successfully.');
            } elseif ($opt == 2) {
                return abort(500);
            }
        } catch (Exception $e) {
            return  back()->with('error', 'Oops! Error deleting activity.');
        }
    }

    /* Document Setting */
    public function documentSetting(Request $req)
    {
        try {
            // if ($req->ajax()) {

            //     $data = DB::table('activities')
            //         ->select('id', 'act_name')
            //         ->get();

            //     $table = DataTables::of($data)->addIndexColumn();

            //     $table->addColumn('action', function ($row) {

            //         $isReferenced = false;
            //         // $isReferenced = DB::table('procedures')->where('act_id', $row->id)->exists();

            //         $buttonEdit =
            //             '
            //                 <a href="javascript: void(0)" class="avtar avtar-xs btn-light-primary" data-bs-toggle="modal"
            //                     data-bs-target="#updateModal-' . $row->id . '">
            //                     <i class="ti ti-edit f-20"></i>
            //                 </a>
            //             ';

            //         if (!$isReferenced) {
            //             $buttonRemove =
            //                 '
            //                     <a href="javascript: void(0)" class="avtar avtar-xs  btn-light-danger" data-bs-toggle="modal"
            //                         data-bs-target="#deleteModal-' . $row->id . '">
            //                         <i class="ti ti-trash f-20"></i>
            //                     </a>
            //                 ';
            //         } else {


            //             $buttonRemove =
            //                 '
            //                     <a href="javascript: void(0)" class="avtar avtar-xs  btn-light-danger disabled-a" data-bs-toggle="modal"
            //                         data-bs-target="#disableModal">
            //                         <i class="ti ti-trash f-20"></i>
            //                     </a>
            //                 ';
            //         }

            //         return $buttonEdit . $buttonRemove;
            //     });

            //     $table->rawColumns(['fac_status', 'action']);

            //     return $table->make(true);
            // }
            return view('staff.sop.document-setting', [
                'title' => 'Activity Setting',
                'docs' => Document::all(),
                'acts' => Activity::all()
            ]);
        } catch (Exception $e) {
            return abort(500);
        }
    }

    public function viewDocumentByActivity($id)
    {
        try {
            $data = DB::table('documents')
                ->where('activity_id', $id)
                ->get();
            return response()->json([
                    'success' => true,
                    'data' => $data,
                ],200
            );
        } catch (Exception $e) {
            return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ],500
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
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'addDocModal');
        }
        try {
            $validated = $validator->validated();
            $document = Document::create([
                'doc_name' => $validated['doc_name'],
                'isRequired' => $validated['isRequired'],
                'isShowDoc' => $validated['isShowDoc'],
                'doc_status' => $validated['doc_status'],
                'activity_id' => $validated['act_id'],
            ]);
            return response()->json(['success' => true, 'document' => $document], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateDocument(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'doc_name' => 'required|string',
            'isRequired' => 'required|integer',
            'isShowDoc' => 'required|integer',
            'doc_status' => 'required|integer',
        ], [], [
            'doc_name' => 'document name',
            'isRequired' => 'document required',
            'isShowDoc' => 'document appear in form',
            'doc_status' => 'document status',
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'updateModal-' . $id);
        }

        try {
            $validated = $validator->validated();
            $document = Document::find($req->id)->update([
                'doc_name' => $validated['doc_name'],
                'isRequired' => $validated['isRequired'],
                'isShowDoc' => $validated['isShowDoc'],
                'doc_status' => $validated['doc_status'],
                'activity_id' => $document->activity_id,
            ]);
            return response()->json(['success' => true, 'document' => $document], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);

        }
    }

    public function deleteDocument($id, $opt)
    {
        try {
            $id = decrypt($id);
            if ($opt == 1) {
                Activity::where('id', $id)->delete();
                return  back()->with('success', 'Activity deleted successfully.');
            } elseif ($opt == 2) {
                Activity::where('id', $id)->update(['fac_status' => 0]);
                return  back()->with('success', 'Activity disabled successfully.');
            }
        } catch (Exception $e) {
            return  back()->with('error', 'Oops! Error deleting activity.');
        }
    }
}
