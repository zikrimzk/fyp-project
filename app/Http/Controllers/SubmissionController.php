<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{
    /* Student Activity Index */
    public function studentProgrammeOverview()
    {
        try {

            $programmeActivity = DB::table('procedures as a')
                ->join('programmes as b', 'a.programme_id', '=', 'b.id')
                ->join('activities as c', 'a.activity_id', '=', 'c.id')
                ->where('b.id', auth()->user()->programme_id)
                ->get();

            // dd($programmeActivity);
            $document = DB::table('procedures as a')
                ->join('programmes as b', 'a.programme_id', '=', 'b.id')
                ->join('activities as c', 'a.activity_id', '=', 'c.id')
                ->leftJoin('documents as d', 'c.id', '=', 'd.activity_id')
                ->where('b.id', auth()->user()->programme_id)
                ->select(
                    'c.id as activity_id',
                    'c.act_name as activity_name',
                    'd.doc_name as document_name',
                    'd.isRequired'
                )
                ->get()
                ->groupBy('activity_id');
            // dd($document);



            return view('student.programme.programme-index', [
                'title' => 'Programme Overview',
                'acts' => $programmeActivity,
                'docs' => $document,

            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
            return abort(500);
        }
    }

    public function activitySubmissionList($id)
    {
        try {

            $id = decrypt($id);
            $document = DB::table('procedures as a')
                ->join('programmes as b', 'a.programme_id', '=', 'b.id')
                ->join('activities as c', 'a.activity_id', '=', 'c.id')
                ->join('documents as d', 'c.id', '=', 'd.activity_id') 
                ->where('b.id', auth()->user()->programme_id)
                ->where('c.id', $id)
                ->select(
                    'c.id as activity_id',
                    'c.act_name as activity_name',
                    'd.doc_name as document_name',
                    'd.isRequired'
                )
                ->get();

            $activity = DB::table('activities')
                ->where('id', $id)
                ->first();

            return view('student.programme.activity-submission-list', [
                'title' => 'Submission Document List',
                'act' => $activity,
                'docs' => $document,


            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
            return abort(500);
        }
    }
}
