<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\Activity;
use App\Models\Semester;
use App\Models\Evaluator;
use App\Models\FormField;
use App\Models\Procedure;
use App\Models\Programme;
use App\Models\Evaluation;
use App\Models\Nomination;
use App\Models\Supervision;
use App\Models\ActivityForm;
use Illuminate\Http\Request;
use App\Models\StudentActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ActivityCorrection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;


class EvaluationController extends Controller
{
    /* Examiner / Panel - Evaluation */
    public function examinerPanelEvaluation(Request $req, $name)
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
                    'f.id as evaluation_id',
                    'f.evaluation_status',
                    'f.evaluation_date',
                    'f.evaluation_document',
                    'f.evaluation_isFinal',
                    'f.evaluation_isFinal',
                    'f.semester_id',
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
                ->join('evaluators as e', 'n.id', '=', 'e.nom_id')
                ->join('evaluations as f', 's.id', '=', 'f.student_id')
                ->join('activities as a', 'n.activity_id', '=', 'a.id')
                ->join('programmes as c', 'c.id', '=', 's.programme_id')
                ->where('s.student_status', '=', 1)
                ->where('e.eva_status', '=', 3)
                ->where('e.eva_role', '=', 1)
                ->where('e.staff_id', '=', auth()->user()->id)
                ->where('f.staff_id', '=', auth()->user()->id)
                ->where('n.activity_id', '=', $id)
                ->where('f.activity_id', '=', $id)
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
                    if ($req->input('status') == 10) {
                        $data->where('f.evaluation_isFinal', 1);
                    } else {
                        $data->where('f.evaluation_status', $req->input('status'));
                    }
                } else {
                    $data->where('f.evaluation_isFinal', 0);
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

                $table->addColumn('evaluation_document', function ($row) {

                    // SEMESTER LABEL
                    $currsemester = Semester::find($row->semester_id);
                    $rawLabel = $currsemester->sem_label;
                    $semesterlabel = str_replace('/', '', $rawLabel);
                    $semesterlabel = trim($semesterlabel);

                    // STUDENT SUBMISSION DIRECTORY
                    $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Evaluation/' . $semesterlabel;

                    if (empty($row->evaluation_document)) {
                        return '-';
                    }

                    $final_doc =
                        '
                        <a href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->evaluation_document)]) . '" 
                            target="_blank" class="link-dark d-flex align-items-center">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                            <span class="fw-semibold">View Document</span>
                        </a>
                    ';
                    return $final_doc;
                });

                $table->addColumn('evaluation_date', function ($row) {
                    if (empty($row->evaluation_date)) {
                        return '-';
                    } else {
                        return Carbon::parse($row->evaluation_date)->format('d M Y h:i A');
                    }
                });

                $table->addColumn('evaluation_status', function ($row) {
                    $status = '';

                    if ($row->evaluation_status == 1) {
                        $status = '<span class="badge bg-light-warning">' . 'Pending' . '</span>';
                    } elseif ($row->evaluation_status == 2) {
                        $status = '<span class="badge bg-light-success">' . 'Passed' . '</span>';
                    } elseif ($row->evaluation_status == 3) {
                        $status = '<span class="badge bg-light-success">' . 'Passed (Minor Changes)' . '</span>';
                    } elseif ($row->evaluation_status == 4) {
                        $status = '<span class="badge bg-light-success">' . 'Passed (Major Changes)' . '</span>';
                    } elseif ($row->evaluation_status == 5) {
                        $status = '<span class="badge bg-light-warning">' . 'Represent/Resubmit' . '</span>';
                    } elseif ($row->evaluation_status == 6) {
                        $status = '<span class="badge bg-danger">' . 'Failed' . '</span>';
                    } elseif ($row->evaluation_status == 7) {
                        $status = '<span class="badge bg-light-danger">' . 'Submitted (Draft)' . '</span>';
                    } elseif ($row->evaluation_status == 8) {
                        $status = '<span class="badge bg-success">' . 'Confirmed' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('evaluation_semester', function ($row) {
                    $semesters = Semester::where('id', $row->semester_id)->first();

                    if (empty($semesters)) {
                        return 'N/A';
                    }

                    return $semesters->sem_label;
                });

                $table->addColumn('action', function ($row) {
                    $currsemester = Semester::where('sem_status', 1)->first();

                    $submissionInProgress = StudentActivity::where('activity_id', $row->activity_id)
                        ->where('student_id', $row->student_id)
                        ->whereBetween('sa_status', [1, 5])
                        ->exists();

                    if ($submissionInProgress) {
                        return '<span class="badge bg-light-danger p-2">Student submission process <br> not yet completed.</span>';
                    }

                    if ($row->evaluation_isFinal != 1 && ($row->semester_id == $currsemester->id)) {
                        return '
                            <a href="' . route('evaluation-student', ['evaluationID' => Crypt::encrypt($row->evaluation_id), 'mode' => 5]) . '" 
                                class="avtar avtar-xs btn-light-primary">
                                <i class="ti ti-edit f-20"></i>
                            </a>
                        ';
                    }

                    return '<div class="fst-italic text-muted">No action required</div>';
                });

                $table->rawColumns(['student_photo', 'evaluation_document', 'evaluation_date', 'evaluation_status', 'evaluation_semester', 'action']);

                return $table->make(true);
            }

            $act =  DB::table('activities as a')->join('procedures as b', 'a.id', '=', 'b.activity_id')
                ->select('a.id', 'a.act_name')
                ->where('a.id', '=', $id)
                ->first();

            if (!$act) {
                abort(404, 'Activity not found');
            }

            return view('staff.evaluation.examiner-panel-evaluation-management', [
                'title' => 'Examiner / Panel - Evaluation Management',
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

    /* Chairman - Evaluation */
    public function chairmanEvaluation(Request $req, $name)
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
                    'f.id as evaluation_id',
                    'f.evaluation_status',
                    'f.evaluation_date',
                    'f.evaluation_document',
                    'f.evaluation_isFinal',
                    'f.semester_id',
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
                ->join('evaluators as e', 'n.id', '=', 'e.nom_id')
                ->join('evaluations as f', 's.id', '=', 'f.student_id')
                ->join('activities as a', 'n.activity_id', '=', 'a.id')
                ->join('programmes as c', 'c.id', '=', 's.programme_id')
                ->where('s.student_status', '=', 1)
                ->where('e.eva_status', '=', 3)
                ->where('e.eva_role', '=', 2)
                ->where('e.staff_id', '=', auth()->user()->id)
                ->where('f.staff_id', '=', auth()->user()->id)
                ->where('n.activity_id', '=', $id)
                ->where('f.activity_id', '=', $id)
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
                    if ($req->input('status') == 10) {
                        $data->where('f.evaluation_isFinal', 1);
                    } else {
                        $data->where('f.evaluation_status', $req->input('status'));
                    }
                } else {
                    $data->where('f.evaluation_isFinal', 0);
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

                $table->addColumn('evaluation_document', function ($row) {

                    // SEMESTER LABEL
                    $currsemester = Semester::find($row->semester_id);
                    $rawLabel = $currsemester->sem_label;
                    $semesterlabel = str_replace('/', '', $rawLabel);
                    $semesterlabel = trim($semesterlabel);

                    // STUDENT SUBMISSION DIRECTORY
                    $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Evaluation/' . $semesterlabel;

                    if (empty($row->evaluation_document)) {
                        return '-';
                    }

                    $final_doc =
                        '
                        <a href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->evaluation_document)]) . '" 
                            target="_blank" class="link-dark d-flex align-items-center">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                            <span class="fw-semibold">View Document</span>
                        </a>
                    ';
                    return $final_doc;
                });

                $table->addColumn('evaluation_date', function ($row) {
                    if (empty($row->evaluation_date)) {
                        return '-';
                    } else {
                        return Carbon::parse($row->evaluation_date)->format('d M Y h:i A');
                    }
                });

                $table->addColumn('evaluation_status', function ($row) {
                    $status = '';

                    if ($row->evaluation_status == 1) {
                        $status = '<span class="badge bg-light-warning">' . 'Pending' . '</span>';
                    } elseif ($row->evaluation_status == 2) {
                        $status = '<span class="badge bg-light-success">' . 'Passed' . '</span>';
                    } elseif ($row->evaluation_status == 3) {
                        $status = '<span class="badge bg-light-success">' . 'Passed (Minor Changes)' . '</span>';
                    } elseif ($row->evaluation_status == 4) {
                        $status = '<span class="badge bg-light-success">' . 'Passed (Major Changes)' . '</span>';
                    } elseif ($row->evaluation_status == 5) {
                        $status = '<span class="badge bg-light-warning">' . 'Represent/Resubmit' . '</span>';
                    } elseif ($row->evaluation_status == 6) {
                        $status = '<span class="badge bg-danger">' . 'Failed' . '</span>';
                    } elseif ($row->evaluation_status == 7) {
                        $status = '<span class="badge bg-light-danger">' . 'Submitted (Draft)' . '</span>';
                    } elseif ($row->evaluation_status == 8) {
                        $status = '<span class="badge bg-success">' . 'Confirmed' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('evaluation_semester', function ($row) {
                    $semesters = Semester::where('id', $row->semester_id)->first();

                    if (empty($semesters)) {
                        return 'N/A';
                    }

                    return $semesters->sem_label;
                });

                $table->addColumn('action', function ($row) {
                    $currsemester = Semester::where('sem_status', 1)->first();

                    $submissionInProgress = StudentActivity::where('activity_id', $row->activity_id)
                        ->where('student_id', $row->student_id)
                        ->whereBetween('sa_status', [1, 5])
                        ->exists();

                    if ($submissionInProgress) {
                        return '<span class="badge bg-light-danger p-2">Student submission process <br> not yet completed.</span>';
                    }

                    if ($row->evaluation_isFinal != 1 && ($row->semester_id == $currsemester->id)) {
                        return '
                            <a href="' . route('evaluation-student', [
                            'studentId' => Crypt::encrypt($row->student_id),
                            'actId' => Crypt::encrypt($row->activity_id),
                            'semesterId' => Crypt::encrypt($row->semester_id),
                            'mode' => 6
                        ]) . '" class="avtar avtar-xs btn-light-primary">
                                <i class="ti ti-edit f-20"></i>
                            </a>
                        ';
                    }

                    return '<div class="fst-italic text-muted">No action required</div>';
                });

                $table->rawColumns(['student_photo', 'evaluation_document', 'evaluation_date', 'evaluation_status', 'evaluation_semester', 'action']);

                return $table->make(true);
            }

            $act =  DB::table('activities as a')->join('procedures as b', 'a.id', '=', 'b.activity_id')
                ->select('a.id', 'a.act_name')
                ->where('a.id', '=', $id)
                ->first();

            if (!$act) {
                abort(404, 'Activity not found');
            }

            return view('staff.evaluation.chairman-evaluation-management', [
                'title' => 'Chairman - Evaluation Management',
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

    /* Committee - Evaluation */
    public function finalEvaluationReport(Request $req, $name)
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
                    'e.eva_status',
                    'e.eva_role',
                    'f.id as evaluation_id',
                    'f.evaluation_status',
                    'f.evaluation_date',
                    'f.evaluation_document',
                    'f.evaluation_isFinal',
                    'f.semester_id',
                    'd.staff_name',
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
                ->join('evaluators as e', 'n.id', '=', 'e.nom_id')
                ->join('evaluations as f', function ($join) {
                    $join->on('s.id', '=', 'f.student_id')
                        ->on('f.staff_id', '=', 'e.staff_id');
                })
                ->join('activities as a', 'n.activity_id', '=', 'a.id')
                ->join('programmes as c', 'c.id', '=', 's.programme_id')
                ->join('staff as d', 'd.id', '=', 'f.staff_id')
                ->where('s.student_status', 1)
                ->where('f.evaluation_isFinal', 1)
                ->where('n.activity_id', $id)
                ->where('f.activity_id', $id)
                ->where('e.eva_status', 3)
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

                $table->addColumn('evaluation_document', function ($row) {

                    // SEMESTER LABEL
                    $currsemester = Semester::find($row->semester_id);
                    $rawLabel = $currsemester->sem_label;
                    $semesterlabel = str_replace('/', '', $rawLabel);
                    $semesterlabel = trim($semesterlabel);

                    // STUDENT SUBMISSION DIRECTORY
                    $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Evaluation/' . $semesterlabel;

                    if (empty($row->evaluation_document)) {
                        return '-';
                    }

                    $final_doc =
                        '
                        <a href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->evaluation_document)]) . '" 
                            target="_blank" class="link-dark d-flex align-items-center">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                            <span class="fw-semibold">View Document</span>
                        </a>
                    ';
                    return $final_doc;
                });

                $table->addColumn('evaluation_date', function ($row) {
                    if (empty($row->evaluation_date)) {
                        return '-';
                    } else {
                        return Carbon::parse($row->evaluation_date)->format('d M Y h:i A');
                    }
                });

                $table->addColumn('confirmed_by', function ($row) {

                    $evarole = match ($row->eva_role) {
                        1 => "Examiner/Panel",
                        2 => "Chairman",
                        default => "N/A",
                    };

                    return '
                        <div class="d-flex align-items-center" >
                            <div style="max-width: 200px;">
                                <span class="mb-0 fw-medium">' . $row->staff_name . '</span>
                                <small class="text-muted d-block fw-medium">' . $evarole . '</small>
                            </div>
                        </div>
                    ';
                });

                $table->addColumn('evaluation_status', function ($row) {
                    $status = '';

                    if ($row->evaluation_status == 1) {
                        $status = '<span class="badge bg-light-warning">' . 'Pending' . '</span>';
                    } elseif ($row->evaluation_status == 2) {
                        $status = '<span class="badge bg-light-success">' . 'Passed' . '</span>';
                    } elseif ($row->evaluation_status == 3) {
                        $status = '<span class="badge bg-light-success">' . 'Passed (Minor Changes)' . '</span>';
                    } elseif ($row->evaluation_status == 4) {
                        $status = '<span class="badge bg-light-success">' . 'Passed (Major Changes)' . '</span>';
                    } elseif ($row->evaluation_status == 5) {
                        $status = '<span class="badge bg-light-warning">' . 'Represent/Resubmit' . '</span>';
                    } elseif ($row->evaluation_status == 6) {
                        $status = '<span class="badge bg-danger">' . 'Failed' . '</span>';
                    } elseif ($row->evaluation_status == 7) {
                        $status = '<span class="badge bg-light-danger">' . 'Submitted (Draft)' . '</span>';
                    } elseif ($row->evaluation_status == 8) {
                        $status = '<span class="badge bg-success">' . 'Confirmed' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('evaluation_semester', function ($row) {
                    $semesters = Semester::where('id', $row->semester_id)->first();

                    if (empty($semesters)) {
                        return 'N/A';
                    }

                    return $semesters->sem_label;
                });

                $table->rawColumns(['student_photo', 'evaluation_document', 'evaluation_date', 'confirmed_by', 'evaluation_status', 'evaluation_semester']);

                return $table->make(true);
            }

            $act =  DB::table('activities as a')->join('procedures as b', 'a.id', '=', 'b.activity_id')
                ->select('a.id', 'a.act_name')
                ->where('a.id', '=', $id)
                ->first();

            if (!$act) {
                abort(404, 'Activity not found');
            }

            return view('staff.evaluation.final-report-evaluation', [
                'title' => 'Final Evaluation Report',
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

    /* Evaluation Student - Route */
    public function evaluationStudent($evaluationID, $mode)
    {
        try {
            /* DECRYPT PROCESS */
            $evaluationID = decrypt($evaluationID);

            /* LOAD EVALUATION DATA */
            $evaluation = Evaluation::where('id', $evaluationID)->first();

            if (!$evaluation) {
                abort(404, 'Evaluation not found. Please try again.');
            }

            /* LOAD STUDENT DATA */
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
                ->select('a.*', 'a.id as student_id', 'b.sem_label', 'c.prog_code', 'c.prog_mode', 'ss.semester_id')
                ->where('a.id', $evaluation->student_id)
                ->first();

            /* LOAD ACTIVITY DATA */
            $activity =  DB::table('activities as a')
                ->join('procedures as b', 'a.id', '=', 'b.activity_id')
                ->select('a.id', 'a.act_name')
                ->where('a.id', '=', $evaluation->activity_id)
                ->first();

            if (!$activity) {
                abort(404, 'Evaluation not found. Please try again.');
            }

            /* LOAD ACTIVITY FORM */
            if ($mode == 5) {
                /* EXAMINER/PANEL */
                $actForm = ActivityForm::where('activity_id', $evaluation->activity_id)
                    ->where('af_target', 5)
                    ->first();
            } else if ($mode == 6) {
                /* CHAIRMAN */
                $actForm = ActivityForm::where('activity_id', $evaluation->activity_id)
                    ->where('af_target', 4)
                    ->first();
            }

            if (!$actForm) {
                return back()->with('error', 'Form for this activity were not found. Please add the form first at the Form Setting page.');
            }

            /* LOAD EXAMINER SIGNATURE  */
            $examinerSign = FormField::where('af_id', $actForm->id)
                ->where('ff_signature_role', 8)
                ->select('ff_signature_key')
                ->get();

            /* LINK ASSIGNMENT */
            if ($mode == 5) {
                $page = 'Examiner / Panel';
                $link =  route('examiner-panel-evaluation', strtolower(str_replace(' ', '-', $activity->act_name)));
            } else if ($mode == 6) {
                $page = 'Chairman';
                $link =  route('chairman-evaluation', strtolower(str_replace(' ', '-', $activity->act_name)));
            }

            return view('staff.evaluation.evaluation-student', [
                'title' => $data->student_name . 'Evaluation',
                'act' => $activity,
                'actform' => $actForm,
                'examinerSign' => $examinerSign,
                'data' => $data,
                'mode' => $mode,
                'page' => $page,
                'link' => $link,
                'evaluationID' => $evaluationID
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* View Evaluation Form - Function */
    public function viewEvaluationForm(Request $req)
    {
        try {

            /* GET IDs */
            $mode = $req->input('mode');
            $evaid = $req->input('evaid');
            $afid = $req->input('afid');

            /* LOAD EVALUATION DATA */
            $evaluation = Evaluation::where('id', $evaid)->first();

            if (!$evaluation) {
                return back()->with('error', 'Evaluation not found. Cannot view form. Please try again.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $evaluation->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Student not found. Cannot view form. Please try again.');
            }

            /* LOAD ACTIVITY FORM DATA */
            $form = ActivityForm::where('id', $afid)->first();

            if (!$form) {
                return back()->with('error', 'Form not found. Cannot view form. Please try again.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $evaluation->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Activity not found. Cannot view form. Please try again.');
            }

            /* LOAD FACULTY DATA */
            $faculty = Faculty::where('fac_status', 3)->first();

            if (!$faculty) {
                return back()->with('error', 'Faculty not found. Cannot view form. Please try again.');
            }

            /* LOAD FORM FIELD DATA */
            $formfields = FormField::where('af_id', $form->id)
                ->orderBy('ff_order')
                ->get();

            if (!$formfields) {
                return back()->with('error', 'Form field not found. Cannot view form. Please try again.');
            }

            /* GET FORM SIGNATURE */
            $signatures = $formfields->where('ff_category', 6);

            /* GET EVALUATION SIGNATURE */
            $signatureData = $evaluation ? json_decode($evaluation->evaluation_signature_data) : null;

            /* MAPPING PROCESS - SUBSTITUTE DATA */
            $userData = [];
            $fhc = new FormHandlerController();
            $userData = $fhc->joinMap($formfields, $student, $activity);

            /* FETCH [EVALUATION] - EXTRA META DATA */
            if ($evaluation && $evaluation->evaluation_meta_data) {
                $extraData = json_decode($evaluation->evaluation_meta_data, true);
                if (is_array($extraData)) {
                    foreach ($extraData as $key => $value) {
                        $normalizedKey = str_replace(' ', '_', strtolower($key));
                        $userData[$normalizedKey] = $value ?? '-';
                    }
                }
            }

            $html = view('staff.sop.template.input-form', [
                'title' => $activity->act_name . " Document",
                'act' => $activity,
                'form_title' => $form->af_title,
                'formfields' => $formfields,
                'userData' => $userData,
                'faculty' => $faculty,
                'signatures' => $signatures,
                'signatureData' => $signatureData,
                'mode' => $mode
            ])->render();

            return response()->json(['html' => $html]);
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error fetching evaluation form: ' . $e->getMessage());
        }
    }

    /* Evaluation Report Submission - Function [Evaluator] | Email : Yes With Works */
    public function submitEvaluation(Request $req, $evaluationID, $mode)
    {
        try {
            /* SET VARIABLE FROM REQUEST */
            $option = $req->input('opt');

            /* DECRYPT PROCESS */
            $evaluationID = decrypt($evaluationID);

            /* LOAD EVALUATION DATA */
            $evaluation = Evaluation::where('id', $evaluationID)->first();

            if (!$evaluation) {
                return back()->with('error', 'Evaluation not found. Operation could not be processed. Please try again.');
            }
            /* LOAD USER DATA */
            $staffId = auth()->user()->id;

            if (!$staffId) {
                return back()->with('error', 'Unauthorized access : Staff record is not found.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $evaluation->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Student record not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $evaluation->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Activity record not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD STUDENT ACTIVITY DATA */
            $studentActivity = StudentActivity::where('student_id', $evaluation->student_id)
                ->where('activity_id', $evaluation->activity_id)
                ->where('semester_id', $evaluation->semester_id)
                ->first();

            if (!$studentActivity) {
                return back()->with('error', 'Student confirmation record not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD PROCEDURE DATA */
            $procedure = Procedure::where([
                'activity_id' => $evaluation->activity_id,
                'programme_id' => $student->programme_id
            ])->first();

            if (!$procedure) {
                return back()->with('error', 'Procedure not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* 
             * LOAD ACTIVITY BASED ON MODE
             * 5 : EXAMINER / PANEL FORM
             * 6 : CHAIRMAN FORM
             */
            if ($mode == 5) {
                $form = ActivityForm::where('activity_id', $evaluation->activity_id)
                    ->where('af_target', 5)
                    ->first();
            } elseif ($mode == 6) {
                $form = ActivityForm::where('activity_id', $evaluation->activity_id)
                    ->where('af_target', 4)
                    ->first();
            }

            if (!$form) {
                return back()->with('error', 'Evaluation form not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD NOMINATION DATA */
            $nomination = Nomination::where('student_id', $evaluation->student_id)
                ->where('activity_id', $evaluation->activity_id)
                ->where('semester_id', $evaluation->semester_id)
                ->first();

            if (!$nomination) {
                return back()->with('error', 'Nomination record not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD SEMESTER DATA */
            $currsemester = Semester::where('id', $evaluation->semester_id)->first();

            if (!$currsemester) {
                return back()->with('error', 'Semester record not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* ESTABLISHED FORM META DATA */
            $formData = $req->except(['_token', 'signatureData', 'opt', 'semester_id']);
            $scoreData = $this->extractScoreData($formData);
            $evaluationMeta = $formData;
            $evaluationMeta['Score'] = $scoreData;

            /* STORE EVALUATION SIGNATURE */
            if ($req->has('signatureData')) {
                $this->storeEvaluationSignature($student, $form, $req->signatureData, $evaluation, $nomination, $mode);
            }

            /* GENERATE FILENAME BASED ON ROLES */
            $fileName = $this->generateEvaluationFilename($student, $nomination, $evaluation, $mode);

            /* UPDATE EVALUATION RECORDS */
            if ($option == 1) {
                /* EVALUATOR SAVE AS DRAFT */
                $evaluation->evaluation_status = 7;
            } elseif ($option == 2) {
                if ($mode == 5) {
                    /* EVALUATOR SUBMIT AS CONFIRMED - [EXAMINER/PANEL] */

                    /* GET FORM ROLES */
                    $formRoles = DB::table('activity_forms as a')
                        ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                        ->where('a.id', $form->id)
                        ->where('b.ff_category', 6)
                        ->pluck('b.ff_signature_role')
                        ->unique()
                        ->toArray();

                    /* SEARCHING FOR HIGHER ROLES */
                    $higherRoles = [2, 3, 4, 5, 6];
                    $requiredRoles = array_values(array_intersect($formRoles, $higherRoles));

                    if (empty($requiredRoles)) {
                        /* HIGHER ROLES NOT FOUND */
                        $evaluation->evaluation_status = 8;
                        $evaluation->evaluation_isFinal = 1;
                    }

                    if (array_intersect($requiredRoles, [2, 3])) {
                        $higherRoleKeys = DB::table('form_fields')
                            ->where('af_id', $form->id)
                            ->where('ff_category', 6)
                            ->whereIn('ff_signature_role', [2, 3])
                            ->pluck('ff_signature_key')
                            ->toArray();

                        $sigData = $evaluation->evaluation_signature_data
                            ? json_decode($evaluation->evaluation_signature_data, true)
                            : [];

                        $allHigherAlreadySigned = collect($higherRoleKeys)->every(function ($key) use ($sigData) {
                            return isset($sigData[$key]) && !empty($sigData[$key]);
                        });

                        if ($allHigherAlreadySigned) {
                            $evaluation->evaluation_status = 8;
                            $evaluation->evaluation_isFinal = 1;
                        } else {
                            $evaluation->evaluation_status = 9;
                        }
                    } elseif (array_intersect($requiredRoles, [4, 5, 6])) {
                        $higherRoleKeys = DB::table('form_fields')
                            ->where('af_id', $form->id)
                            ->where('ff_category', 6)
                            ->whereIn('ff_signature_role', [4, 5, 6])
                            ->pluck('ff_signature_key')
                            ->toArray();

                        $sigData = $evaluation->evaluation_signature_data
                            ? json_decode($evaluation->evaluation_signature_data, true)
                            : [];

                        $allHigherAlreadySigned = collect($higherRoleKeys)->every(function ($key) use ($sigData) {
                            return isset($sigData[$key]) && !empty($sigData[$key]);
                        });

                        if ($allHigherAlreadySigned) {
                            $evaluation->evaluation_status = 8;
                            $evaluation->evaluation_isFinal = 1;
                        } else {
                            $evaluation->evaluation_status = 9;
                        }
                    }
                } elseif ($mode == 6) {
                    /* EVALUATOR SUBMIT AS CONFIRMED - [CHAIRMAN] */

                    /* GET DURATION DATA */
                    $duration = $this->findDurationInRequest($req);

                    /* GET DECISION DATA */
                    $decisionStatus = $this->mapDecisionToStatus($req->all());

                    /* UPDATE EVALUATION STATUS */
                    $evaluation->evaluation_status = $decisionStatus;

                    if ($decisionStatus == 2) {
                        /* STATUS : PASS LOGIC */

                        /* UPDATE STUDENT ACTIVITY STATUS TO CONFIRMED AND COMPLETE */
                        $studentActivity->sa_status = 3;
                    } elseif ($decisionStatus == 3 || $decisionStatus == 4) {
                        /* STATUS : PASS WITH MINOR/MAJOR CORRECTION LOGIC */

                        /* UPDATE STUDENT ACTIVITY STATUS TO PENDING EVALUATION */
                        $studentActivity->sa_status = 8;

                        /* SET START AND DUE DATE FOR CORRECTION PROCESS */
                        $startDate = Carbon::now()->startOfDay();
                        $dueDate = $duration ? Carbon::now()->add($duration['unit'] . 's', $duration['value'])->startOfDay() : null;

                        /* CREATE CORRECTION FOR CURRENT SEMESTER */
                        ActivityCorrection::updateOrCreate(
                            [
                                'student_id' => $student->id,
                                'activity_id' => $activity->id,
                            ],
                            [
                                'ac_status' => 1,
                                'ac_startdate' => $startDate,
                                'ac_duedate' => $dueDate,
                                'ac_signature_data' => json_encode([]),
                                'semester_id' => $currsemester->id,
                            ]
                        );

                        /* RESTORE ANY ACRHIVE SUBMISSION */
                        $this->restoreSubmission($student, $activity->id, $dueDate);
                    } elseif ($decisionStatus == 5) {
                        /* STATUS : REPRESENT/RESUBMIT LOGIC */

                        /* UPDATE STUDENT ACTIVITY STATUS TO PENDING NEW SUBMISSION */
                        $studentActivity->sa_status = 9;
                    } elseif ($decisionStatus == 5) {
                        /* STATUS : FAILED LOGIC [NOT YET CONFIRMED PROCESS] */

                        /* UPDATE STUDENT ACTIVITY STATUS TO FAILED */
                        $studentActivity->sa_status = 5;
                    } else {
                        $studentActivity->sa_status = 5;
                    }

                    /* UPDATE EVALUATION */
                    $studentActivity->save();

                    /* SET EVALUATION AS FINAL */
                    $evaluation->evaluation_isFinal = 1;
                }
            }

            /* SET AND UPDATE EVALUATION DATA */
            $evaluation->evaluation_date = now();
            $evaluation->evaluation_meta_data = json_encode($evaluationMeta);
            $evaluation->evaluation_document = $fileName;
            $evaluation->save();

            /* GENERATE EVALUATION FORM DOCUMENT */
            $progcode = strtoupper($student->programmes->prog_code);
            $activityName = str_replace(['/', '\\'], '-', $activity->act_name);

            /* SET RELATIVE DIRECTORY */
            $rawLabel = $currsemester->sem_label;
            $semesterlabel = str_replace('/', '', $rawLabel);
            $semesterlabel = trim($semesterlabel);

            if ($procedure->is_repeatable == 1) {
                $relativeDir = "{$student->student_directory}/{$progcode}/{$activityName}/{$semesterlabel}/Evaluation";
            } else {
                $relativeDir = "{$student->student_directory}/{$progcode}/{$activityName}/Evaluation/{$semesterlabel}";
            }

            /* LOAD FINAL DIRECTORY */
            $fullPath = storage_path("app/public/{$relativeDir}");

            if (!File::exists($fullPath)) {
                File::ensureDirectoryExists($fullPath, 0755, true);
            }

            /* GENERATE EVALUATION FORM */
            $this->generateEvaluationForm($evaluation, $student, $activity, $form, $mode, $relativeDir, $fileName);

            /* REDIRECT BASED ON MODE */
            if ($mode == 5) {
                return redirect()->route('examiner-panel-evaluation', strtolower(str_replace(' ', '-', $activity->act_name)))
                    ->with('success', 'Evaluation submitted successfully!');
            } else if ($mode == 6) {
                return redirect()->route('chairman-evaluation', strtolower(str_replace(' ', '-', $activity->act_name)))
                    ->with('success', 'Evaluation submitted successfully!');
            }

            /* RETURN ABORT */
            return abort(404, 'Inavalid request. Please try again.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error submitting evaluation: ' . $e->getMessage());
        }
    }

    /* Extract Score Field [Evaluator] - Function */
    private function extractScoreData($formData)
    {
        /* SCORE KEYWORDS */
        $scoreKeywords = ['score', 'mark', 'marks', 'grading', 'grade', 'rating'];
        $scoreData = [];

        /* SEARCH SCORE KEYWORDS DYNAMICLY */
        foreach ($formData as $key => $value) {
            foreach ($scoreKeywords as $keyword) {
                if (stripos($key, $keyword) !== false) {
                    $scoreData[$key] = $value;
                    break;
                }
            }
        }

        /* RETURN SCORE DATA */
        return $scoreData;
    }

    /* Store Evaluation Form Signature [Evaluator] - Function */
    public function storeEvaluationSignature($student, $form, $signatureData, $evaluation, $nomination, $mode)
    {
        try {
            if ($signatureData) {

                /* LOAD SIGNATURE FIELD DATA */
                $signatureFields = FormField::where('af_id', $form->id)
                    ->where('ff_category', 6)
                    ->get();

                /* LOAD EXISTING SIGNATURE FIELD */
                $existingData = $evaluation->evaluation_signature_data
                    ? json_decode($evaluation->evaluation_signature_data, true)
                    : [];

                /* LOAD EVALUATOR DATA */
                $evaluators = Evaluator::where('nom_id', $nomination->id)
                    ->where('eva_status', 3)
                    ->with('staff')
                    ->orderBy('id')
                    ->get();

                /* LOAD CHAIRMAN DATA */
                $chairman = $evaluators->where('eva_role', 2)->first();

                /* LOAD EXAMINER/PANEL DATA */
                $otherEvaluators = $evaluators->where('eva_role', 1)->values();

                /* STORE SIGNATURE LOGIC */
                foreach ($signatureFields as $signatureField) {
                    $signatureKey = $signatureField->ff_signature_key;
                    $dateKey = $signatureField->ff_signature_date_key;

                    if (!isset($signatureData[$signatureKey]) || empty($signatureData[$signatureKey])) {
                        continue;
                    }

                    $role = null;
                    $signerName = null;

                    if ($signatureField->ff_signature_role == 1) {
                        /* STUDENT SIGNATURE LOGIC */
                        $role = 'Student';
                        $signerName = $student->student_name;
                    } else {
                        if ($mode == 6) {
                            /* CHAIRMAN FORM MODE [MASS SIGN MODE] */

                            if (str_contains(strtolower($signatureKey), 'chair')) {
                                if ($chairman && $chairman->staff) {
                                    $role = $signatureField->ff_label;
                                    $signerName = $chairman->staff->staff_name;
                                } else {
                                    continue;
                                }
                            } elseif (preg_match('/(examiner|panel|reviewer|evaluator|assessor)(?:_(\d+))?/i', $signatureKey, $matches)) {
                                $keyword = strtolower($matches[1]);
                                $index = isset($matches[2]) ? intval($matches[2]) - 1 : 0;

                                if (isset($otherEvaluators[$index]) && $otherEvaluators[$index]->staff) {
                                    $role = $signatureField->ff_label;
                                    $signerName = $otherEvaluators[$index]->staff->staff_name;
                                } else {
                                    continue;
                                }
                            } else {
                                continue;
                            }
                        } elseif ($mode == 5) {
                            /* EXAMINER/PANEL FORM MODE [INDIVIDUAL SIGN MODE] */

                            $currentStaff = auth()->user();

                            /* CHECK STAFF IS ASSIGNED TO THE EVALUATION */
                            $matchedEvaluator = $evaluators->first(function ($eva) use ($currentStaff) {
                                return $eva->staff_id == $currentStaff->id;
                            });

                            if (!$matchedEvaluator) {
                                /* SKIP UNASSIGN EVALUATOR */
                                continue;
                            }

                            if (str_contains(strtolower($signatureKey), 'chair') && $matchedEvaluator->eva_role == 2) {
                                /* EXTRA : ALLOWING CHAIR TO SIGN */

                                $role = $signatureField->ff_label;
                                $signerName = $matchedEvaluator->staff->staff_name;
                            } elseif (preg_match('/(examiner|panel|reviewer|evaluator|assessor)/i', $signatureKey)) {
                                /* MATCH EITHER EXAMINER 1 OR 2 */

                                if ($matchedEvaluator->eva_role == 1) {
                                    $role = $signatureField->ff_label;
                                    $signerName = $matchedEvaluator->staff->staff_name;
                                } else {
                                    continue;
                                }
                            } else {
                                continue;
                            }
                        } else {
                            continue;
                        }
                    }

                    $newSignatureData = [
                        $signatureKey => $signatureData[$signatureKey],
                        $dateKey => now()->format('d M Y'),
                        $signatureKey . '_is_cross_approval' => false,
                        $signatureKey . '_name' => $signerName,
                        $signatureKey . '_role' => $role
                    ];

                    $existingData = array_merge($existingData, $newSignatureData);
                }

                $evaluation->evaluation_signature_data = json_encode($existingData);
                $evaluation->save();
            }
        } catch (Exception $e) {
            throw new Exception('Signature storage error: ' . $e->getMessage());
        }
    }

    /* Generate Evaluation Filename [Evaluator] - Function */
    private function generateEvaluationFilename($student, $nomination, $evaluation, $mode)
    {
        if ($mode == 5) {
            /* LOAD CURRENT USER */
            $currentStaff = Staff::where('id', $evaluation->staff_id)->first();

            if (!$currentStaff) {
                return back()->with('error', 'Staff record not found. Please contact administrator for further assistance.');
            }

            /* LOAD EVALUATOR DATA */
            $evaluator = Evaluator::where('nom_id', $nomination->id)
                ->where('staff_id', $currentStaff->id)
                ->where('eva_status', 3)
                ->first();

            /* MAPPING ROLE LOGIC */
            if ($evaluator) {

                if ($evaluator->eva_role == 2) {
                    /* EVALUATOR ROLE = CHAIRMAN */
                    $roleLabel = 'Chairman';
                } elseif ($evaluator->eva_role == 1) {
                    /* EVALUATOR ROLE = EXAMINER/PANEL */

                    /* SEARCH EITHER EXAMINER OR PANEL */
                    $meta = json_decode($evaluator->eva_meta, true);

                    if (!empty($meta['field_label'])) {
                        $label = strtolower($meta['field_label']);

                        if (preg_match('/(examiner|panel|reviewer|evaluator|assessor).*?(\d+)/i', $label, $matches)) {
                            $role = ucfirst($matches[1]);
                            $number = $matches[2];
                            $roleLabel = "{$role}{$number}";
                        } elseif (preg_match('/(examiner|panel|reviewer|evaluator|assessor)/i', $label, $matches)) {
                            $role = ucfirst($matches[1]);
                            $roleLabel = $role;
                        } else {
                            $roleLabel = 'Examiner';
                        }
                    } else {
                        $roleLabel = 'Examiner';
                    }
                } else {
                    $roleLabel = 'Evaluator';
                }
            } else {
                $roleLabel = 'Evaluator';
            }
        } elseif ($mode == 6) {
            /* CHAIRMAN FORM MODE */
            $roleLabel = 'Chairman';
        } else {
            /* DEFAULT FILENAME */
            $roleLabel = 'Evaluation';
        }

        /* RETURN FILENAME */
        return strtoupper($roleLabel) . '-Evaluation_Report_' . $student->student_matricno . '.pdf';
    }

    /* Extract Duration Data [Evaluator] - Function [1] */
    function extractDurationPhrase(string $text): ?array
    {
        /* MAPPING WORD NUMBERS UP TO TWENTY */
        $wordNumbers = [
            'one' => 1,
            'two' => 2,
            'three' => 3,
            'four' => 4,
            'five' => 5,
            'six' => 6,
            'seven' => 7,
            'eight' => 8,
            'nine' => 9,
            'ten' => 10,
            'eleven' => 11,
            'twelve' => 12,
            'thirteen' => 13,
            'fourteen' => 14,
            'fifteen' => 15,
            'sixteen' => 16,
            'seventeen' => 17,
            'eighteen' => 18,
            'nineteen' => 19,
            'twenty' => 20
        ];

        /* BUILD REGEX FOR WORDS OR DIGITS */
        $wordsPattern = implode('|', array_keys($wordNumbers));
        $regex = '/\b('
            . '\d+'
            . '|' . $wordsPattern
            . ')\b\s*'
            . '(day|days|week|weeks|month|months|year|years)\b/i';

        if (preg_match($regex, $text, $m)) {
            /* DIGITS PART */

            $numRaw = strtolower($m[1]);
            $value = is_numeric($numRaw)
                ? (int)$numRaw
                : ($wordNumbers[$numRaw] ?? null);
            if (! $value) {
                return null;
            }

            /* UNIT PART */
            $unit = strtolower($m[2]);
            $unit = rtrim($unit, 's');

            return ['value' => $value, 'unit' => $unit];
        }
        /* RETURN NULL */
        return null;
    }

    /* Extract Duration Data [Evaluator] - Function [2] */
    function findDurationInRequest(Request $request): ?array
    {
        /* LOOP THROUGH REQUEST DATA AND EXTRACT KEYWORDS */
        foreach ($request->all() as $key => $val) {
            if (
                preg_match('/duration|time|deadline|period/i', $key)
                && is_string($val)
            ) {
                if ($duration = $this->extractDurationPhrase($val)) {
                    return $duration;
                }
            }
        }

        /* RETURN NULL */
        return null;
    }

    /* Extract Decision Field [Evaluator] - Function */
    private function mapDecisionToStatus($formData)
    {
        /* DECISION KEYWORDS */
        $decisionKeywords = ['decision', 'status', 'result', 'recommendation', 'verdict', 'dicision'];
        $decisionValue = null;

        /* SEARCH DECISION KEYWORDS DYNAMICLY */
        foreach ($formData as $key => $value) {
            foreach ($decisionKeywords as $keyword) {
                if (stripos($key, $keyword) !== false) {
                    $decisionValue = strtolower($value);
                    break 2;
                }
            }
        }

        if (!$decisionValue) {
            /* STATUS : PENDING IF NOT FOUND */
            return 1;
        }

        /* STATUS MAPPING LOGIC */
        $passKeywords = ['pass', 'passed', 'success', 'successful', 'accepted', 'approved'];
        $minorKeywords = ['minor', 'small', 'slight', 'light', 'little'];
        $majorKeywords = ['major', 'extensive', 'significant', 'substantial', 'many'];
        $resubmitKeywords = ['resubmit', 'represent', 're-examine', 'redefend', 're-present'];
        $failKeywords = ['fail', 'failed', 'unsuccessful', 'reject', 'decline', 'not pass'];

        /* STATUS : FAILED LOGIC */
        foreach ($failKeywords as $keyword) {
            if (stripos($decisionValue, $keyword) !== false) {
                return 6;
            }
        }

        /* STATUS : RESUBMIT/REPRESENT LOGIC */
        foreach ($resubmitKeywords as $keyword) {
            if (stripos($decisionValue, $keyword) !== false) {
                return 5; // Resubmit
            }
        }

        /* STATUS : PASSED WITH OR WITHOUT CORRECTION LOGIC */
        $isPass = false;
        foreach ($passKeywords as $keyword) {
            if (stripos($decisionValue, $keyword) !== false) {
                $isPass = true;
                break;
            }
        }

        if ($isPass) {
            /* PASSED WITH CORRECTIONS LOGIC */

            /* IF PASSED WITH MINOR CORRECTIONS */
            foreach ($minorKeywords as $keyword) {
                if (stripos($decisionValue, $keyword) !== false) {
                    return 3;
                }
            }

            /* IF PASSED WITH MAJOR CORRECTIONS */
            foreach ($majorKeywords as $keyword) {
                if (stripos($decisionValue, $keyword) !== false) {
                    return 4; // Pass with major corrections
                }
            }

            /* RETURN PASSED STATUS */
            return 2;
        }

        /* RETURN PENDING FOR FALLBACK */
        return 1;
    }

    /* Restore Archieves Submission [Evaluator] - Function */
    public function restoreSubmission($student, $activityId, $newduedate)
    {
        /* LOAD ARCHIVED SUBMISSION */
        $submissions = DB::table('submissions as a')
            ->join('documents as b', 'a.document_id', '=', 'b.id')
            ->join('activities as c', 'b.activity_id', '=', 'c.id')
            ->where('a.student_id', $student->id)
            ->where('c.id', $activityId)
            ->where('a.submission_status', 5)
            ->select('a.id as submission_id', 'a.submission_document')
            ->get();

        /* CHANGE SUBMISSION STATUS */
        foreach ($submissions as $submission) {
            if ($submission->submission_document !== '-') {
                DB::table('submissions')
                    ->where('id', $submission->submission_id)
                    ->update([
                        'submission_status' => 3,
                        'submission_duedate' => $newduedate,
                    ]);
            } else {
                DB::table('submissions')
                    ->where('id', $submission->submission_id)
                    ->update([
                        'submission_status' => 1,
                        'submission_duedate' => $newduedate,
                    ]);
            }
        }
    }

    /* Generate Evaluation Report FOrm Document [Evaluator] - Function */
    public function generateEvaluationForm($evaluation, $student, $activity, $form, $mode, $finalDocRelativePath, $fileName)
    {
        try {
            /* LOAD FACULTY DATA */
            $faculty = Faculty::where('fac_status', 3)->first();

            if (!$faculty) {
                return back()->with('error', 'Faculty not found. Document could not be generated. Please contact administrator for further assistance.');
            }

            /* LOAD FORM FIELD DATA */
            $formfields = FormField::where('af_id', $form->id)
                ->orderBy('ff_order')
                ->get();

            /* GET SIGNATURE FIELD */
            $signatures = $formfields->where('ff_category', 6);

            /* GET EXISTING EVALUATION SIGNATURE */
            $signatureData = $evaluation ? json_decode($evaluation->evaluation_signature_data) : null;

            /* MAPPING PROCESS - SUBSTITUTE DATA */
            $userData = [];
            $fhc = new FormHandlerController();
            $userData = $fhc->joinMap($formfields, $student, $activity);

            /* FETCH [EVALUATION] - EXTRA META DATA */
            if ($evaluation && $evaluation->evaluation_meta_data) {
                $extraData = json_decode($evaluation->evaluation_meta_data, true);
                if (is_array($extraData)) {
                    foreach ($extraData as $key => $value) {
                        $normalizedKey = str_replace(' ', '_', strtolower($key));
                        $userData[$normalizedKey] = $value ?? '-';
                    }
                }
            }

            /* RETURN PDF VIEW */
            $pdf = Pdf::loadView('staff.sop.template.input-document', [
                'title' => $fileName,
                'act' => $activity,
                'form_title' => $form->af_title,
                'formfields' => $formfields,
                'userData' => $userData,
                'faculty' => $faculty,
                'signatures' => $signatures,
                'signatureData' => $signatureData,
                'mode' => $mode
            ]);

            /* SAVING DOCUMENT */
            $path = "app/public/{$finalDocRelativePath}/{$fileName}";
            $pdf->save(storage_path($path));

            /* RETURN PATH */
            return $path;
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error generating evaluation form: ' . $e->getMessage());
        }
    }

    /* Evaluation Approval [HIGH ATTENTION - IN PROGRESS] - Route */
    public function evaluationApproval(Request $req, $name)
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
                ->whereNotIn('s.id', function ($query) {
                    $query->select('student_id')
                        ->from('supervisions')
                        ->where('staff_id', auth()->user()->id)
                        ->whereIn('supervision_role', [1, 2]);
                })
                ->whereNotIn('s.id', function ($query) use ($id) {
                    $query->select('w.student_id')
                        ->from('nominations as w')
                        ->join('evaluators as x', 'w.id', '=', 'x.nom_id')
                        ->where('x.staff_id', auth()->user()->id)
                        ->where('w.activity_id', $id)
                        ->where('x.eva_status', 3)
                        ->where('x.eva_role', 1);
                })
                ->where('s.student_status', 1)
                ->where('sa.activity_id', $id)
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
                        if ($allCompleted && !$haveHigherUp) {
                            /* ALL COMPLETED STATUS WITH PENDING FINAL STATUS */
                            return '<span class="badge bg-light-warning">Pending Final Status</span>';
                        } elseif ($allCompleted && $haveHigherUp) {
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
                        ->whereBetween('sa_status', [1, 5])
                        ->exists();

                    if ($submissionInProgress) {
                        return '<span class="badge bg-light-danger p-2">Student submission process <br> not yet completed.</span>';
                    }

                    /* GET STAFF ROLE */
                    $userRole = auth()->user()->staff_role;

                    /* GET REQUIRED ROLES */
                    $rolesRequired = DB::table('form_fields as ff')
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

                    /* MAP STAFF ROLE TO FF SIGNATURE ROLE */
                    $mappedUserRole = [
                        1 => 4,
                        3 => 5,
                        4 => 6,
                    ];

                    $mappedUserRole = $mappedUserRole[$userRole];

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
                    } elseif ($allCompleted) {

                        $evaluations = Evaluation::where('activity_id', $row->activity_id)
                            ->where('student_id', $row->student_id)
                            ->where('semester_id', $row->semester_id)
                            ->where('evaluation_status', 8)
                            ->get();

                        $progressCount = 0;
                        $mockCount = 0;

                        foreach ($evaluations as $evaluation) {
                            $statusType = $this->extractEvaluationStatus($evaluation->evaluation_meta_data);

                            if ($statusType === 1) {
                                $progressCount++;
                            } elseif ($statusType === 2) {
                                $mockCount++;
                            }
                        }

                        // Priority: progress > mock
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
                        <a href="' . route('student-evaluation-approval', [
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

            return view('staff.evaluation.evaluation-approval', [
                'title' => 'Evaluation Approval',
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

    // WILL BE CHECK SOON
    public function extractEvaluationStatus($evaluationMetaData)
    {
        // Decode JSON to array
        $data = json_decode($evaluationMetaData, true);
        if (!is_array($data)) {
            return null; // Invalid JSON
        }

        // Possible status key synonyms
        $statusKeys = [
            'status',
            'stage',
            'session',
            'session_type',
            'presentation_type',
            'current_status',
            'type',
            'evaluation_status',
            'eval_type'
        ];

        // Possible value synonyms
        $progressSynonyms = [
            'progress presentation',
            'progress pres',
            'progress',
            'pp',
            'presentation progress',
            'initial presentation'
        ];

        $mockVivaSynonyms = [
            'mock viva',
            'mv',
            'mock',
            'mock v',
            'mock exam',
            'mock defense',
            'mock defence',
            'mock test'
        ];

        $statusValue = null;

        // 1️⃣ Find the status-like key
        foreach ($statusKeys as $key) {
            foreach ($data as $jsonKey => $jsonValue) {
                if (stripos($jsonKey, $key) !== false) {
                    $statusValue = trim($jsonValue);
                    break 2;
                }
            }
        }

        // 2️⃣ If not found, try to guess by checking short text fields
        if (!$statusValue) {
            foreach ($data as $jsonValue) {
                if (is_string($jsonValue) && strlen($jsonValue) < 50) {
                    if (preg_match('/progress/i', $jsonValue) || preg_match('/mock/i', $jsonValue)) {
                        $statusValue = trim($jsonValue);
                        break;
                    }
                }
            }
        }

        // 3️⃣ Match against synonyms
        if ($statusValue) {
            $statusValueLower = strtolower($statusValue);

            foreach ($progressSynonyms as $syn) {
                if (stripos($statusValueLower, strtolower($syn)) !== false) {
                    return 1; // Progress Presentation
                }
            }

            foreach ($mockVivaSynonyms as $syn) {
                if (stripos($statusValueLower, strtolower($syn)) !== false) {
                    return 2; // Mock Viva
                }
            }
        }

        return null; // No match
    }

    /* Each Student Evaluation Approval [HIGH ATTENTION - IN PROGRESS] - Route */
    public function studentEvaluationApproval(Request $req, $activityID, $studentID)
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
                ->whereNotIn('s.id', function ($query) {
                    $query->select('student_id')
                        ->from('supervisions')
                        ->where('staff_id', auth()->user()->id)
                        ->whereIn('supervision_role', [1, 2]);
                })
                ->where('s.student_status', 1)
                ->where('f.activity_id', $activityID)
                ->where('s.id', $studentID)
                ->orderBy('s.student_matricno');


            if ($req->ajax()) {

                if ($req->has('semester') && !empty($req->input('semester'))) {
                    $data->where('ss.semester_id', $req->input('semester'));
                }

                if ($req->has('status') && !empty($req->input('status'))) {
                    if ($req->input('status') == 10) {
                        $data->where('f.evaluation_isFinal', 1);
                    } else {
                        $data->where('f.evaluation_status', $req->input('status'));
                    }
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
                        $statusLines[] = '<span class="badge bg-light-danger">Rjected : Supervisor</span>';
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
                        ->whereIn('ff.ff_signature_role', [4, 5, 6])
                        ->select('ff.ff_signature_role', 'ff.ff_signature_key')
                        ->get();

                    /* ROLES MAPPING */
                    $roleLabels = [
                        4 => 'Committee',
                        5 => 'Deputy Dean',
                        6 => 'Dean',
                    ];

                    /* LOAD EVALUATION DATA */
                    $evaluation = Evaluation::where('id', $row->evaluation_id)->first();

                    /* GET EVALUATION SIGNATURE DATA */
                    $signatureData = $evaluation && $evaluation->evaluation_signature_data
                        ? json_decode($evaluation->evaluation_signature_data, true)
                        : [];

                    /* LOOP THROUGH REQUIRED ROLES */
                    foreach ($requiredRoles as $role) {
                        $roleName = $roleLabels[$role->ff_signature_role] ?? 'Unknown Role';
                        $sigKey = $role->ff_signature_key;

                        if (!empty($signatureData[$sigKey])) {
                            $statusLines[] = '<span class="badge bg-light-success">Approved : ' . $roleName . '</span>';
                        } else {
                            $statusLines[] = '<span class="badge bg-light-danger">Required : ' . $roleName . '</span>';
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
                    $PENDING_HU = 10;

                    /* GET EACH ATTRIBUTE IDs */
                    $activityId    = $row->activity_id;
                    $evaluationId  = $row->evaluation_id;

                    /* MAPS ROLE FROM DB */
                    $dbRole = auth()->user()->staff_role; // 1 = Committee, 3 = DD, 4 = Dean

                    $roleMap = [
                        1 => 4, // Committee
                        3 => 5, // Deputy Dean
                        4 => 6, // Dean
                    ];

                    /* SET AND MAP ROLE */
                    $myRole = $roleMap[$dbRole] ?? null;

                    /* LOAD REQUIRED ROLES */
                    $requiredRoles = DB::table('activity_forms as a')
                        ->join('form_fields as f', 'a.id', '=', 'f.af_id')
                        ->where('a.activity_id', $activityId)
                        ->where('a.af_target', 5)
                        ->where('f.ff_category', 6)
                        ->pluck('f.ff_signature_role')
                        ->unique()
                        ->toArray();

                    $commRequired  = in_array(4, $requiredRoles, true);
                    $ddRequired    = in_array(5, $requiredRoles, true);
                    $deanRequired  = in_array(6, $requiredRoles, true);

                    /* LOAD SIGNED DATA */
                    $sigData       = json_decode($row->evaluation_signature_data ?? '[]', true);
                    $commSigned    = !empty($sigData['comm_signature']);
                    $ddSigned      = !empty($sigData['deputy_dean_signature']);
                    $deanSigned    = !empty($sigData['dean_signature']);

                    /* CHECK IF THIS LEVEL IS COMPLETE */
                    $levelComplete = (
                        ($commRequired && $ddRequired && $deanRequired && $commSigned && $ddSigned && $deanSigned)
                        || ($commRequired && $ddRequired && !$deanRequired && $commSigned && $ddSigned)
                        || ($commRequired && !$ddRequired && $deanRequired && $commSigned && $deanSigned)
                        || (!$commRequired && $ddRequired && $deanRequired && $ddSigned && $deanSigned)
                        || ($commRequired && !$ddRequired && !$deanRequired && $commSigned)
                        || (!$commRequired && $ddRequired && !$deanRequired && $ddSigned)
                        || (!$commRequired && !$ddRequired && $deanRequired && $deanSigned)
                    );

                    /* AM I REQUIRED TO SIGN? */
                    $iAmRequired = ($myRole === 4 && $commRequired)
                        || ($myRole === 5 && $ddRequired)
                        || ($myRole === 6 && $deanRequired);

                    /* HAVE I ALREADY SIGNED? */
                    $iHaveSigned = ($myRole === 4 && $commSigned)
                        || ($myRole === 5 && $ddSigned)
                        || ($myRole === 6 && $deanSigned);

                    /* SHOW ACTION BUTTONS ONLY IF */
                    if (
                        $row->evaluation_status === $PENDING_HU &&
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

                    /* ELSE: No action */
                    return '<div class="fst-italic text-muted">No action required</div>';
                });

                $table->rawColumns(['student_photo', 'evaluator', 'evaluation_document', 'evaluation_date', 'evaluation_status', 'evaluation_semester', 'action']);

                return $table->make(true);
            }

            return view('staff.evaluation.evaluation-student-approval', [
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

    /* Evaluation Report Approval - Function [Staff] | Email : Yes With Works | [HIGH ATTENTION - IN PROGRESS] */
    public function approvePanelEvaluation(Request $req, $evaluationID, $option)
    {
        try {
            /* DECRYPT PROCESS */
            $evaluationID = decrypt($evaluationID);

            /* LOAD EVALUATION DATA */
            $evaluation = Evaluation::where('id', $evaluationID)->first();

            if (!$evaluation) {
                return back()->with('error', 'Evaluation not found. Operation could not be processed. Please try again.');
            }

            /* LOAD USER DATA */
            $authUser = auth()->user();

            if (!$authUser) {
                return back()->with('error', 'Unauthorized access : Staff record is not found.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $evaluation->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Student record not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $evaluation->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Activity record not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD STUDENT ACTIVITY DATA */
            $studentActivity = StudentActivity::where('student_id', $evaluation->student_id)
                ->where('activity_id', $evaluation->activity_id)
                ->where('semester_id', $evaluation->semester_id)
                ->first();

            if (!$studentActivity) {
                return back()->with('error', 'Student confirmation record not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD PROCEDURE DATA */
            $procedure = Procedure::where([
                'activity_id' => $evaluation->activity_id,
                'programme_id' => $student->programme_id
            ])->first();

            if (!$procedure) {
                return back()->with('error', 'Procedure not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD ACTIVITY FORM */
            $form = ActivityForm::where('activity_id', $evaluation->activity_id)
                ->where('af_target', 5)
                ->first();

            if (!$form) {
                return back()->with('error', 'Evaluation form not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD NOMINATION DATA */
            $nomination = Nomination::where('student_id', $evaluation->student_id)
                ->where('activity_id', $evaluation->activity_id)
                ->where('semester_id', $evaluation->semester_id)
                ->first();

            if (!$nomination) {
                return back()->with('error', 'Nomination record not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD SEMESTER DATA */
            $currsemester = Semester::where('id', $evaluation->semester_id)->first();

            if (!$currsemester) {
                return back()->with('error', 'Semester record not found. Operation could not be processed. Please contact administrator for further assistance.');
            }

            /* GENERATE FILENAME BASED ON ROLES */
            $fileName = $this->generateEvaluationFilename($student, $nomination, $evaluation, 5);

            /* CHECK SUPERVISOR ROLE (SV or CoSV) */
            $supervision = Supervision::where('student_id', $student->id)
                ->where('staff_id', $authUser->id)->first();

            /* CHECK IF SV IS REQUIRED */
            $hasSvfield = DB::table('activity_forms as a')
                ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                ->where('a.activity_id', $evaluation->activity_id)
                ->where('b.ff_category', 6)
                ->where('b.ff_signature_role', 2)
                ->where('a.id', $form->id)
                ->exists();

            /* CHECK IF CO-SV IS REQUIRED */
            $hasCoSvfield = DB::table('activity_forms as a')
                ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                ->where('a.activity_id', $evaluation->activity_id)
                ->where('b.ff_category', 6)
                ->where('b.ff_signature_role', 3)
                ->where('a.id', $form->id)
                ->exists();

            $hasCoSv = $hasSvfield && $hasCoSvfield;

            /* SUBMISSION CONTROLLER INSTANCE */
            $sc = new SubmissionController();

            /* UPDATE EVALUATION RECORDS */
            if ($option == 1) {
                /* EVALUATION REPORT APPROVED */

                /* DETERMINE APPROVAL ROLE AND STATUS */
                [$role, $status] = $sc->determineApprovalRoleStatus($supervision, null, $authUser->staff_role, 3);

                /* STORE EVALUATION SIGNATURE */
                $sc->storeSignature($evaluation->activity_id, $student, $currsemester, $form, $req->signatureData, $fileName, $role, $authUser, $status, 3, null, $evaluation);

                /* RELOAD STUDENT ACTIVITY DATA */
                $updatedEvaluation = Evaluation::where('id', $evaluation->id)->first();

                if (!$updatedEvaluation) {
                    return back()->with('error', 'Evaluation record not found. Approval could not be processed. Please contact administrator for further assistance.');
                }

                /* DECODE UPDATED SIGNATURE DATA */
                $updatedSignatureData = json_decode($updatedEvaluation->evaluation_signature_data ?? '[]', true);

                /* GENERATE EVALUATION FORM DOCUMENT */
                $progcode = strtoupper($student->programmes->prog_code);
                $activityName = str_replace(['/', '\\'], '-', $activity->act_name);

                /* SET RELATIVE DIRECTORY */
                $rawLabel = $currsemester->sem_label;
                $semesterlabel = str_replace('/', '', $rawLabel);
                $semesterlabel = trim($semesterlabel);

                if ($procedure->is_repeatable == 1) {
                    $relativeDir = "{$student->student_directory}/{$progcode}/{$activityName}/{$semesterlabel}/Evaluation";
                } else {
                    $relativeDir = "{$student->student_directory}/{$progcode}/{$activityName}/Evaluation/{$semesterlabel}";
                }

                /* LOAD FINAL DIRECTORY */
                $fullPath = storage_path("app/public/{$relativeDir}");

                if (!File::exists($fullPath)) {
                    File::ensureDirectoryExists($fullPath, 0755, true);
                }

                /* GENERATE EVALUATION FORM */
                $this->generateEvaluationForm($updatedEvaluation, $student, $activity, $form, 5, $relativeDir, $fileName);

                /* HANDLE SIGNATURE LOGIC */

                /* HANDLE FORM ROLES */
                $formRoles = DB::table('form_fields as b')
                    ->where('b.af_id', $form->id)
                    ->where('b.ff_category', 6)
                    ->pluck('b.ff_signature_role')
                    ->unique()
                    ->toArray();

                if (in_array($role, [2, 3])) {
                    /* SUPERVISOR / CO-SUPERVISOR LOGIC */

                    $hasHigherRoles   = collect($formRoles)->intersect([4, 5, 6])->isNotEmpty();
                    $hasSvSignature   = isset($updatedSignatureData['sv_signature']);
                    $hasCoSvSignature = isset($updatedSignatureData['cosv_signature']);
                    $allSigned        = $hasCoSv
                        ? ($hasSvSignature && $hasCoSvSignature)
                        : $hasSvSignature;

                    if ($allSigned) {
                        if (! $hasHigherRoles) {
                            $finalStatus = 8;
                        } else {
                            $finalStatus = 10;
                        }
                    } else {
                        $finalStatus = 9;
                    }
                } elseif (in_array($role, [4, 5, 6])) {
                    /* COMMITTEE / DEPUTY-DEAN / DEAN LOGIC */

                    $roleSignatures = [
                        4 => in_array(4, $formRoles)
                            ? isset($updatedSignatureData['comm_signature_date'])
                            : true,
                        5 => in_array(5, $formRoles)
                            ? isset($updatedSignatureData['deputy_dean_signature_date'])
                            : true,
                        6 => in_array(6, $formRoles)
                            ? isset($updatedSignatureData['dean_signature_date'])
                            : true,
                    ];

                    $allSigned = collect($roleSignatures)
                        ->only($formRoles)
                        ->every(fn($signed) => $signed);

                    $finalStatus = $allSigned ? 8 : 10;
                }

                /* UPDATE STATUS */
                $evaluation->evaluation_status = $finalStatus;

                /* FINALIZE PROCESS WITH EMAIL NOTIFICATION TO PANEL */
                if ($finalStatus == 8) {
                    $evaluation->evaluation_isFinal = 1;
                }

                /* UPDATE EVALUATION */
                $evaluation->save();

                /* RETURN SUCCESS */
                return back()->with('success', $student->student_name . ' evaluation report for ' . $activity->act_name . ' has been approved.');
            } elseif ($option == 2) {
                /* EVALUATOR REPORT REJECTED */
            }

            /* RETURN ABORT */
            return abort(404, 'Inavalid request. Please try again.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error approving evaluation: ' . $e->getMessage());
        }
    }

    public function finalizeEvaluation(Request $req, $studentActID)
    {
        try {

            /* DECRYPT IDs */
            $studentActID = Crypt::decrypt($studentActID);

            /* LOAD STUDENT ACTIVITY DATA */
            $studentactivity = StudentActivity::where('id', $studentActID)->first();

            if (!$studentactivity) {
                return back()->with('error', 'Student activity not found. Could not finalize evaluation. Please try again.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id',  $studentactivity->student_id)->first();
            if (!$student) {
                return back()->with('error', 'Student not found. Could not finalize evaluation. Please try again.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id',  $studentactivity->activity_id)->first();
            if (!$activity) {
                return back()->with('error', 'Activity not found. Could not finalize evaluation. Please try again.');
            }

            /* SUBMISSION CONTROLLER INITIALIZE */
            $sc = new SubmissionController();

            /* EXTRACT EVALUATION TYPE FROM REQUEST */
            if ($req->evaluation_type == 1) {
                $studentactivity->update([
                    'sa_status' => 13
                ]);


                $message = $student->student_name . " activity for " . $activity->act_name . " has been marked as Passed & Continue. Student will continue their progress presentation for this activity in next semester. An email notification has been sent to the student.";
            } elseif ($req->evaluation_type == 2) {
                $studentactivity->update([
                    'sa_status' => 3
                ]);

                $message = $student->student_name . " activity for " . $activity->act_name . " has been marked as Approved & Completed. An email notification has been sent to the student.";
            }

            /* FINALIZE SUBMISSION */
            $sc->finalizeSubmission($student, $studentactivity->activity_id);

            /* RETURN IF SUCCESS */
            return back()->with('success', $message);
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error finalizing evaluation: ' . $e->getMessage());
        }
    }
}
