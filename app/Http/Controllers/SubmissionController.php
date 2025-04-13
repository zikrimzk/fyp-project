<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\Semester;
use App\Models\Programme;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SubmissionController extends Controller
{
    /* Programme Overview [Student] [UNFINISHED] */
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
            // dd($document);

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

    /* Submission Management [Staff] */
    public function submissionManagement(Request $req)
    {
        try {
            if ($req->ajax()) {

                $data = DB::table('students as a')
                    ->join('semesters as b', 'b.id', '=', 'a.semester_id')
                    ->join('programmes as c', 'c.id', '=', 'a.programme_id')
                    ->join('submissions as d', 'd.student_id', '=', 'a.id')
                    ->join('documents as e', 'e.id', '=', 'd.document_id')
                    ->join('activities as f', 'f.id', '=', 'e.activity_id')
                    ->select(
                        'a.*',
                        'b.sem_label',
                        'c.prog_code',
                        'c.prog_mode',
                        'd.id as submission_id',
                        'd.submission_status',
                        'd.submission_date',
                        'd.submission_duedate',
                        'e.id as document_id',
                        'e.doc_name as document_name',
                        'f.id as activity_id',
                        'f.act_name as activity_name'
                    );

                // Apply filters
                if ($req->has('faculty') && !empty($req->input('faculty'))) {
                    $data->where('fac_id', $req->input('faculty'));
                }
                if ($req->has('programme') && !empty($req->input('programme'))) {
                    $data->where('programme_id', $req->input('programme'));
                }
                if ($req->has('semester') && !empty($req->input('semester'))) {
                    $data->where('semester_id', $req->input('semester'));
                }
                if ($req->has('status') && !empty($req->input('status'))) {
                    $data->where('student_status', $req->input('status'));
                }

                $data = $data->get();

                $groupedData = collect($data)
                    ->groupBy('activity_name')
                    ->sortKeys()
                    ->flatMap(function ($items, $activity) {
                       
                        $items = $items->sortBy(['activity_id', 'student_name']);

                        $header = (object)[
                            'is_group_header' => true,
                            'activity_name' => $activity,
                            'document_count' => count($items),
                        ];

                        return collect([$header])->concat($items);
                    });

                $table = DataTables::of($groupedData)->addIndexColumn();

                $table->addColumn('checkbox', function ($row) {
                    if (!empty($row->is_group_header)) return ''; 
                    return '<input type="checkbox" class="user-checkbox form-check-input" value="' . $row->id . '">';
                });

                $table->addColumn('student_photo', function ($row) {
                    if (!empty($row->is_group_header)) return '';
                    $mode = match ($row->prog_mode) {
                        "FT" => "Full-Time",
                        "PT" => "Part-Time",
                        default => "N/A",
                    };

                    $photoUrl = empty($row->student_photo)
                        ? asset('assets/images/user/default-profile-1.jpg')
                        : asset('storage/' . $row->student_directory . '/photo/' . $row->student_photo);

                    return '
                        <div class="d-flex align-items-center" >
                            <div class="me-3">
                                <img src="' . $photoUrl . '" alt="user-image" class="rounded-circle border" style="width: 50px; height: 50px; object-fit: cover;">
                            </div>
                            <div style="max-width: 200px;">
                                <span class="mb-0 fw-medium">' . $row->student_name . '</span>
                                <small class="text-muted d-block fw-medium">' . $row->student_email . '</small>
                                <small class="text-muted d-block fw-medium">' . $row->student_matricno . '</small>
                                <small class="text-muted d-block fw-medium">' . $row->prog_code . ' (' . $mode . ')</small>
                            </div>
                        </div>
                    ';
                });

                $table->addColumn('document_name', function ($row) {
                    if (!empty($row->is_group_header)) return '';
                    return $row->document_name;
                });

                $table->addColumn('submission_status', function ($row) {
                    if (!empty($row->is_group_header)) return '';
                    $status = '';

                    if ($row->student_status == 1) {
                        $status = '<span class="badge bg-light-warning">' . 'No Attempt' . '</span>';
                    } elseif ($row->student_status == 2) {
                        $status = '<span class="badge bg-danger">' . 'Locked' . '</span>';
                    } elseif ($row->student_status == 3) {
                        $status = '<span class="badge bg-light-success">' . 'Submitted' . '</span>';
                    } elseif ($row->student_status == 4) {
                        $status = '<span class="badge bg-light-danger">' . 'Overdue' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('action', function ($row) {
                    if (!empty($row->is_group_header)) return '';
                    $isReferenced = false;
                    $isReferenced = DB::table('supervisions')->where('student_id', $row->id)->exists();

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
                                <a href="javascript: void(0)" class="avtar avtar-xs  btn-light-warning ' . ($row->student_status == 2 ? 'disabled-a' : '') . '" data-bs-toggle="modal"
                                    data-bs-target="#disableModal-' . $row->id . '">
                                    <i class="ti ti-trash f-20"></i>
                                </a>
                            ';
                    }

                    return $buttonEdit . $buttonRemove;
                });


                $table->rawColumns(['checkbox', 'student_photo', 'document_name', 'submission_status', 'action']);

                return $table->make(true);
            }
            return view('staff.submission.submission-management', [
                'title' => 'Submission Management',
                'studs' => Student::all(),
                'current_sem' => Semester::where('sem_status', 1)->first()->sem_label ?? 'N/A',
                'progs' => Programme::all(),
                'facs' => Faculty::all(),
                'sems' => Semester::all(),
            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
            return abort(500);
        }
    }

    public function assignSubmission()
    {
        try {
            // GET DATA
            $data = DB::table('procedures as a')
                ->join('activities as b', 'a.activity_id', '=', 'b.id')
                ->join('documents as c', 'b.id', '=', 'c.activity_id')
                ->join('programmes as d', 'a.programme_id', '=', 'd.id')
                ->join('students as e', 'd.id', '=', 'e.programme_id')
                ->where('e.student_status', '=', 1)
                ->select('e.student_matricno', 'a.timeline_week', 'a.init_status', 'e.id as student_id', 'c.id as document_id')
                ->get();

            // GET CURRENT SEMESTER
            $currSem = Semester::where('sem_status', 1)->first();

            // ASSIGN SUBMISSION 
            foreach ($data as $sub) {
                $checkExists = Submission::where('student_id', $sub->student_id)
                    ->where('document_id', $sub->document_id)
                    ->exists();

                if (!$checkExists) {
                    $days = $sub->timeline_week * 7;
                    $submissionDate = Carbon::parse($currSem->sem_startdate)->addDays($days);
                    Submission::create([
                        'submission_document' => '-',
                        'submission_duedate' => $submissionDate,
                        'submission_status' => $sub->init_status,
                        'student_id' => $sub->student_id,
                        'document_id' => $sub->document_id,
                    ]);
                }
            }

            return back()->with('success', 'Submission has been assigned successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error assigning students with submission: ' . $e->getMessage());
        }
    }
}
