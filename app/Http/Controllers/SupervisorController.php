<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\Activity;
use App\Models\Document;
use App\Models\Semester;
use App\Models\FormField;
use App\Models\Programme;
use App\Models\ActivityForm;
use Illuminate\Http\Request;
use App\Models\StudentActivity;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\MySupervisionStudentExport;

class SupervisorController extends Controller
{
    /* Supervisor - Student List */
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

    public function exportMySupervisionStudentList(Request $req)
    {
        try {
            $selectedIds = $req->query('ids');
            return Excel::download(new MySupervisionStudentExport($selectedIds), 'e-PGS_MY_SUPERVISION_STUDENT_LIST_' . date('dMY') . '.xlsx');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error exporting students: ' . $e->getMessage());
        }
    }

    /* Supervisor - Submission Management */
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
                    $data->where('semester_id', $req->input('semester'));
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

    /* Supervisor - Submission Approval */
    public function mySupervisionSubmissionApproval(Request $req)
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
                    $data->where('semester_id', $req->input('semester'));
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
                    // STUDENT SUBMISSION DIRECTORY
                    $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Final Document';

                    $final_submission =
                        '
                        <a href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->sa_final_submission)]) . '" 
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

                $table->addColumn('sa_status', function ($row) {

                    $confirmation_status = match ($row->sa_status) {
                        1 => "<span class='badge bg-light-warning d-block mb-1'>Pending Approval: <br> Supervisor</span>",
                        2 => "<span class='badge bg-light-warning d-block mb-1'>Pending Approval: <br> (Comm/DD/Dean)</span>",
                        3 => "<span class='badge bg-success d-block mb-1'>Approved & Completed</span>",
                        4 => "<span class='badge bg-danger d-block mb-1'>Rejected: <br> Supervisor</span>",
                        5 => "<span class='badge bg-danger d-block mb-1'>Rejected: <br> (Comm/DD/Dean)</span>",
                        default => "N/A",
                    };


                    $signatureData = !empty($row->sa_signature_data)
                        ? json_decode($row->sa_signature_data, true)
                        : [];

                    // Get required signature roles for the activity
                    $formRoles = DB::table('activity_forms as a')
                        ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                        ->where('a.activity_id', $row->activity_id)
                        ->where('b.ff_category', 6)
                        ->pluck('b.ff_signature_role')
                        ->unique()
                        ->sort()
                        ->values()
                        ->toArray();

                    // All roles involved in approvals (SV, Co-SV, Comm, DD, Dean)

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

                        // Signature key for each role
                        $signatureKeys = [
                            4 => 'comm_signature_date',
                            5 => 'deputy_dean_signature_date',
                            6 => 'dean_signature_date'
                        ];
                    } else {
                        $roleMap = [];
                        $signatureKeys = [];
                    }


                    $statusFragments = [];

                    foreach ($formRoles as $role) {
                        // Skip if not mapped properly
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

                    return $confirmation_status . implode('', $statusFragments);
                });

                $table->addColumn('action', function ($row) {
                    $activityId = $row->activity_id;

                    // Query only once and cache the result
                    $formFields = DB::table('activity_forms as a')
                        ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                        ->where('a.activity_id', $activityId)
                        ->where('b.ff_category', 6)
                        ->select('b.ff_signature_role')
                        ->pluck('ff_signature_role')
                        ->toArray();

                    $hasSvfield = in_array(2, $formFields);
                    $hasCoSvfield = in_array(3, $formFields);
                    $hasCoSv = ($hasSvfield && $hasCoSvfield);

                    // Check signatures
                    $hasSvSignature = false;
                    $hasCoSvSignature = false;

                    if (!empty($row->sa_signature_data)) {
                        $signatureData = json_decode($row->sa_signature_data, true);
                        $hasSvSignature = isset($signatureData['sv_signature']);
                        $hasCoSvSignature = isset($signatureData['cosv_signature']);
                    }

                    $signatureExists = ($hasCoSv && $hasSvSignature && $hasCoSvSignature);

                    $svNoBtn = ($row->supervision_role == 1 && $hasSvSignature);
                    $cosvNoBtn = ($row->supervision_role == 2 && $hasCoSvSignature);

                    $svNoPermission = ($row->supervision_role == 1 && !$hasSvfield);
                    $cosvNoPermission = ($row->supervision_role == 2 && !$hasCoSvfield);

                    $studentActivityId = $row->student_activity_id;

                    if ($signatureExists) {
                        return '
                            <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                               onclick="loadReviews(' . $row->student_activity_id . ')">
                                <i class="ti ti-eye me-2"></i>
                                <span class="me-2">Review</span>
                            </button>
                        ';
                    }

                    if (!$signatureExists && $row->sa_status == 1) {

                        if ($svNoPermission || $cosvNoPermission) {
                            return '<div class="fst-italic text-muted">No action to proceed</div>';
                        }

                        if ($svNoBtn || $cosvNoBtn) {
                            return ' 
                                <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                                    onclick="loadReviews(' . $row->student_activity_id . ')">
                                    <i class="ti ti-eye me-2"></i>
                                    <span class="me-2">Review</span>
                                </button>
                            ';
                        }

                        return '
                            <button type="button" class="btn btn-light-success btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                                data-bs-toggle="modal" data-bs-target="#approveModal-' . $studentActivityId . '">
                                <i class="ti ti-circle-check me-2"></i>
                                <span class="me-2">Approve</span>
                            </button>

                            <button type="button" class="btn btn-light-danger btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                                data-bs-toggle="modal" data-bs-target="#rejectModal-' . $studentActivityId . '">
                                <i class="ti ti-circle-x me-2"></i>
                                <span class="me-2">Reject</span>
                            </button>

                            <button type="button" class="btn btn-light-warning btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                                data-bs-toggle="modal" data-bs-target="#revertModal-' . $studentActivityId . '">
                                <i class="ti ti-rotate me-2"></i>
                                <span class="me-2">Revert</span>
                            </button>
                        ';
                    }

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

    /* Supervisor - Nomination */
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
                    $data->where('semester_id', $req->input('semester'));
                }
                if ($req->has('status') && !empty($req->input('status'))) {
                    $data->where('nom_status', $req->input('status'));
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
                    // STUDENT SUBMISSION DIRECTORY
                    $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Nomination';

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

                $table->addColumn('action', function ($row) {
                    $button = '';

                    if ($row->nom_status == 1) {
                        $button = '
                            <a href="' . route('nomination-student', ['studentId' => Crypt::encrypt($row->student_id), 'actId' => Crypt::encrypt($row->activity_id), 'mode' => 1]) . '" class="avtar avtar-xs btn-light-primary">
                                <i class="ti ti-user-plus f-20"></i>
                            </a>
                        ';
                    } else {
                        $button = '<div class="fst-italic text-muted">No action required</div>';
                    }

                    return $button;
                });

                $table->rawColumns(['student_photo', 'nom_document', 'nom_date', 'nom_status', 'action']);

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
            return abort(500, $e->getMessage());
        }
    }

}
