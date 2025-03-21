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
        try{
            return view('staff.sop.procedure-setting',[
                'title' => 'Procedure Setting',
                'acts' => Activity::all(),
            ]);
        }
        catch(Exception $e){
            return abort(500);
        }
    }


    
}
