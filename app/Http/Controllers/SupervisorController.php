<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\Activity;
use App\Models\Document;
use App\Models\Semester;
use App\Models\Procedure;
use App\Models\Programme;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use App\Models\StudentActivity;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\MySupervisionStudentExport;

class SupervisorController extends Controller
{
    /* My Supervision Student List - Route */
    public function mySupervisionStudentList(Request $req)
    {
        try {

            if ($req->ajax()) {

                $latestSemesterSub = DB::table('student_semesters')
                    ->select('student_id', DB::raw('MAX(semester_id) as latest_semester_id'))
                    ->groupBy('student_id');

                $data = DB::table('students as a')
                    ->leftJoinSub($latestSemesterSub, 'latest', function ($join) {
                        $join->on('latest.student_id', '=', 'a.id');
                    })
                    ->leftJoin('student_semesters as ss', function ($join) {
                        $join->on('ss.student_id', '=', 'a.id')
                            ->on('ss.semester_id', '=', 'latest.latest_semester_id');
                    })
                    ->leftJoin('semesters as b', 'b.id', '=', 'ss.semester_id')
                    ->join('programmes as c', 'c.id', '=', 'a.programme_id')
                    ->join('supervisions as d', 'd.student_id', '=', 'a.id')
                    ->select('a.*', 'b.sem_label', 'c.prog_code', 'c.prog_mode', 'ss.semester_id', 'd.supervision_role')
                    ->where('d.staff_id', auth()->user()->id)
                    ->orderBy('d.supervision_role');


                if ($req->has('faculty') && !empty($req->input('faculty'))) {
                    $data->where('fac_id', $req->input('faculty'));
                }

                if ($req->has('programme') && !empty($req->input('programme'))) {
                    $data->where('programme_id', $req->input('programme'));
                }

                if ($req->has('semester') && !empty($req->input('semester'))) {
                    $data->where('ss.semester_id', $req->input('semester'));
                }

                if ($req->has('status') && !empty($req->input('status'))) {
                    $data->where('student_status', $req->input('status'));
                }
                $data = $data->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="user-checkbox form-check-input" value="' . $row->id . '">';
                });

                $table->addColumn('student_photo', function ($row) {
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
                                <small class="text-muted d-block fw-medium"> Enrolled Semesters: ' . $row->student_semcount . '</small>
                            </div>
                        </div>
                    ';
                });

                $table->addColumn('student_programme', function ($row) {
                    $mode = null;
                    if ($row->prog_mode == "FT") {
                        $mode = "Full-Time";
                    } elseif ($row->prog_mode == "PT") {
                        $mode = "Part-Time";
                    } else {
                        $mode = "N/A";
                    }
                    $programme = '
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <p class="mb-0 text-truncate">' . $row->prog_code . '</p>
                            <p class="mb-0  text-truncate">' . $mode . '</p>
                        </div>
                    </div>              
                    ';
                    return $programme;
                });

                $table->addColumn('student_status', function ($row) {
                    $status = '';

                    if ($row->student_status == 1) {
                        $status = '<span class="badge bg-light-success">' . 'Active' . '</span>';
                    } elseif ($row->student_status == 2) {
                        $status = '<span class="badge bg-light-secondary">' . 'Inactive' . '</span>';
                    } elseif ($row->student_status == 3) {
                        $status = '<span class="badge bg-light-info">' . 'Extend' . '</span>';
                    } elseif ($row->student_status == 4) {
                        $status = '<span class="badge bg-danger">' . 'Terminate' . '</span>';
                    } elseif ($row->student_status == 5) {
                        $status = '<span class="badge bg-light-secondary">' . 'Withdraw' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('supervision_role', function ($row) {
                    $role = match ($row->supervision_role) {
                        1 => "Main Supervisor",
                        2 => "Co-Supervisor",
                        default => "N/A",
                    };
                    return $role;
                });

                $table->addColumn('action', function ($row) {
                    $isReferenced = false;
                    $isReferenced = DB::table('supervisions')->where('student_id', $row->id)->exists() || DB::table('student_semesters')->where('student_id', $row->id)->exists();

                    $buttonEdit =
                        '
                            <a href="javascript: void(0)" class="avtar avtar-xs btn-light-primary" data-bs-toggle="modal"
                                data-bs-target="#updateModal-' . $row->id . '">
                                <i class="ti ti-edit f-20"></i>
                            </a>
                        ';

                    if ($isReferenced) {
                        $buttonRemove =
                            '
                                <a href="javascript: void(0)" class="avtar avtar-xs  btn-light-warning ' . ($row->student_status == 2 ? 'disabled-a' : '') . '" data-bs-toggle="modal"
                                    data-bs-target="#disableModal-' . $row->id . '">
                                    <i class="ti ti-user-off f-20"></i>
                                </a>
                            ';
                    }

                    return $buttonEdit . $buttonRemove;
                });

                $table->rawColumns(['checkbox', 'student_photo', 'student_programme', 'student_status', 'supervision_role', 'action']);

                return $table->make(true);
            }

            return view('staff.supervisor.student-list', [
                'title' => 'My Supervision - Student List',
                'studs' => Student::all(),
                'progs' => Programme::all(),
                'facs' => Faculty::all(),
                'sems' => Semester::all(),
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* My Supervision Export Student List - Function */
    public function exportMySupervisionStudentList(Request $req)
    {
        try {
            $selectedIds = $req->query('ids');
            return Excel::download(new MySupervisionStudentExport($selectedIds), 'e-PGS_MY_SUPERVISION_STUDENT_LIST_' . date('dMY') . '.xlsx');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error exporting students: ' . $e->getMessage());
        }
    }

    /* My Supervision Submission Management - Route */
    public function mySupervisionSubmissionManagement(Request $req)
    {
        try {

            $latestSemesterSub = DB::table('student_semesters')
                ->select('student_id', DB::raw('MAX(semester_id) as latest_semester_id'))
                ->groupBy('student_id');

            $data = DB::table('students as a')
                ->leftJoinSub($latestSemesterSub, 'latest', function ($join) {
                    $join->on('latest.student_id', '=', 'a.id');
                })
                ->leftJoin('student_semesters as ss', function ($join) {
                    $join->on('ss.student_id', '=', 'a.id')
                        ->on('ss.semester_id', '=', 'latest.latest_semester_id');
                })
                ->leftJoin('semesters as b', 'b.id', '=', 'ss.semester_id')
                ->join('programmes as c', 'c.id', '=', 'a.programme_id')
                ->join('submissions as d', 'd.student_id', '=', 'a.id')
                ->join('documents as e', 'e.id', '=', 'd.document_id')
                ->join('activities as f', 'f.id', '=', 'e.activity_id')
                ->join('supervisions as g', 'g.student_id', '=', 'a.id')
                ->select(
                    'a.*',
                    'b.sem_label',
                    'c.prog_code',
                    'c.prog_mode',
                    'd.id as submission_id',
                    'd.submission_status',
                    'd.submission_date',
                    'd.submission_duedate',
                    'd.submission_document',
                    'e.id as document_id',
                    'e.doc_name as document_name',
                    'f.id as activity_id',
                    'f.act_name as activity_name'
                )
                ->where('g.staff_id', auth()->user()->id)
                ->orderBy('f.act_name');

            if ($req->ajax()) {

                // Apply filters
                if ($req->has('faculty') && !empty($req->input('faculty'))) {
                    $data->where('fac_id', $req->input('faculty'));
                }
                if ($req->has('programme') && !empty($req->input('programme'))) {
                    $data->where('programme_id', $req->input('programme'));
                }
                if ($req->has('semester') && !empty($req->input('semester'))) {
                    $data->where('ss.semester_id', $req->input('semester'));
                }
                if ($req->has('activity') && !empty($req->input('activity'))) {
                    $data->where('activity_id', $req->input('activity'));
                }
                if ($req->has('document') && !empty($req->input('document'))) {
                    $data->where('document_id', $req->input('document'));
                }
                if ($req->has('status') && $req->input('status') !== null && $req->input('status') !== '') {
                    // If a status is selected (even status 5), show it
                    $data->where('submission_status', $req->input('status'));
                } else {
                    // Default: exclude status 5
                    $data->where('submission_status', '!=', 5);
                }

                $data = $data->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="user-checkbox form-check-input" value="' . $row->submission_id . '">';
                });

                $table->addColumn('student_photo', function ($row) {
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

                $table->addColumn('submission_duedate', function ($row) {
                    return Carbon::parse($row->submission_duedate)->format('d M Y g:i A') ?? '-';
                });

                $table->addColumn('submission_date', function ($row) {
                    return  $row->submission_date == null ? '-' : Carbon::parse($row->submission_date)->format('d M Y g:i A');
                });

                $table->addColumn('submission_status', function ($row) {
                    $status = '';

                    if ($row->submission_status == 1) {
                        $status = '<span class="badge bg-light-warning">' . 'No Attempt' . '</span>';
                    } elseif ($row->submission_status == 2) {
                        $status = '<span class="badge bg-danger">' . 'Locked' . '</span>';
                    } elseif ($row->submission_status == 3) {
                        $status = '<span class="badge bg-light-success">' . 'Submitted' . '</span>';
                    } elseif ($row->submission_status == 4) {
                        $status = '<span class="badge bg-light-danger">' . 'Overdue' . '</span>';
                    } elseif ($row->submission_status == 5) {
                        $status = '<span class="badge bg-secondary">' . 'Archive' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('action', function ($row) {
                    // STUDENT SUBMISSION DIRECTORY
                    $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name;
                    $htmlOne =
                        '
                            <div class="dropdown">
                                <a class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                    href="javascript: void(0)" data-bs-toggle="dropdown" 
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="material-icons-two-tone f-18">more_vert</i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                        ';
                    if ($row->submission_document != '-' && $row->submission_status != 5) {
                        $htmlTwo =
                            '          
                                    <a href="javascript: void(0)" class="dropdown-item" data-bs-toggle="modal"
                                        data-bs-target="#settingModal-' . $row->submission_id . '">
                                        Setting 
                                    </a>
                                    <a class="dropdown-item" href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->submission_document)]) . '" download="' . $row->submission_document . '">Download</a> 
                                    <a href="javascript: void(0)" class="dropdown-item" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal-' . $row->submission_id . '">
                                        Archive
                                    </a> 
                            ';
                    } elseif ($row->submission_status == 5 && $row->submission_document != '-') {
                        $htmlTwo = '
                                    <a class="dropdown-item" href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->submission_document)]) . '" download="' . $row->submission_document . '">Download</a>  
                                    <a href="javascript: void(0)" class="dropdown-item" data-bs-toggle="modal"
                                        data-bs-target="#unarchiveModal-' . $row->submission_id . '">
                                        Unarchive 
                                    </a>
                        ';
                    } elseif ($row->submission_status == 5 && $row->submission_document == '-') {
                        $htmlTwo = '
                                    <a href="javascript: void(0)" class="dropdown-item" data-bs-toggle="modal"
                                        data-bs-target="#unarchiveModal-' . $row->submission_id . '">
                                        Unarchive 
                                    </a>
                        ';
                    } else {
                        $htmlTwo =
                            '           
                                    <a href="javascript: void(0)" class="dropdown-item" data-bs-toggle="modal"
                                        data-bs-target="#settingModal-' . $row->submission_id . '">
                                        Setting 
                                    </a>
                                    <a href="javascript: void(0)" class="dropdown-item" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal-' . $row->submission_id . '">
                                        Archive
                                    </a>
                            ';
                    }

                    $htmlThree =
                        '
                                </div>
                            </div>
                        ';

                    return $htmlOne . $htmlTwo . $htmlThree;
                });


                $table->rawColumns(['checkbox', 'student_photo', 'submission_duedate', 'submission_date', 'submission_status', 'action']);

                return $table->make(true);
            }
            return view('staff.supervisor.submission-management', [
                'title' => 'My Supervision - Submission Management',
                'studs' => Student::all(),
                'current_sem' => Semester::where('sem_status', 1)->first()->sem_label ?? 'N/A',
                'progs' => Programme::all(),
                'facs' => Faculty::all(),
                'sems' => Semester::all(),
                'acts' => Activity::all(),
                'docs' => Document::all(),
                'subs' => $data->get()
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* My Supervision Submission Approval - Route */
    public function mySupervisionSubmissionApproval(Request $req)
    {
        try {

            /* LOAD DATATABLE DATA */
            $latestSemesterSub = DB::table('student_semesters')
                ->select('student_id', DB::raw('MAX(semester_id) as latest_semester_id'))
                ->groupBy('student_id');

            $data = DB::table('students as a')
                ->leftJoinSub($latestSemesterSub, 'latest', function ($join) {
                    $join->on('latest.student_id', '=', 'a.id');
                })
                ->leftJoin('student_semesters as ss', function ($join) {
                    $join->on('ss.student_id', '=', 'a.id')
                        ->on('ss.semester_id', '=', 'latest.latest_semester_id');
                })
                ->leftJoin('semesters as sem', 'sem.id', '=', 'ss.semester_id')
                ->join('programmes as b', 'b.id', '=', 'a.programme_id')
                ->join('student_activities as c', 'c.student_id', '=', 'a.id')
                ->join('activities as d', 'd.id', '=', 'c.activity_id')
                ->join('supervisions as e', 'e.student_id', '=', 'a.id')
                ->select(
                    'a.id as student_id',
                    'a.*',
                    'b.prog_code',
                    'b.prog_mode',
                    'd.id as activity_id',
                    'd.act_name as activity_name',
                    'c.id as student_activity_id',
                    'c.sa_status',
                    'c.sa_final_submission',
                    'c.sa_signature_data',
                    'c.activity_id',
                    'c.updated_at',
                    'c.semester_id',
                    'e.supervision_role',
                    'sem.sem_label'
                )
                ->where('e.staff_id', auth()->user()->id)
                ->orderBy('d.act_name');


            if ($req->ajax()) {

                if ($req->has('faculty') && !empty($req->input('faculty'))) {
                    $data->where('fac_id', $req->input('faculty'));
                }
                if ($req->has('programme') && !empty($req->input('programme'))) {
                    $data->where('programme_id', $req->input('programme'));
                }
                if ($req->has('semester') && !empty($req->input('semester'))) {
                    $data->where('c.semester_id', $req->input('semester'));
                }
                if ($req->has('activity') && !empty($req->input('activity'))) {
                    $data->where('activity_id', $req->input('activity'));
                }
                if ($req->has('document') && !empty($req->input('document'))) {
                    $data->where('document_id', $req->input('document'));
                }
                if ($req->has('status') && $req->input('status') !== null && $req->input('status') !== '') {
                    $data->where('sa_status', $req->input('status'));
                }
                if ($req->has('role') && $req->input('role') !== null && $req->input('role') !== '') {
                    $data->where('supervision_role', $req->input('role'));
                }

                $data = $data->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="user-checkbox form-check-input" value="' . $row->student_activity_id . '">';
                });

                $table->addColumn('student_photo', function ($row) {
                    $mode = match ($row->prog_mode) {
                        "FT" => "Full-Time",
                        "PT" => "Part-Time",
                        default => "N/A",
                    };

                    $svrole = match ($row->supervision_role) {
                        1 => "Main Supervisor",
                        2 => "Co-Supervisor",
                        default => "N/A",
                    };

                    $svname = DB::table('supervisions as a')
                        ->join('staff as b', 'b.id', '=', 'a.staff_id')
                        ->where('a.student_id', $row->student_id)
                        ->where('a.supervision_role', 1)
                        ->select('b.staff_name')
                        ->first();

                    $cosvname = DB::table('supervisions as a')
                        ->join('staff as b', 'b.id', '=', 'a.staff_id')
                        ->where('a.student_id', $row->student_id)
                        ->where('a.supervision_role', 2)
                        ->select('b.staff_name')
                        ->first();

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
                                <small class="text-muted d-block fw-bold mt-2 mb-2">Main Supervisor: <br><span class="fw-normal">' .  $svname->staff_name . '</span></small>
                                <small class="text-muted d-block fw-bold mb-2">Co-Supervisor: <br><span class="fw-normal">' .  $cosvname->staff_name . '</span></small>
                                <small class="text-muted d-block fw-medium">Your Role: <span class="fw-normal text-danger">' .  $svrole . '</span></small>
                            </div>
                        </div>
                    ';
                });

                $table->addColumn('sa_final_submission', function ($row) {

                    /* HANDLE EMPTY FINAL DOCUMENT */
                    if (empty($row->sa_final_submission)) {
                        return '-';
                    }

                    /* LOAD PROCEDURE DATA */
                    $procedure = Procedure::where('programme_id', $row->programme_id)
                        ->where('activity_id', $row->activity_id)
                        ->first();

                    /* LOAD SEMESTER DATA */
                    $currsemester = Semester::where('id', $row->semester_id)->first();

                    /* FORMAT SEMESTER LABEL */
                    $rawLabel = $currsemester->sem_label;
                    $semesterlabel = str_replace('/', '', $rawLabel);
                    $semesterlabel = trim($semesterlabel);

                    /* LOOK UP FOR DOCUMENT DIRECTORY */
                    if ($procedure->is_repeatable == 1) {
                        $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/' . $semesterlabel . '/Final Document';
                    } else {
                        $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Final Document';
                    }

                    /* HTML OUTPUT */
                    $final_doc =
                        '
                        <a href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->sa_final_submission)]) . '" 
                            target="_blank" class="link-dark d-flex align-items-center">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                            <span class="fw-semibold">View Document</span>
                        </a>
                    ';

                    /* RETURN HTML */
                    return $final_doc;
                });

                $table->addColumn('confirm_date', function ($row) {
                    /* HANDLE CONFIRMATION DATE */
                    return  $row->updated_at == null ? '-' : Carbon::parse($row->updated_at)->format('d M Y g:i A');
                });

                $table->addColumn('sa_status', function ($row) {

                    /* HANDLE STUDENT ACTIVITY STATUS */
                    $confirmation_status = match ($row->sa_status) {
                        1 => "<span class='badge bg-light-warning d-block mb-1'>Pending Approval: <br> Supervisor</span>",
                        2 => "<span class='badge bg-light-warning d-block mb-1'>Pending Approval: <br> (Comm/DD/Dean)</span>",
                        3 => "<span class='badge bg-success d-block mb-1'>Approved & Completed</span>",
                        4 => "<span class='badge bg-danger d-block mb-1'>Rejected: <br> Supervisor</span>",
                        5 => "<span class='badge bg-danger d-block mb-1'>Rejected: <br> (Comm/DD/Dean)</span>",
                        7 => "<span class='badge bg-light-warning d-block mb-1'>Pending: <br> Evaluation</span>",
                        8 => "<span class='badge bg-light-warning d-block mb-1'>Evaluation: <br> Minor/Major Correction</span>",
                        9 => "<span class='badge bg-light-danger d-block mb-1'>Evaluation: <br> Resubmit/Represent</span>",
                        12 => "<span class='badge bg-danger d-block mb-1'>Evaluation: <br> Failed</span>",
                        13 => "<span class='badge bg-light-success d-block mb-1'>Evaluation: <br> Passed & Continue Activity</span>",
                        default => "N/A",
                    };


                    /* LOAD SIGNATURE DATA */
                    $signatureData = !empty($row->sa_signature_data)
                        ? json_decode($row->sa_signature_data, true)
                        : [];

                    /* LOAD REQUIRED SIGNATURE ROLE DATA */
                    $formRoles = DB::table('activity_forms as a')
                        ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                        ->where('a.activity_id', $row->activity_id)
                        ->where('a.af_target', 1)
                        ->where('b.ff_category', 6)
                        ->pluck('b.ff_signature_role')
                        ->unique()
                        ->sort()
                        ->values()
                        ->toArray();

                    /* MAP SIGNATURE ROLE */
                    if ($row->sa_status == 1) {
                        $roleMap = [
                            2 => 'Main Supervisor',
                            3 => 'Co-Supervisor',
                        ];
                        $signatureKeys = [
                            2 => 'sv_signature',
                            3 => 'cosv_signature',
                        ];
                    } elseif ($row->sa_status == 2) {
                        $roleMap = [
                            4 => 'Committee',
                            5 => 'Deputy Dean',
                            6 => 'Dean'
                        ];
                        $signatureKeys = [
                            4 => 'comm_signature_date',
                            5 => 'deputy_dean_signature_date',
                            6 => 'dean_signature_date'
                        ];
                    } else {
                        $roleMap = [];
                        $signatureKeys = [];
                    }

                    /* MAPPING LOGIC */
                    $statusFragments = [];

                    foreach ($formRoles as $role) {
                        /* SKIP IF NO ROLE */
                        if (!isset($roleMap[$role]) || !isset($signatureKeys[$role])) {
                            continue;
                        }

                        $roleName = $roleMap[$role];
                        $signatureKey = $signatureKeys[$role];
                        $hasSigned = !empty($signatureData[$signatureKey]);

                        $statusFragments[] = $hasSigned
                            ? '<span class="badge bg-light-success d-block mb-1">Approved (' . $roleName . ')</span>'
                            : '<span class="badge bg-light-danger d-block mb-1">Required: ' . $roleName . '</span>';
                    }

                    /* RETURN STATUS */
                    return $confirmation_status . implode('', $statusFragments);
                });

                $table->addColumn('action', function ($row) {
                    /* LOAD FORM FIELD CONFIGURATION FOR THIS ACTIVITY */
                    $formFields = DB::table('activity_forms as a')
                        ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                        ->where('a.activity_id', $row->activity_id)
                        ->where('b.ff_category', 6)
                        ->select('b.ff_signature_role')
                        ->pluck('ff_signature_role')
                        ->toArray();

                    /* CHECK WHICH SIGNATURE ROLES ARE REQUIRED */
                    $requiresSv = in_array(2, $formFields);  // Supervisor required
                    $requiresCoSv = in_array(3, $formFields);  // Co-Supervisor required
                    $requiresBothSignatures = ($requiresSv && $requiresCoSv);

                    /* CHECK EXISTING SIGNATURES IN STUDENT ACTIVITY */
                    $hasSvSigned = false;
                    $hasCoSvSigned = false;
                    if (!empty($row->sa_signature_data)) {
                        $signatures = json_decode($row->sa_signature_data, true);
                        $hasSvSigned = isset($signatures['sv_signature']);
                        $hasCoSvSigned = isset($signatures['cosv_signature']);
                    }
                    $hasAllRequiredSignatures = ($requiresBothSignatures && $hasSvSigned && $hasCoSvSigned);

                    /* DETERMINE USER PERMISSIONS AND ACTIONS */
                    $isSupervisor = ($row->supervision_role == 1);
                    $isCoSupervisor = ($row->supervision_role == 2);

                    $svCannotAct = ($isSupervisor && !$requiresSv);
                    $cosvCannotAct = ($isCoSupervisor && !$requiresCoSv);
                    $userHasAlreadySigned = ($isSupervisor && $hasSvSigned) || ($isCoSupervisor && $hasCoSvSigned);

                    /* RETURN APPROPRIATE ACTION BUTTONS */
                    if ($hasAllRequiredSignatures) {
                        // Case 1: All required signatures exist - show only review button
                        return '
                            <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                            onclick="loadReviews(' . $row->student_activity_id . ')">
                                <i class="ti ti-eye me-2"></i>
                                <span class="me-2">Review</span>
                            </button>
                        ';
                    }

                    if ($row->sa_status == 1) {
                        // Case 2: Activity is active but not fully signed
                        if ($svCannotAct || $cosvCannotAct) {
                            return '<div class="fst-italic text-muted">No action to proceed</div>';
                        }

                        if ($userHasAlreadySigned) {
                            return '
                                <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                                    onclick="loadReviews(' . $row->student_activity_id . ')">
                                    <i class="ti ti-eye me-2"></i>
                                    <span class="me-2">Review</span>
                                </button>
                            ';
                        }

                        // Case 3: Show full action buttons for approver
                        return '
                            <button type="button" class="btn btn-light-success btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                                data-bs-toggle="modal" data-bs-target="#approveModal-' . $row->student_activity_id . '">
                                <i class="ti ti-circle-check me-2"></i>
                                <span class="me-2">Approve</span>
                            </button>

                            <button type="button" class="btn btn-light-danger btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                                data-bs-toggle="modal" data-bs-target="#rejectModal-' . $row->student_activity_id . '">
                                <i class="ti ti-circle-x me-2"></i>
                                <span class="me-2">Reject</span>
                            </button>

                            <button type="button" class="btn btn-light-warning btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                                data-bs-toggle="modal" data-bs-target="#revertModal-' . $row->student_activity_id . '">
                                <i class="ti ti-rotate me-2"></i>
                                <span class="me-2">Revert</span>
                            </button>
                        ';
                    }

                    // Default case: Show review button
                    return '
                        <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                        onclick="loadReviews(' . $row->student_activity_id . ')">
                            <i class="ti ti-eye me-2"></i>
                            <span class="me-2">Review</span>
                        </button>
                    ';
                });

                $table->rawColumns(['checkbox', 'student_photo', 'sa_final_submission', 'confirm_date', 'sa_status', 'action']);

                return $table->make(true);
            }

            return view('staff.supervisor.submission-approval', [
                'title' => 'My Supervision - Submission Approval',
                'studs' => Student::all(),
                'progs' => Programme::all(),
                'facs' => Faculty::all(),
                'sems' => Semester::all(),
                'acts' => Activity::all(),
                'subs' => $data->get(),
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* My Supervision Correction Approval - Route ## */
    public function mySupervisionCorrectionApproval(Request $req)
    {
        try {
            $latestSemesterSub = DB::table('student_semesters')
                ->select('student_id', DB::raw('MAX(semester_id) as latest_semester_id'))
                ->groupBy('student_id');

            $data = DB::table('students as a')
                ->leftJoinSub($latestSemesterSub, 'latest', function ($join) {
                    $join->on('latest.student_id', '=', 'a.id');
                })
                ->leftJoin('student_semesters as ss', function ($join) {
                    $join->on('ss.student_id', '=', 'a.id')
                        ->on('ss.semester_id', '=', 'latest.latest_semester_id');
                })
                ->leftJoin('semesters as sem', 'sem.id', '=', 'ss.semester_id')
                ->join('programmes as b', 'b.id', '=', 'a.programme_id')
                ->join('activity_corrections as c', 'c.student_id', '=', 'a.id')
                ->join('activities as d', 'd.id', '=', 'c.activity_id')
                ->join('supervisions as e', 'e.student_id', '=', 'a.id')
                ->select(
                    'a.id as student_id',
                    'a.*',
                    'b.prog_code',
                    'b.prog_mode',
                    'd.id as activity_id',
                    'd.act_name as activity_name',
                    'c.id as activity_correction_id',
                    'c.ac_status',
                    'c.ac_final_submission',
                    'c.ac_signature_data',
                    'c.activity_id',
                    'c.updated_at',
                    'c.semester_id',
                    'e.supervision_role',
                    'sem.sem_label'
                )
                ->where('e.staff_id', auth()->user()->id)
                ->orderBy('d.act_name');


            if ($req->ajax()) {

                // Apply filters
                if ($req->has('faculty') && !empty($req->input('faculty'))) {
                    $data->where('fac_id', $req->input('faculty'));
                }
                if ($req->has('programme') && !empty($req->input('programme'))) {
                    $data->where('programme_id', $req->input('programme'));
                }
                if ($req->has('semester') && !empty($req->input('semester'))) {
                    $data->where('ss.semester_id', $req->input('semester'));
                }
                if ($req->has('activity') && !empty($req->input('activity'))) {
                    $data->where('activity_id', $req->input('activity'));
                }
                if ($req->has('document') && !empty($req->input('document'))) {
                    $data->where('document_id', $req->input('document'));
                }
                if ($req->has('status') && $req->input('status') !== null && $req->input('status') !== '') {
                    $data->where('c.ac_status', $req->input('status'));
                }
                if ($req->has('role') && $req->input('role') !== null && $req->input('role') !== '') {
                    $data->where('supervision_role', $req->input('role'));
                }

                $data = $data->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="user-checkbox form-check-input" value="' . $row->activity_correction_id . '">';
                });

                $table->addColumn('student_photo', function ($row) {
                    $mode = match ($row->prog_mode) {
                        "FT" => "Full-Time",
                        "PT" => "Part-Time",
                        default => "N/A",
                    };

                    $svrole = match ($row->supervision_role) {
                        1 => "Main Supervisor",
                        2 => "Co-Supervisor",
                        default => "N/A",
                    };

                    $svname = DB::table('supervisions as a')
                        ->join('staff as b', 'b.id', '=', 'a.staff_id')
                        ->where('a.student_id', $row->student_id)
                        ->where('a.supervision_role', 1)
                        ->select('b.staff_name')
                        ->first();

                    $cosvname = DB::table('supervisions as a')
                        ->join('staff as b', 'b.id', '=', 'a.staff_id')
                        ->where('a.student_id', $row->student_id)
                        ->where('a.supervision_role', 2)
                        ->select('b.staff_name')
                        ->first();

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
                                <small class="text-muted d-block fw-bold mt-2 mb-2">Main Supervisor: <br><span class="fw-normal">' .  $svname->staff_name . '</span></small>
                                <small class="text-muted d-block fw-bold mb-2">Co-Supervisor: <br><span class="fw-normal">' .  $cosvname->staff_name . '</span></small>
                                <small class="text-muted d-block fw-medium">Your Role: <span class="fw-normal text-danger">' .  $svrole . '</span></small>
                            </div>
                        </div>
                    ';
                });

                $table->addColumn('ac_final_submission', function ($row) {

                    $currsemester = Semester::find($row->semester_id);
                    $rawLabel = $currsemester->sem_label;
                    $semesterlabel = str_replace('/', '', $rawLabel);
                    $semesterlabel = trim($semesterlabel);

                    $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Correction/' . $semesterlabel;

                    $final_submission =
                        '
                        <a href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->ac_final_submission)]) . '" 
                            target="_blank" class="link-dark d-flex align-items-center mb-2">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                            <span class="fw-semibold">View Document</span>
                        </a>
                    ';
                    return $final_submission;
                });

                $table->addColumn('confirm_date', function ($row) {
                    return  $row->updated_at == null ? '-' : Carbon::parse($row->updated_at)->format('d M Y g:i A');
                });

                $table->addColumn('ac_status', function ($row) {
                    // 1) Main status badge
                    $confirmationBadge = match ($row->ac_status) {
                        1 => "<span class='badge bg-light-warning d-block mb-1'>Pending:<br>Student Action</span>",
                        2 => "<span class='badge bg-light-warning d-block mb-1'>Pending Approval:<br>Supervisor</span>",
                        3 => "<span class='badge bg-light-warning d-block mb-1'>Pending Approval:<br>Examiners/Panels</span>",
                        4 => "<span class='badge bg-light-warning d-block mb-1'>Pending Approval:<br>(Comm/DD/Dean)</span>",
                        5 => "<span class='badge bg-light-success d-block mb-1'>Approved & Completed</span>",
                        6 => "<span class='badge bg-light-danger d-block mb-1'>Rejected:<br>Supervisor</span>",
                        7 => "<span class='badge bg-light-danger d-block mb-1'>Rejected:<br>Examiners/Panels</span>",
                        8 => "<span class='badge bg-light-danger d-block mb-1'>Rejected:<br>(Comm/DD/Dean)</span>",
                        default => "<span class='badge bg-secondary d-block mb-1'>N/A</span>",
                    };

                    // 2) Decode stored signatures
                    $sigs = ! empty($row->ac_signature_data)
                        ? json_decode($row->ac_signature_data, true)
                        : [];

                    // 3) Pull all signature‐fields once
                    $formFields = DB::table('activity_forms as a')
                        ->join('form_fields as f', 'a.id', '=', 'f.af_id')
                        ->where('a.activity_id', $row->activity_id)
                        ->where('a.af_target',   2)   // correction form
                        ->where('f.ff_category', 6)   // signature fields
                        ->select('f.ff_signature_role', 'f.ff_label', 'f.ff_signature_key')
                        ->orderBy('f.ff_order')
                        ->get();

                    // 4) Which roles belong to this level?
                    $levelRoles = match ($row->ac_status) {
                        2 => [2, 3],      // Supervisor + Co-Supervisor
                        3 => [8],         // Examiners/Panels
                        4 => [4, 5, 6],   // Committee, Deputy Dean, Dean
                        default => [],
                    };

                    // 5) Build sub-badges for *just* this level
                    $subBadges = '';
                    if ($levelRoles) {
                        $fieldsThisLevel = $formFields
                            ->whereIn('ff_signature_role', $levelRoles);

                        foreach ($fieldsThisLevel as $f) {
                            $label = e($f->ff_label);
                            $key   = $f->ff_signature_key;
                            $signed = ! empty($sigs[$key]);

                            if ($signed) {
                                $subBadges .=
                                    "<span class='badge bg-light-success d-block mb-1 text-wrap'>
                                        Approved: {$label}
                                    </span>";
                            } else {
                                $subBadges .=
                                    "<span class='badge bg-light-danger d-block mb-1 text-wrap'>
                                        Required: {$label}
                                    </span>";
                            }
                        }
                    }

                    return $confirmationBadge . $subBadges;
                });

                $table->addColumn('action', function ($row) {
                    // Status constants
                    $PENDING_SUPERVISOR = 2;

                    $activityId      = $row->activity_id;
                    $correctionId    = $row->activity_correction_id;
                    $myRole          = $row->supervision_role;   // 1 = SV, 2 = CoSV

                    // 1) Which signature roles does the form require?
                    $requiredRoles = DB::table('activity_forms as a')
                        ->join('form_fields as f', 'a.id', '=', 'f.af_id')
                        ->where('a.activity_id', $activityId)
                        ->where('a.af_target',   2)
                        ->where('f.ff_category', 6)
                        ->pluck('f.ff_signature_role')
                        ->unique()
                        ->toArray();

                    $svRequired   = in_array(2, $requiredRoles, true);
                    $cosvRequired = in_array(3, $requiredRoles, true);

                    // 2) What’s already signed?
                    $sigData    = json_decode($row->ac_signature_data ?? '[]', true);
                    $svSigned   = ! empty($sigData['sv_signature']);
                    $cosvSigned = ! empty($sigData['cosv_signature']);

                    // 3) Has this level fully completed?
                    //    – if both required, both must sign
                    //    – if only one required, that one alone suffices
                    $levelComplete = (
                        ($svRequired   && $cosvRequired && $svSigned && $cosvSigned)
                        || ($svRequired   && ! $cosvRequired && $svSigned)
                        || (! $svRequired && $cosvRequired   && $cosvSigned)
                    );

                    // 4) Is my signature required? And have I already signed?
                    $iAmRequired = ($myRole === 1 && $svRequired)
                        || ($myRole === 2 && $cosvRequired);

                    $iHaveSigned = ($myRole === 1 && $svSigned)
                        || ($myRole === 2 && $cosvSigned);

                    // 5) Only show buttons in PENDING_SUPERVISOR if:
                    //    • I am one of the required signers
                    //    • I haven't signed yet
                    //    • The level is not already completed
                    if (
                        $row->ac_status === $PENDING_SUPERVISOR
                        && $iAmRequired
                        && ! $iHaveSigned
                        && ! $levelComplete
                    ) {
                        return '
                            <button class="btn btn-light-success btn-sm mb-1 w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#approveModal-' . $correctionId . '">
                                <i class="ti ti-circle-check me-2"></i>Approve
                            </button>
                            <button class="btn btn-light-danger btn-sm mb-1 w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#rejectModal-' . $correctionId . '">
                                <i class="ti ti-circle-x me-2"></i>Reject
                            </button>
                            <button class="btn btn-light-warning btn-sm w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#revertModal-' . $correctionId . '">
                                <i class="ti ti-rotate me-2"></i>Revert
                            </button>
                        ';
                    }

                    // 6) Everything else:
                    return '<div class="fst-italic text-muted">No action to proceed</div>';
                });

                $table->rawColumns(['checkbox', 'student_photo', 'ac_final_submission', 'confirm_date', 'ac_status', 'action']);

                return $table->make(true);
            }

            return view('staff.supervisor.correction-approval', [
                'title' => 'My Supervision - Correction Approval',
                'studs' => Student::all(),
                'progs' => Programme::all(),
                'facs' => Faculty::all(),
                'sems' => Semester::all(),
                'acts' => Activity::all(),
                'subs' => $data->get(),
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* My Supervision Nomination ## - Route */
    public function mySupervisionNomination(Request $req, $name)
    {
        try {

            $id = Activity::all()
                ->first(function ($activity) use ($name) {
                    return strtolower(str_replace(' ', '-', $activity->act_name)) === $name;
                })?->id;

            $latestSemesterSub = DB::table('student_semesters')
                ->select('student_id', DB::raw('MAX(semester_id) as latest_semester_id'))
                ->groupBy('student_id');

            $data = DB::table('students as s')
                ->select([
                    's.id as student_id',
                    's.student_name',
                    's.student_matricno',
                    's.student_email',
                    's.student_directory',
                    's.student_photo',
                    'b.sem_label',
                    'c.prog_code',
                    'c.prog_mode',
                    'c.fac_id',
                    's.programme_id',
                    'a.id as activity_id',
                    'a.act_name as activity_name',
                    'n.id as nomination_id',
                    'n.nom_status',
                    'n.nom_date',
                    'n.nom_document',
                    'n.semester_id as nom_semester_id',
                ])
                ->leftJoinSub($latestSemesterSub, 'latest', function ($join) {
                    $join->on('s.id', '=', 'latest.student_id');
                })
                ->leftJoin('student_semesters as ss', function ($join) {
                    $join->on('ss.student_id', '=', 's.id')
                        ->on('ss.semester_id', '=', 'latest.latest_semester_id');
                })
                ->leftJoin('semesters as b', 'b.id', '=', 'ss.semester_id')
                ->join('nominations as n', 'n.student_id', '=', 's.id')
                ->join('activities as a', 'n.activity_id', '=', 'a.id')
                ->join('programmes as c', 'c.id', '=', 's.programme_id')
                ->join('supervisions as d', 'd.student_id', '=', 's.id')
                ->where('s.student_status', '=', 1)
                ->where('d.staff_id', '=', auth()->user()->id)
                ->where('d.supervision_role', '=', 1)
                ->where('a.id', '=', $id)
                ->orderBy('s.student_matricno');

            if ($req->ajax()) {

                if ($req->has('faculty') && !empty($req->input('faculty'))) {
                    $data->where('c.fac_id', $req->input('faculty'));
                }
                if ($req->has('programme') && !empty($req->input('programme'))) {
                    $data->where('s.programme_id', $req->input('programme'));
                }
                if ($req->has('semester') && !empty($req->input('semester'))) {
                    $data->where('ss.semester_id', $req->input('semester'));
                }
                if ($req->has('status') && !empty($req->input('status'))) {
                    $data->where('n.nom_status', $req->input('status'));
                }


                $data = $data->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('student_photo', function ($row) {
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

                $table->addColumn('nom_document', function ($row) {
                    // SEMESTER LABEL
                    $currsemester = Semester::find($row->nom_semester_id);
                    $rawLabel = $currsemester->sem_label;
                    $semesterlabel = str_replace('/', '', $rawLabel);
                    $semesterlabel = trim($semesterlabel);

                    // STUDENT SUBMISSION DIRECTORY
                    $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Nomination/' . $semesterlabel;

                    if (empty($row->nom_document)) {
                        return '-';
                    }

                    $final_doc =
                        '
                        <a href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->nom_document)]) . '" 
                            target="_blank" class="link-dark d-flex align-items-center">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                            <span class="fw-semibold">View Document</span>
                        </a>
                    ';
                    return $final_doc;
                });

                $table->addColumn('nom_date', function ($row) {
                    if (empty($row->nom_date)) {
                        return '-';
                    } else {
                        return Carbon::parse($row->nom_date)->format('d M Y h:i A');
                    }
                });

                $table->addColumn('nom_status', function ($row) {
                    $status = '';

                    if ($row->nom_status == 1) {
                        $status = '<span class="badge bg-light-warning">' . 'Pending' . '</span>';
                    } elseif ($row->nom_status == 2) {
                        $status = '<span class="badge bg-light-success">' . 'Nominated - SV' . '</span>';
                    } elseif ($row->nom_status == 3) {
                        $status = '<span class="badge bg-light-success">' . 'Reviewed - Committee' . '</span>';
                    } elseif ($row->nom_status == 4) {
                        $status = '<span class="badge bg-success">' . 'Approved' . '</span>';
                    } elseif ($row->nom_status == 5) {
                        $status = '<span class="badge bg-light-danger">' . 'Rejected' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('nom_semester', function ($row) {
                    $semesters = Semester::where('id', $row->nom_semester_id)->first();

                    if (!$semesters) {
                        return 'N/A';
                    }

                    return $semesters->sem_label;
                });

                $table->addColumn('action', function ($row) {
                    $button = '';

                    if ($row->nom_status == 1) {
                        $button = '
                            <a href="' . route('nomination-student', ['studentId' => Crypt::encrypt($row->student_id), 'actId' => Crypt::encrypt($row->activity_id), 'semesterId' => Crypt::encrypt($row->nom_semester_id), 'mode' => 1]) . '" class="avtar avtar-xs btn-light-primary">
                                <i class="ti ti-user-plus f-20"></i>
                            </a>
                        ';
                    } else {
                        $button = '<div class="fst-italic text-muted">No action required</div>';
                    }

                    return $button;
                });

                $table->rawColumns(['student_photo', 'nom_document', 'nom_date', 'nom_status', 'nom_semester', 'action']);

                return $table->make(true);
            }

            $act =  DB::table('activities as a')->join('procedures as b', 'a.id', '=', 'b.activity_id')
                ->select('a.id', 'a.act_name')
                ->where('a.id', '=', $id)
                ->first();

            if (!$act) {
                abort(404, 'Activity not found');
            }

            return view('staff.supervisor.nomination-management', [
                'title' => 'Supervisors - Nomination Management',
                'studs' => Student::all(),
                'progs' => Programme::all(),
                'facs' => Faculty::all(),
                'sems' => Semester::all(),
                'act' => $act,
                'data' => $data->get(),
            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
            return abort(500, $e->getMessage());
        }
    }

    /* My Supervision Evaluation Approval [HIGH ATTENTION - IN PROGRESS] - Route */
    public function mySupervisionEvaluationApproval(Request $req, $name)
    {
        try {

            /* GET ACTIVITY ID FROM ACTIVITY NAME */
            $id = Activity::all()
                ->first(function ($activity) use ($name) {
                    return strtolower(str_replace(' ', '-', $activity->act_name)) === $name;
                })?->id;

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $id)->first();

            if (!$activity) {
                return abort(404, 'Activity not found. Please try again.');
            }

            /* LOAD DATATABLE DATA */
            $latestSemesterSub = DB::table('student_semesters')
                ->select('student_id', DB::raw('MAX(semester_id) as latest_semester_id'))
                ->groupBy('student_id');

            $data = DB::table('students as s')
                ->select([
                    's.id as student_id',
                    's.student_name',
                    's.student_matricno',
                    's.student_email',
                    's.student_directory',
                    's.student_photo',
                    'b.sem_label',
                    'c.prog_code',
                    'c.prog_mode',
                    'c.fac_id',
                    's.programme_id',
                    'a.id as activity_id',
                    'a.act_name as activity_name',
                    'sa.id as sa_id',
                    'sa.sa_status',
                    'sa.sa_final_submission',
                    'sa.semester_id',
                ])
                ->leftJoinSub($latestSemesterSub, 'latest', function ($join) {
                    $join->on('s.id', '=', 'latest.student_id');
                })
                ->leftJoin('student_semesters as ss', function ($join) {
                    $join->on('ss.student_id', '=', 's.id')
                        ->on('ss.semester_id', '=', 'latest.latest_semester_id');
                })
                ->leftJoin('semesters as b', 'b.id', '=', 'ss.semester_id')
                ->join('student_activities as sa', 's.id', '=', 'sa.student_id')
                ->join('activities as a', 'sa.activity_id', '=', 'a.id')
                ->join('programmes as c', 'c.id', '=', 's.programme_id')
                ->join('supervisions as h', 'h.student_id', '=', 's.id')
                ->where('s.student_status', 1)
                ->where('sa.activity_id', $id)
                ->where('h.staff_id', auth()->user()->id)
                ->orderBy('s.student_matricno');

            if ($req->ajax()) {

                if ($req->has('faculty') && !empty($req->input('faculty'))) {
                    $data->where('c.fac_id', $req->input('faculty'));
                }
                if ($req->has('programme') && !empty($req->input('programme'))) {
                    $data->where('s.programme_id', $req->input('programme'));
                }
                if ($req->has('semester') && !empty($req->input('semester'))) {
                    $data->where('sa.semester_id', $req->input('semester'));
                }
                if ($req->has('status') && !empty($req->input('status'))) {
                    $data->where('sa.sa_status', $req->input('status'));
                } else {
                    $data->where('sa.sa_status', '!=', 3);
                }

                $data = $data->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('student_photo', function ($row) {
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

                $table->addColumn('sa_final_document', function ($row) {

                    /* HANDLE EMPTY FINAL DOCUMENT */
                    if (empty($row->sa_final_submission)) {
                        return '-';
                    }

                    /* LOAD PROCEDURE DATA */
                    $procedure = Procedure::where('programme_id', $row->programme_id)
                        ->where('activity_id', $row->activity_id)
                        ->first();

                    /* LOAD SEMESTER DATA */
                    $currsemester = Semester::where('id', $row->semester_id)->first();

                    /* FORMAT SEMESTER LABEL */
                    $rawLabel = $currsemester->sem_label;
                    $semesterlabel = str_replace('/', '', $rawLabel);
                    $semesterlabel = trim($semesterlabel);

                    /* LOOK UP FOR DOCUMENT DIRECTORY */
                    if ($procedure->is_repeatable == 1) {
                        $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/' . $semesterlabel . '/Final Document';
                    } else {
                        $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Final Document';
                    }

                    /* HTML OUTPUT */
                    $final_doc =
                        '
                        <a href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->sa_final_submission)]) . '" 
                            target="_blank" class="link-dark d-flex align-items-center">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                            <span class="fw-semibold">View Document</span>
                        </a>
                    ';

                    /* RETURN HTML */
                    return $final_doc;
                });

                $table->addColumn('approval_status', function ($row) {

                    /* LOAD FINAL STATUS */
                    if ($row->sa_status == 3) {
                        return '<span class="badge bg-success">Approved & Completed</span>';
                    }

                    if ($row->sa_status == 13) {
                        return '<span class="badge bg-success">Passed & Continue</span>';
                    }

                    if ($row->sa_status == 7) {

                        /* LOAD ALL EVALUATIONS FOR THIS STUDENT/ACTIVITY/SEMESTER */
                        $evaluations = Evaluation::where('activity_id', $row->activity_id)
                            ->where('student_id', $row->student_id)
                            ->where('semester_id', $row->semester_id)
                            ->get();

                        /* CHECK STATUS CONDITIONS */
                        $pendingSupervisorCount = $evaluations->where('evaluation_status', 9)->count();
                        $pendingHigherUpCount = $evaluations->where('evaluation_status', 10)->count();
                        $allCompleted = $evaluations->count() > 0 && $evaluations->where('evaluation_status', 8)->count() === $evaluations->count();

                        /* GET HIGHER UPS REQUIRED ROLES */
                        $rolesHURequired = DB::table('form_fields as ff')
                            ->join('activity_forms as af', 'ff.af_id', '=', 'af.id')
                            ->whereIn('ff.ff_signature_role', [4, 5, 6])
                            ->where('af.id', function ($q) use ($row) {
                                $q->select('af_id')
                                    ->from('evaluations')
                                    ->where('activity_id', $row->activity_id)
                                    ->where('student_id', $row->student_id)
                                    ->where('semester_id', $row->semester_id)
                                    ->limit(1);
                            })
                            ->pluck('ff.ff_signature_role')
                            ->unique()
                            ->toArray();

                        $haveHigherUp = count(array_intersect([4, 5, 6], $rolesHURequired)) > 0;

                        /* HANDLE STATUS OUTPUT */
                        if ($allCompleted && $haveHigherUp) {
                            /* ALL COMPLETED STATUS WITH PENDING FINAL STATUS */
                            return '<span class="badge bg-light-warning">Pending Final Status</span>';
                        } elseif ($allCompleted && !$haveHigherUp) {
                            /* ALL COMPLETED STATUS BUT ACTION REQUIRED*/
                            return '<span class="badge bg-light-danger">Action Required</span>';
                        } elseif ($pendingSupervisorCount > 0 || $pendingHigherUpCount > 0) {
                            /* PENDING APPROVAL STATUS */
                            $messages = [];
                            if ($pendingSupervisorCount > 0) {
                                $messages[] = '<div class="badge bg-light-warning p-2 mb-1 text-warning">
                             ' . $pendingSupervisorCount . ' Report(s) Pending Supervisor Approval
                           </div>';
                            }
                            if ($pendingHigherUpCount > 0) {
                                $messages[] = '<div class="badge bg-light-warning p-2 mb-1 text-warning">
                             ' . $pendingHigherUpCount . ' Report(s) Pending Administrative Approvals
                           </div>';
                            }
                            return implode('', $messages);
                        } else {
                            /* DEFAULT STATUS */
                            return '<span class="badge bg-secondary">Pending: Report Confirmation</span>';
                        }
                    }
                });

                $table->addColumn('semester', function ($row) {
                    /* LOAD SEMESTER DATA */
                    $semesters = Semester::where('id', $row->semester_id)->first();

                    if (empty($semesters)) {
                        return 'N/A';
                    }

                    /* RETURN SEMESTER LABEL */
                    return $semesters->sem_label;
                });

                $table->addColumn('action', function ($row) {

                    /* LOAD FINAL STATUS */
                    if ($row->sa_status == 3 || $row->sa_status == 13) {
                        return '<div class="fst-italic text-muted">No action required</div>';
                    }

                    /* LOAD STUDENT ACTIVITY DATA */
                    $submissionInProgress = StudentActivity::where('id', $row->sa_id)
                        ->whereIn('sa_status', [1, 2, 4, 5])
                        ->exists();

                    if ($submissionInProgress) {
                        return '<span class="badge bg-light-danger p-2">Student submission process <br> not yet completed.</span>';
                    }

                    /* GET STAFF ROLE EITHER AS MAIN SUPERVISOR OR CO-SUPERVISOR */
                    $userRole = DB::table('supervisions')
                        ->where('student_id', $row->student_id)
                        ->where('staff_id', auth()->user()->id)
                        ->value('supervision_role');

                    /* GET REQUIRED ROLES */
                    $rolesRequired = DB::table('form_fields as ff')
                        ->join('activity_forms as af', 'ff.af_id', '=', 'af.id')
                        ->where('af.activity_id', $row->activity_id)
                        ->where('af.af_target', 5)
                        ->whereIn('ff.ff_signature_role', [2,3])
                        ->pluck('ff.ff_signature_role')
                        ->unique()
                        ->toArray();

                    /* GET HIGHER UPS REQUIRED ROLES */
                    $rolesHURequired = DB::table('form_fields as ff')
                        ->join('activity_forms as af', 'ff.af_id', '=', 'af.id')
                        ->where('af.activity_id', $row->activity_id)
                        ->where('af.af_target', 5)
                        ->whereIn('ff.ff_signature_role', [4, 5, 6])
                        ->pluck('ff.ff_signature_role')
                        ->unique()
                        ->toArray();

                    $haveHigherUp = count(array_intersect([4, 5, 6], $rolesHURequired)) > 0;

                    /* MAP SUPERVISION ROLE TO FF SIGNATURE ROLE */
                    $mappedUserRole = ($userRole == 1) ? 2 : (($userRole == 2) ? 3 : null);

                    /* HANDLE USER HAS NO ROLE */
                    if (!$mappedUserRole || !in_array($mappedUserRole, $rolesRequired)) {
                        return '<div class="fst-italic text-muted">No action required</div>';
                    }

                    /* CHECK PENDING EVALUATIONS */
                    $pendingExists = Evaluation::where('activity_id', $row->activity_id)
                        ->where('student_id', $row->student_id)
                        ->where('semester_id', $row->semester_id)
                        ->where('evaluation_status', 9)
                        ->exists();

                    /* CHECK PENDING HU EVALUATIONS */
                    $pendingHUExists = Evaluation::where('activity_id', $row->activity_id)
                        ->where('student_id', $row->student_id)
                        ->where('semester_id', $row->semester_id)
                        ->where('evaluation_status', 10)
                        ->exists();

                    /* CHECK ALL COMPLETED EVALUATIONS */
                    $allCompleted = Evaluation::where('activity_id', $row->activity_id)
                        ->where('student_id', $row->student_id)
                        ->where('semester_id', $row->semester_id)
                        ->where('evaluation_status', 8)
                        ->count() > 0
                        &&
                        Evaluation::where('activity_id', $row->activity_id)
                        ->where('student_id', $row->student_id)
                        ->where('semester_id', $row->semester_id)
                        ->whereIn('evaluation_status', [1, 2, 3, 4, 5, 6, 7, 9, 10])
                        ->count() === 0;

                    /* BUTTON RENDER LOGIC */
                    if ($pendingExists || $pendingHUExists) {
                        $btnClass = '';
                    } elseif (!$pendingExists && !$allCompleted) {
                        $btnClass = 'disabled-a';
                    } elseif ($allCompleted && !$haveHigherUp) {

                        /* LOAD EVALUATIONS DATA */
                        $evaluations = Evaluation::where('activity_id', $row->activity_id)
                            ->where('student_id', $row->student_id)
                            ->where('semester_id', $row->semester_id)
                            ->where('evaluation_status', 8)
                            ->get();

                        /* LOAD EVALUATION CONTROLLER */
                        $ec = new EvaluationController();

                        /* GET PROGRESS AND MOCK COUNT */
                        $progressCount = 0;
                        $mockCount = 0;

                        foreach ($evaluations as $evaluation) {
                            $statusType = $ec->extractEvaluationStatus($evaluation->evaluation_meta_data);
                            if ($statusType === 1) {
                                $progressCount++;
                            } elseif ($statusType === 2) {
                                $mockCount++;
                            }
                        }

                        /* CHECK IF CONTINUE OR END */
                        $continueChecked = $progressCount > 0;
                        $endChecked = !$continueChecked && $mockCount > 0;

                        return '
                            <form method="POST" action="' . route('finalize-evaluation-post', Crypt::encrypt($row->sa_id)) . '" class="d-flex flex-column gap-1" style="min-width:180px;">
                                ' . csrf_field() . '
                                <div class="fw-semibold mb-1">Select Status</div>
                                <div class="form-check form-check-sm">
                                    <input class="form-check-input" type="radio" name="evaluation_type" id="eval_progress_' . $row->sa_id . '" value="1" ' . ($continueChecked ? 'checked' : '') . ' required>
                                    <label class="form-check-label small" for="eval_progress_' . $row->sa_id . '">Continue Next Semester</label>
                                </div>
                                <div class="form-check form-check-sm">
                                    <input class="form-check-input" type="radio" name="evaluation_type" id="eval_mock_' . $row->sa_id . '" value="2" ' . ($endChecked ? 'checked' : '') . '>
                                    <label class="form-check-label small" for="eval_mock_' . $row->sa_id . '">End This Semester</label>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary mt-1">Confirmed</button>
                            </form>
                        ';
                    } else {
                        return '<div class="fst-italic text-muted">No action required</div>';
                    }

                    /* RETURN HTML BUTTON */
                    return '
                        <a href="' . route('my-supervision-student-evaluation-approval', [
                        'activityID' => encrypt($row->activity_id),
                        'studentID' => encrypt($row->student_id)
                    ]) . '" class="avtar avtar-xs btn-light-primary ' . $btnClass . '">
                            <i class="ti ti-eye f-20"></i>
                        </a>
                    ';
                });

                $table->rawColumns(['student_photo', 'sa_final_document', 'approval_status', 'semester', 'action']);

                return $table->make(true);
            }

            return view('staff.supervisor.evaluation-approval', [
                'title' => 'Supervisor - Evaluation Approval',
                'studs' => Student::all(),
                'progs' => Programme::all(),
                'facs' => Faculty::all(),
                'sems' => Semester::all(),
                'act' => $activity,
                'data' => $data->get(),
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* My Supervision Each Student Evaluation Approval [HIGH ATTENTION - IN PROGRESS] - Route */
    public function mySupervisionStudentEvaluationApproval(Request $req, $activityID, $studentID)
    {
        try {

            /* DECRYPT IDs */
            $activityID = decrypt($activityID);
            $studentID = decrypt($studentID);

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $activityID)->first();

            if (!$activity) {
                return abort(404, 'Activity not found. Please try again.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $studentID)->first();

            if (!$student) {
                return abort(404, 'Student not found. Please try again.');
            }

            /* LOAD DATATABLE DATA */
            $latestSemesterSub = DB::table('student_semesters')
                ->select('student_id', DB::raw('MAX(semester_id) as latest_semester_id'))
                ->groupBy('student_id');

            $data = DB::table('students as s')
                ->select([
                    's.id as student_id',
                    's.student_name',
                    's.student_matricno',
                    's.student_email',
                    's.student_directory',
                    's.student_photo',
                    'b.sem_label',
                    'c.prog_code',
                    'c.prog_mode',
                    'c.fac_id',
                    's.programme_id',
                    'a.id as activity_id',
                    'a.act_name as activity_name',
                    'f.id as evaluation_id',
                    'f.evaluation_status',
                    'f.evaluation_date',
                    'f.evaluation_document',
                    'f.evaluation_isFinal',
                    'f.evaluation_signature_data',
                    'f.semester_id',
                    'f.staff_id',
                    'h.supervision_role',
                ])
                ->leftJoinSub($latestSemesterSub, 'latest', function ($join) {
                    $join->on('s.id', '=', 'latest.student_id');
                })
                ->leftJoin('student_semesters as ss', function ($join) {
                    $join->on('ss.student_id', '=', 's.id')
                        ->on('ss.semester_id', '=', 'latest.latest_semester_id');
                })
                ->leftJoin('semesters as b', 'b.id', '=', 'ss.semester_id')
                ->join('evaluations as f', 's.id', '=', 'f.student_id')
                ->join('activities as a', 'f.activity_id', '=', 'a.id')
                ->join('programmes as c', 'c.id', '=', 's.programme_id')
                ->join('supervisions as h', 'h.student_id', '=', 's.id') // fixed join
                ->where('s.student_status', 1)
                ->where('f.activity_id', $activityID)
                ->where('h.staff_id', auth()->user()->id)
                ->where('s.id', $studentID)
                ->orderBy('s.student_matricno');


            if ($req->ajax()) {

                if ($req->has('semester') && !empty($req->input('semester'))) {
                    $data->where('f.semester_id', $req->input('semester'));
                }

                if ($req->has('status') && !empty($req->input('status'))) {
                    $data->where('f.evaluation_status', $req->input('status'));
                }

                $data = $data->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('student_photo', function ($row) {
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

                $table->addColumn('evaluator', function ($row) {
                    /* LOAD STAFF DATA */
                    $staff = Staff::where('id', $row->staff_id)->first();

                    /* BUILD EVALUATOR INFO */
                    $evaluatorInfo = '
                        <div style="max-width: 250px;" class="mb-2">
                            <span class="mb-0 fw-medium">' . e($staff->staff_name) . '</span>
                            <small class="text-muted d-block fw-medium">' . e($staff->staff_email) . '</small>
                            <small class="text-muted d-block fw-medium">' . e($staff->staff_no) . '</small>
                        </div>
                    ';

                    /* HANDLE EMPTY FINAL DOCUMENT */
                    if (!empty($row->evaluation_document)) {
                        /* LOAD PROCEDURE DATA */
                        $procedure = Procedure::where('programme_id', $row->programme_id)
                            ->where('activity_id', $row->activity_id)
                            ->first();

                        /* LOAD SEMESTER DATA */
                        $currsemester = Semester::where('id', $row->semester_id)->first();

                        /* FORMAT SEMESTER LABEL */
                        $rawLabel = $currsemester->sem_label;
                        $semesterlabel = str_replace('/', '', $rawLabel);
                        $semesterlabel = trim($semesterlabel);

                        /* LOOK UP FOR DOCUMENT DIRECTORY */
                        if ($procedure->is_repeatable == 1) {
                            $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/' . $semesterlabel . '/Evaluation';
                        } else {
                            $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Evaluation/' . $semesterlabel;
                        }

                        /* DOCUMENT LINK */
                        $docLink = '
                            <a href="' . route('view-material-get', [
                            'filename' => Crypt::encrypt($submission_dir . '/' . $row->evaluation_document)
                        ]) . '" target="_blank" class="link-dark d-flex align-items-center mt-1">
                                <i class="fas fa-file-pdf me-2 text-danger"></i>
                                <span class="fw-semibold">View Document</span>
                            </a>
                        ';
                    } else {
                        $docLink = '<small class="text-muted d-block fst-italic mt-1">No document uploaded</small>';
                    }

                    /* RETURN COMBINED HTML */
                    return '
                        <div class="d-flex align-items-start flex-column">
                            ' . $evaluatorInfo . '
                            ' . $docLink . '
                        </div>
                    ';
                });

                $table->addColumn('evaluator-info', function ($row) {
                    /* LOAD STAFF DATA */
                    $staff = Staff::where('id', $row->staff_id)->first();

                    /* RETURN HTML */
                    return '
                        <div class="d-flex align-items-center" >
                            <div style="max-width: 200px;">
                                <span class="mb-0 fw-medium">' . $staff->staff_name . '</span>
                                <small class="text-muted d-block fw-medium">' . $staff->staff_email . '</small>
                                <small class="text-muted d-block fw-medium">' . $staff->staff_no . '</small>
                            </div>
                        </div>
                    ';
                });

                $table->addColumn('evaluation_document', function ($row) {

                    /* HANDLE EMPTY FINAL DOCUMENT */
                    if (empty($row->evaluation_document)) {
                        return '-';
                    }

                    /* LOAD PROCEDURE DATA */
                    $procedure = Procedure::where('programme_id', $row->programme_id)
                        ->where('activity_id', $row->activity_id)
                        ->first();

                    /* LOAD SEMESTER DATA */
                    $currsemester = Semester::where('id', $row->semester_id)->first();

                    /* FORMAT SEMESTER LABEL */
                    $rawLabel = $currsemester->sem_label;
                    $semesterlabel = str_replace('/', '', $rawLabel);
                    $semesterlabel = trim($semesterlabel);

                    /* LOOK UP FOR DOCUMENT DIRECTORY */
                    if ($procedure->is_repeatable == 1) {
                        $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/' . $semesterlabel . '/Evaluation';
                    } else {
                        $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Evaluation/' . $semesterlabel;
                    }

                    /* HTML OUTPUT */
                    $final_doc =
                        '
                        <a href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->evaluation_document)]) . '" 
                            target="_blank" class="link-dark d-flex align-items-center">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                            <span class="fw-semibold">View Document</span>
                        </a>
                    ';

                    /* RETURN HTML */
                    return $final_doc;
                });

                $table->addColumn('evaluation_date', function ($row) {

                    /* HANDLE EMPTY DATE */
                    if (empty($row->evaluation_date)) {
                        return '-';
                    }

                    /* RETURN FORMATTED DATE */
                    return Carbon::parse($row->evaluation_date)->format('d M Y h:i A');
                });

                $table->addColumn('evaluation_status', function ($row) {
                    $statusLines = [];

                    /* EVALUATION STATUS 8 : CONFIRMED */
                    if ($row->evaluation_status == 8) {
                        $statusLines[] = '<span class="badge bg-success">Confirmed</span>';
                    }

                    /* EVALUATION STATUS 9 : PENDING SUPERVISOR's APPROVAL */ elseif ($row->evaluation_status == 9) {
                        $statusLines[] = '<span class="badge bg-light-warning">Pending : Supervisor Approval</span>';
                    }

                    /* EVALUATION STATUS 10 : PENDING COMMITTEE/DD/DEAN APPROVAL */ elseif ($row->evaluation_status == 10) {
                        $statusLines[] = '<span class="badge bg-light-warning">Pending : Committee/DD/Dean Approval</span>';
                    }

                    /* EVALUATION STATUS 11 : APPROVAL REJECTED BY SUPERVISOR */ elseif ($row->evaluation_status == 11) {
                        $statusLines[] = '<span class="badge bg-light-danger">Rejected : Supervisor</span>';
                    }

                    /* EVALUATION STATUS 12 : APPROVAL REJECTED BY COMMITTEE/DD/DEAN */ elseif ($row->evaluation_status == 12) {
                        $statusLines[] = '<span class="badge bg-light-danger">Rejected : Committee/DD/Dean</span>';
                    }

                    /* EVALUATION STATUS : NOT YET COMPLETED */ else {
                        $statusLines[] = '<span class="badge bg-light-secondary">Evaluation : Not Completed</span>';
                    }

                    /* LOAD REQUIRED ROLES */
                    $requiredRoles = DB::table('form_fields as ff')
                        ->join('activity_forms as af', 'ff.af_id', '=', 'af.id')
                        ->where('af.activity_id', $row->activity_id)
                        ->where('af.af_target', 5)
                        ->whereIn('ff.ff_signature_role', [2, 3])
                        ->select('ff.ff_signature_role', 'ff.ff_signature_key')
                        ->get();

                    /* ROLES MAPPING */
                    $roleLabels = [
                        2 => 'Supervisor',
                        3 => 'Co-Supervisor'
                    ];

                    /* LOAD EVALUATION DATA */
                    $evaluation = Evaluation::where('id', $row->evaluation_id)->first();

                    /* GET EVALUATION SIGNATURE DATA */
                    $signatureData = $evaluation && $evaluation->evaluation_signature_data
                        ? json_decode($evaluation->evaluation_signature_data, true)
                        : [];

                    /* LOOP THROUGH REQUIRED ROLES */
                    if ($row->evaluation_status == 9 || $row->evaluation_status == 10) {
                        foreach ($requiredRoles as $role) {
                            $roleName = $roleLabels[$role->ff_signature_role] ?? 'Unknown Role';
                            $sigKey = $role->ff_signature_key;

                            if (!empty($signatureData[$sigKey])) {
                                $statusLines[] = '<span class="badge bg-light-success">Approved : ' . $roleName . '</span>';
                            } else {
                                $statusLines[] = '<span class="badge bg-light-danger">Required : ' . $roleName . '</span>';
                            }
                        }
                    }

                    /* HANDLE EMPTY STATUS */
                    if (empty($statusLines)) {
                        $statusLines[] = '<span class="badge bg-light-danger">N/A</span>';
                    }

                    /* RETURN STATUS */
                    return implode('<br>', $statusLines);
                });

                $table->addColumn('evaluation_semester', function ($row) {

                    /* LOAD SEMESTER DATA */
                    $semesters = Semester::where('id', $row->semester_id)->first();

                    /* HANDLE EMPTY SEMESTER DATA */
                    if (empty($semesters)) {
                        return 'N/A';
                    }

                    /* RETURN SEMESTER LABEL */
                    return $semesters->sem_label;
                });

                $table->addColumn('action', function ($row) {
                    /* SET GLOBAL VARIABLE */
                    $PENDING_SUPERVISOR = 9;

                    /* GET EACH ATTRIBUTE IDs */
                    $activityId = $row->activity_id;
                    $evaluationId = $row->evaluation_id;

                    /* GET ROLE FROM DB */
                    $myRole = $row->supervision_role; // 1 = SV, 2 = CoSV

                    /* LOAD REQUIRED ROLES */
                    $requiredRoles = DB::table('activity_forms as a')
                        ->join('form_fields as f', 'a.id', '=', 'f.af_id')
                        ->where('a.activity_id', $activityId)
                        ->where('a.af_target', 5) // your evaluation target
                        ->where('f.ff_category', 6)
                        ->pluck('f.ff_signature_role')
                        ->unique()
                        ->toArray();

                    $svRequired   = in_array(2, $requiredRoles, true);
                    $cosvRequired = in_array(3, $requiredRoles, true);

                    /* LOAD SIGNED DATA */
                    $sigData    = json_decode($row->evaluation_signature_data ?? '[]', true);
                    $svSigned   = !empty($sigData['sv_signature']);
                    $cosvSigned = !empty($sigData['cosv_signature']);

                    /* CHECK IF THIS LEVEL IS COMPLETE */
                    $levelComplete = (
                        ($svRequired && $cosvRequired && $svSigned && $cosvSigned)
                        || ($svRequired && !$cosvRequired && $svSigned)
                        || (!$svRequired && $cosvRequired && $cosvSigned)
                    );

                    /* AM I REQUIRED TO SIGN? */
                    $iAmRequired = ($myRole === 1 && $svRequired)
                        || ($myRole === 2 && $cosvRequired);

                    /* HAVE I ALREADY SIGNED? */
                    $iHaveSigned = ($myRole === 1 && $svSigned)
                        || ($myRole === 2 && $cosvSigned);

                    /* SHOW ACTION BUTTONS ONLY IF */
                    if (
                        $row->evaluation_status === $PENDING_SUPERVISOR &&
                        $iAmRequired &&
                        !$iHaveSigned &&
                        !$levelComplete
                    ) {
                        return '
                            <div class="d-flex flex-column gap-2 text-start p-1">
                                <button type="button" class="btn btn-light-success btn-sm w-100"
                                    data-bs-toggle="modal" data-bs-target="#approveModal-' . $evaluationId . '">
                                    <i class="ti ti-circle-check me-2"></i> Approve
                                </button>
                                <button type="button" class="btn btn-light-danger btn-sm w-100"
                                    data-bs-toggle="modal" data-bs-target="#rejectModal-' . $evaluationId . '">
                                    <i class="ti ti-circle-x me-2"></i> Reject
                                </button>
                            </div>
                        ';
                    }

                    /* FALLBACK */
                    return '<div class="fst-italic text-muted">No action required</div>';
                });

                $table->rawColumns(['student_photo', 'evaluator', 'evaluation_document', 'evaluation_date', 'evaluation_status', 'evaluation_semester', 'action']);

                return $table->make(true);
            }

            return view('staff.supervisor.evaluation-student-approval', [
                'title' => $student->student_name . ' - Evaluation Approval',
                'student' => $student,
                'sems' => Semester::all(),
                'activity' => $activity,
                'data' => $data->get(),
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }
}
