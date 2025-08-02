<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\Activity;
use App\Models\Semester;
use App\Models\Evaluator;
use App\Models\FormField;
use App\Models\Programme;
use App\Models\Evaluation;
use App\Models\Nomination;
use App\Models\ActivityForm;
use Illuminate\Http\Request;
use App\Models\StudentActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
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
                    $button = '';
                    $currsemester = Semester::where('sem_status', 1)->first();

                    if ($row->evaluation_isFinal != 1 && ($row->semester_id == $currsemester->id)) {
                        $button = '
                            <a href="' . route('evaluation-student', ['studentId' => Crypt::encrypt($row->student_id), 'actId' => Crypt::encrypt($row->activity_id), 'semesterId' => Crypt::encrypt($row->semester_id), 'mode' => 5]) . '" class="avtar avtar-xs btn-light-primary">
                                <i class="ti ti-edit f-20"></i>
                            </a>
                        ';
                    } else {
                        $button = '<div class="fst-italic text-muted">No action required</div>';
                    }

                    return $button;
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
                    $button = '';
                    $currsemester = Semester::where('sem_status', 1)->first();

                    if ($row->evaluation_isFinal != 1 && ($row->semester_id == $currsemester->id)) {
                        $button = '
                            <a href="' . route('evaluation-student', ['studentId' => Crypt::encrypt($row->student_id), 'actId' => Crypt::encrypt($row->activity_id), 'semesterId' => Crypt::encrypt($row->semester_id), 'mode' => 6]) . '" class="avtar avtar-xs btn-light-primary">
                                <i class="ti ti-edit f-20"></i>
                            </a>
                        ';
                    } else {
                        $button = '<div class="fst-italic text-muted">No action required</div>';
                    }

                    return $button;
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
    public function committeeEvaluation(Request $req, $name)
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

            return view('staff.evaluation.committee-evaluation-management', [
                'title' => 'Committee - Evaluation Management',
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

    /* Evaluation Student */
    public function evaluationStudent($studentId, $actId, $semesterId, $mode)
    {
        try {

            /* GET ID'S */
            $studentId = decrypt($studentId);
            $actId = decrypt($actId);
            $semId = decrypt($semesterId);

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
                ->where('a.id', $studentId)
                ->first();

            /* GET ACTIVITY DATA */
            $act =  DB::table('activities as a')->join('procedures as b', 'a.id', '=', 'b.activity_id')
                ->select('a.id', 'a.act_name')
                ->where('a.id', '=', $actId)
                ->first();

            if (!$act) {
                abort(404, 'Activity not found');
            }

            /* GET ACTIVITY FORM */
            if ($mode == 5) {
                // Examiner / Panel
                $actForm = ActivityForm::where('activity_id', $actId)
                    ->where('af_target', 5)
                    ->first();
            } else if ($mode == 6) {
                // Chairman
                $actForm = ActivityForm::where('activity_id', $actId)
                    ->where('af_target', 4)
                    ->first();
            }


            if (!$actForm) {
                return back()->with('error', 'Oops! Form for this activity were not found. Please add the form first at the Form Setting page.');
            }

            $examinerSign = FormField::where('af_id', $actForm->id)->where('ff_signature_role', 8)->select('ff_signature_key')->get();

            /* LINK ASSIGNMENT */
            $page = '';
            $link = '';

            if ($mode == 5) {
                $page = 'Examiner / Panel';
                $link =  route('examiner-panel-evaluation', strtolower(str_replace(' ', '-', $act->act_name)));
            } else if ($mode == 6) {
                $page = 'Chairman';
                $link =  route('chairman-evaluation', strtolower(str_replace(' ', '-', $act->act_name)));
            }

            return view('staff.evaluation.evaluation-student', [
                'title' => $data->student_name . 'Evaluation',
                'act' => $act,
                'actform' => $actForm,
                'examinerSign' => $examinerSign,
                'data' => $data,
                'mode' => $mode,
                'page' => $page,
                'link' => $link,
                'semId' => $semId
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* View Nomination Form */
    public function viewEvaluationForm(Request $req)
    {
        try {

            $mode = $req->input('mode');
            $staffId = auth()->user()->id;
            $semid = $req->input('semid');


            /* GET STUDENT DATA */
            $student = Student::whereId($req->input('studentid'))->first();

            if (!$student) {
                return back()->with('error', 'Student not found.');
            }

            /* GET ACTIVITY FORM DATA */
            $form = ActivityForm::whereId($req->input('afid'))->first();

            if (!$form) {
                return back()->with('error', 'Form not found.');
            }

            /* GET ACTIVITY DATA */
            $actID = $req->input('actid');
            $act = Activity::where('id', $actID)->first();

            if (!$act) {
                return back()->with('error', 'Activity not found.');
            }

            /* GET FACULTY DATA */
            $faculty = Faculty::where('fac_status', 3)->first();

            if (!$faculty) {
                return back()->with('error', 'Faculty not found.');
            }

            /* FETCH - FORM FIELD */
            $formfields = FormField::where('af_id', $form->id)
                ->orderBy('ff_order')
                ->get();

            /* FETCH - SIGNATURE */
            $signatures = $formfields->where('ff_category', 6);

            /* FETCH - EVALUATION */
            $evaluationRecord = Evaluation::where([
                ['activity_id', $actID],
                ['student_id', $student->id],
                ['staff_id', $staffId],
                ['semester_id', $semid],
            ])->first();

            $signatureData = $evaluationRecord ? json_decode($evaluationRecord->evaluation_signature_data) : null;

            /* MAPPING PROCESS - SUBSTITUTE DATA */
            $userData = [];

            $specialMappings = [
                'prog_mode' => [
                    'FT' => 'Full-Time',
                    'PT' => 'Part-Time',
                ],
            ];

            $joinMap = [
                'students' => [
                    'programmes' => [
                        'alias' => 'b',
                        'table' => 'programmes',
                        'on' => ['a.programme_id', '=', 'b.id'],
                    ],
                    'semesters' => [
                        'alias' => 'c',
                        'table' => 'semesters',
                        'on' => ['a.semester_id', '=', 'c.id'],
                    ],
                ],
                'submissions' => [
                    'documents' => [
                        'alias' => 'b',
                        'table' => 'documents',
                        'on' => ['a.document_id', '=', 'b.id'],
                    ],
                ],
                'documents' => [
                    'submissions' => [
                        'alias' => 'b',
                        'table' => 'submissions',
                        'on' => ['a.id', '=', 'b.document_id'],
                    ],
                ],
                'staff' => [
                    'supervisions' => [
                        'alias' => 'b',
                        'table' => 'supervisions',
                        'on' => ['a.id', '=', 'b.staff_id'],
                    ],
                ],
            ];

            foreach ($formfields as $field) {
                $baseTable = $field->ff_table;
                $key = $field->ff_datakey;

                if (empty($baseTable) || empty($key)) {
                    $userData[str_replace(' ', '_', strtolower($field->ff_label))] = '-';
                    continue;
                }

                $extraKey = $field->ff_extra_datakey;
                $extraCondition = $field->ff_extra_condition;

                $query = DB::table($baseTable . ' as a');

                preg_match_all('/\w+/', $key, $matches);
                $keys = $matches[0];
                $fullKeys = [];
                $joinedAliases = [];

                foreach ($keys as $column) {
                    $fullCol = 'a.' . $column;

                    if (isset($joinMap[$baseTable])) {
                        foreach ($joinMap[$baseTable] as $joinName => $joinData) {
                            $columns = Schema::getColumnListing($joinData['table']);
                            if (in_array($column, $columns)) {
                                if (!in_array($joinData['alias'], $joinedAliases)) {
                                    $query->join($joinData['table'] . ' as ' . $joinData['alias'], ...$joinData['on']);
                                    $joinedAliases[] = $joinData['alias'];
                                }
                                $fullCol = $joinData['alias'] . '.' . $column;
                                break;
                            }
                        }
                    }

                    $fullKeys[$column] = $fullCol;
                }

                if ($baseTable === 'students') {
                    $query->where('a.id', $student->id);
                }

                if ($baseTable === 'semesters') {
                    $query->where('a.sem_status', 1);
                }

                if ($baseTable === 'submissions') {
                    if (!in_array('b', $joinedAliases)) {
                        $joinData = $joinMap['submissions']['documents'];
                        $query->join($joinData['table'] . ' as ' . $joinData['alias'], ...$joinData['on']);
                        $joinedAliases[] = 'b';
                    }
                    $query->where('a.student_id', $student->id)
                        ->where('a.submission_status', 3)
                        ->where('b.activity_id', $act->id);
                }

                if ($baseTable === 'documents') {
                    if (!in_array('b', $joinedAliases)) {
                        $joinData = $joinMap['documents']['submissions'];
                        $query->join($joinData['table'] . ' as ' . $joinData['alias'], ...$joinData['on']);
                        $joinedAliases[] = 'b';
                    }
                    $query->where('b.student_id', $student->id)
                        ->where('b.submission_status', 3)
                        ->where('a.activity_id', $act->id)
                        ->where('a.isShowDoc', 1);
                }

                if ($baseTable === 'staff') {
                    if (!in_array('b', $joinedAliases)) {
                        $joinData = $joinMap['staff']['supervisions'];
                        $query->join($joinData['table'] . ' as ' . $joinData['alias'], ...$joinData['on']);
                        $joinedAliases[] = 'b';
                    }
                    $query->where('b.student_id', $student->id);
                }

                if (!empty($extraKey) && !empty($extraCondition)) {
                    $query->where($extraKey, $extraCondition);
                }

                $results = $query->get(array_values($fullKeys));

                $finalValue = '-';

                if (!$results->isEmpty()) {
                    $tempLines = [];

                    foreach ($results as $row) {
                        $tempParts = [];

                        foreach ($fullKeys as $col => $_alias) {
                            $val = $row->$col ?? '';

                            // Apply special value mapping if available
                            if (isset($specialMappings[$col]) && isset($specialMappings[$col][$val])) {
                                $val = $specialMappings[$col][$val];
                            }

                            // Format as date if valid
                            if ($val && strtotime($val)) {
                                $carbonDate = Carbon::parse($val);
                                $val = $carbonDate->format('j F Y g:ia');
                            }

                            $tempParts[] = $val;
                        }

                        $tempLines[] = implode(' : ', $tempParts);
                    }

                    $finalValue = implode("<br>", $tempLines);
                }

                $userData[str_replace(' ', '_', strtolower($field->ff_label))] = $finalValue ?: '-';
            }

            /* FETCH [EVALUATION] - EXTRA META DATA */
            if ($evaluationRecord && $evaluationRecord->evaluation_meta_data) {
                $extraData = json_decode($evaluationRecord->evaluation_meta_data, true);
                if (is_array($extraData)) {
                    foreach ($extraData as $key => $value) {
                        $normalizedKey = str_replace(' ', '_', strtolower($key));
                        $userData[$normalizedKey] = $value ?? '-';
                    }
                }
            }

            $html = view('staff.sop.template.input-form', [
                'title' => $act->act_name . " Document",
                'act' => $act,
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
            return back()->with('error', 'Oops! Error fetching nomination form: ' . $e->getMessage());
        }
    }

    /* Submit Evaluation Form [IN FOCUS] */
    public function submitEvaluation(Request $req, $studentId, $mode)
    {
        try {
            $studentId = decrypt($studentId);
            $staffId = auth()->user()->id;
            $option = $req->input('opt');
            $semId = $req->input('semester_id');


            // 1 - Load student
            $student = Student::where('id', $studentId)->first();
            if (!$student) {
                return back()->with('error', 'Oops! Student not found');
            }

            // 2 - Load activity & Student Activity
            $actID = $req->input('activity_id');
            $activity = Activity::find($actID);
            if (!$activity) {
                return back()->with('error', 'Oops! Activity not found');
            }

            $studentActivity = StudentActivity::where('student_id', $studentId)
                ->where('activity_id', $actID)
                ->first();

            if (!$studentActivity) {
                return back()->with('error', 'Oops! Student activity record not found');
            }

            // 3 - Load correct form based on mode
            if ($mode == 5) {
                $form = ActivityForm::where('activity_id', $actID)
                    ->where('af_target', 5)
                    ->first();
            } elseif ($mode == 6) {
                $form = ActivityForm::where('activity_id', $actID)
                    ->where('af_target', 4)
                    ->first();
            }

            if (!$form) {
                return back()->with('error', 'Oops! Evaluation form not found');
            }

            // 4 - Load nomination
            $nomination = Nomination::where('student_id', $studentId)
                ->where('activity_id', $actID)
                ->where('semester_id', $semId)
                ->first();
            if (!$nomination) {
                return back()->with('error', 'Oops! Nomination record not found');
            }

            // 5 - Load evaluation
            $evaluation = Evaluation::where('student_id', $studentId)
                ->where('activity_id', $actID)
                ->where('staff_id', $staffId)
                ->where('semester_id', $semId)
                ->first();
            if (!$evaluation) {
                return back()->with('error', 'Oops! Evaluation record not found');
            }

            // 6 - Load semester
            $currsemester = Semester::where('id', $semId)->first();

            if (!$currsemester) {
                return back()->with('error', 'Oops! Current semester not found');
            }

            // 7 - Prepare form meta data
            $formData = $req->except(['_token', 'signatureData', 'opt', 'semester_id']);
            $scoreData = $this->extractScoreData($formData);
            $evaluationMeta = $formData;
            $evaluationMeta['Score'] = $scoreData;

            // 8 - Handle signatures
            if ($req->has('signatureData')) {
                $this->storeEvaluationSignature(
                    $student,
                    $form,
                    $req->signatureData,
                    $evaluation,
                    $nomination,
                    $mode
                );
            }

            // 9 - Generate filename
            $fileName = $this->generateEvaluationFilename($student, $nomination, $mode);

            // 10 - Update evaluation record
            if ($option == 1) {
                $evaluation->evaluation_status = 7; // Submitted (Draft)
            } elseif ($option == 2) {
                if ($mode == 5) {
                    $evaluation->evaluation_status = 8; // Confirmed [Examiner/Panel]
                } elseif ($mode == 6) {
                    $decisionStatus = $this->mapDecisionToStatus($req->all());
                    $evaluation->evaluation_status = $decisionStatus;

                    if ($decisionStatus == 2) {
                        $studentActivity->sa_status = 3;
                    } elseif($decisionStatus == 3 || $decisionStatus == 4) {
                        $studentActivity->sa_status = 8;
                    } elseif($decisionStatus == 5) {
                        $studentActivity->sa_status = 9;
                    } else {
                        $studentActivity->sa_status = 5;
                    }
                    $studentActivity->save();
                }
                $evaluation->evaluation_isFinal = 1;
            }

            $evaluation->evaluation_date = now();
            $evaluation->evaluation_meta_data = json_encode($evaluationMeta);
            $evaluation->evaluation_document = $fileName;
            $evaluation->save();

            // 11 - Generate Evaluation Form File
            $progcode = strtoupper($student->programmes->prog_code);
            $activityName = str_replace(['/', '\\'], '-', $activity->act_name);

            // 12 - Sem Label Format
            $rawLabel = $currsemester->sem_label;
            $semesterlabel = str_replace('/', '', $rawLabel);
            $semesterlabel = trim($semesterlabel);

            $relativeDir = "{$student->student_directory}/{$progcode}/{$activityName}/Evaluation/{$semesterlabel}";
            $fullPath = storage_path("app/public/{$relativeDir}");

            if (!File::exists($fullPath)) {
                File::ensureDirectoryExists($fullPath, 0755, true);
            }

            $this->generateEvaluationForm($actID, $student,$semId, $form, $mode, $relativeDir, $fileName);

            // 13 - Redirect
            if ($mode == 5) {
                return redirect()->route('examiner-panel-evaluation', strtolower(str_replace(' ', '-', $activity->act_name)))
                    ->with('success', 'Evaluation submitted successfully!');
            } else if ($mode == 6) {
                return redirect()->route('chairman-evaluation', strtolower(str_replace(' ', '-', $activity->act_name)))
                    ->with('success', 'Evaluation submitted successfully!');
            }

            return abort(404);
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error submitting evaluation: ' . $e->getMessage());
        }
    }

    private function generateEvaluationFilename($student, $nomination, $mode)
    {
        if ($mode == 5) {
            $currentStaff = auth()->user();

            $evaluator = Evaluator::where('nom_id', $nomination->id)
                ->where('staff_id', $currentStaff->id)
                ->where('eva_status', 3)
                ->first();

            if ($evaluator) {
                if ($evaluator->eva_role == 2) {
                    $roleLabel = 'Chairman';
                } elseif ($evaluator->eva_role == 1) {
                    // Check eva_meta for keyword extraction
                    $meta = json_decode($evaluator->eva_meta, true);

                    if (!empty($meta['field_label'])) {
                        $label = strtolower($meta['field_label']);

                        if (preg_match('/(examiner|panel|reviewer|evaluator|assessor).*?(\d+)/i', $label, $matches)) {
                            $role = ucfirst($matches[1]);  // Capitalize first letter
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
            $roleLabel = 'Chairman';
        } else {
            $roleLabel = 'Evaluation';
        }

        return strtoupper($roleLabel) . '-Evaluation_Report_' . $student->student_matricno . '.pdf';
    }

    /* Map decision to status code */
    private function mapDecisionToStatus($formData)
    {
        $decisionKeywords = ['decision', 'status', 'result', 'recommendation', 'verdict', 'dicision'];
        $decisionValue = null;

        // Find decision field dynamically
        foreach ($formData as $key => $value) {
            foreach ($decisionKeywords as $keyword) {
                if (stripos($key, $keyword) !== false) {
                    $decisionValue = strtolower($value);
                    break 2;
                }
            }
        }

        if (!$decisionValue) {
            return 1; // Default to pending if not found
        }

        // Status mapping logic
        $passKeywords = ['pass', 'passed', 'success', 'successful', 'accepted', 'approved'];
        $minorKeywords = ['minor', 'small', 'slight', 'light', 'little'];
        $majorKeywords = ['major', 'extensive', 'significant', 'substantial', 'many'];
        $resubmitKeywords = ['resubmit', 'represent', 're-examine', 'redefend', 're-present'];
        $failKeywords = ['fail', 'failed', 'unsuccessful', 'reject', 'decline', 'not pass'];

        // Check for failure
        foreach ($failKeywords as $keyword) {
            if (stripos($decisionValue, $keyword) !== false) {
                return 6; // Failed
            }
        }

        // Check for resubmit
        foreach ($resubmitKeywords as $keyword) {
            if (stripos($decisionValue, $keyword) !== false) {
                return 5; // Resubmit
            }
        }

        // Check for pass with corrections
        $isPass = false;
        foreach ($passKeywords as $keyword) {
            if (stripos($decisionValue, $keyword) !== false) {
                $isPass = true;
                break;
            }
        }

        if ($isPass) {
            foreach ($minorKeywords as $keyword) {
                if (stripos($decisionValue, $keyword) !== false) {
                    return 3; // Pass with minor corrections
                }
            }

            foreach ($majorKeywords as $keyword) {
                if (stripos($decisionValue, $keyword) !== false) {
                    return 4; // Pass with major corrections
                }
            }

            return 2; // Passed
        }

        return 1; // Default to pending
    }

    /* Extract score data */
    private function extractScoreData($formData)
    {
        $scoreKeywords = ['score', 'mark', 'marks', 'grading', 'grade', 'rating'];
        $scoreData = [];

        foreach ($formData as $key => $value) {
            foreach ($scoreKeywords as $keyword) {
                if (stripos($key, $keyword) !== false) {
                    $scoreData[$key] = $value;
                    break;
                }
            }
        }

        return $scoreData;
    }

    public function storeEvaluationSignature($student, $form, $signatureData, $evaluation, $nomination, $mode)
    {
        try {
            if (!$signatureData || !is_array($signatureData)) {
                throw new Exception('Invalid signature data');
            }

            $signatureFields = FormField::where('af_id', $form->id)
                ->where('ff_category', 6)
                ->get();

            $existingData = $evaluation->evaluation_signature_data
                ? json_decode($evaluation->evaluation_signature_data, true)
                : [];

            $evaluators = Evaluator::where('nom_id', $nomination->id)
                ->where('eva_status', 3)
                ->with('staff')
                ->orderBy('id')
                ->get();

            $chairman = $evaluators->where('eva_role', 2)->first();
            $otherEvaluators = $evaluators->where('eva_role', 1)->values();

            foreach ($signatureFields as $signatureField) {
                $signatureKey = $signatureField->ff_signature_key;
                $dateKey = $signatureField->ff_signature_date_key;

                if (!isset($signatureData[$signatureKey]) || empty($signatureData[$signatureKey])) {
                    continue;
                }

                $role = null;
                $signerName = null;

                if ($signatureField->ff_signature_role == 1) {
                    $role = 'Student';
                    $signerName = $student->student_name;
                } else {

                    // === Chairman Mode (mass signing mode)
                    if ($mode == 6) {

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
                    }

                    // === Examiner/Panel Individual Mode
                    elseif ($mode == 5) {

                        $currentStaff = auth()->user();

                        // Check if this staff assigned as evaluator
                        $matchedEvaluator = $evaluators->first(function ($eva) use ($currentStaff) {
                            return $eva->staff_id == $currentStaff->id;
                        });

                        if (!$matchedEvaluator) {
                            continue; // not assigned → skip
                        }

                        // Allow chairman also to sign in his own form part
                        if (str_contains(strtolower($signatureKey), 'chair') && $matchedEvaluator->eva_role == 2) {
                            $role = $signatureField->ff_label;
                            $signerName = $matchedEvaluator->staff->staff_name;
                        }
                        // For examiner/panel fields
                        elseif (preg_match('/(examiner|panel|reviewer|evaluator|assessor)/i', $signatureKey)) {
                            if ($matchedEvaluator->eva_role == 1) {
                                $role = $signatureField->ff_label;
                                $signerName = $matchedEvaluator->staff->staff_name;
                            } else {
                                continue;
                            }
                        } else {
                            continue;
                        }
                    }

                    // Other unknown mode → ignore
                    else {
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
        } catch (Exception $e) {
            throw new Exception('Signature storage error: ' . $e->getMessage());
        }
    }

    /* Generate Evaluation Document */
    public function generateEvaluationForm($actID, $student, $semesterId, $form, $mode, $finalDocRelativePath, $fileName)
    {
        try {

            $staffId = auth()->user()->id;

            /* GET ACTIVITY FORM DATA */
            $act = Activity::where('id', $actID)->first();

            if (!$act) {
                return back()->with('error', 'Activity not found.');
            }

            /* FETCH - FORM FIELD */
            $formfields = FormField::where('af_id', $form->id)
                ->orderBy('ff_order')
                ->get();

            /* GET FACULTY DATA */
            $faculty = Faculty::where('fac_status', 3)->first();

            if (!$faculty) {
                return back()->with('error', 'Faculty not found.');
            }

            /* FETCH - SIGNATURE */
            $signatures = $formfields->where('ff_category', 6);

            /* FETCH - EVALUATION */
            $evaluationRecord = Evaluation::where([
                ['activity_id', $actID],
                ['student_id', $student->id],
                ['staff_id', $staffId],
                ['semester_id', $semesterId],
            ])->first();

            $signatureData = $evaluationRecord ? json_decode($evaluationRecord->evaluation_signature_data) : null;

            /* MAPPING PROCESS - SUBSTITUTE DATA */
            $userData = [];

            $specialMappings = [
                'prog_mode' => [
                    'FT' => 'Full-Time',
                    'PT' => 'Part-Time',
                ],
            ];

            $joinMap = [
                'students' => [
                    'programmes' => [
                        'alias' => 'b',
                        'table' => 'programmes',
                        'on' => ['a.programme_id', '=', 'b.id'],
                    ],
                    'semesters' => [
                        'alias' => 'c',
                        'table' => 'semesters',
                        'on' => ['a.semester_id', '=', 'c.id'],
                    ],
                ],
                'submissions' => [
                    'documents' => [
                        'alias' => 'b',
                        'table' => 'documents',
                        'on' => ['a.document_id', '=', 'b.id'],
                    ],
                ],
                'documents' => [
                    'submissions' => [
                        'alias' => 'b',
                        'table' => 'submissions',
                        'on' => ['a.id', '=', 'b.document_id'],
                    ],
                ],
                'staff' => [
                    'supervisions' => [
                        'alias' => 'b',
                        'table' => 'supervisions',
                        'on' => ['a.id', '=', 'b.staff_id'],
                    ],
                ],
            ];

            foreach ($formfields as $field) {
                $baseTable = $field->ff_table;
                $key = $field->ff_datakey;

                if (empty($baseTable) || empty($key)) {
                    $userData[str_replace(' ', '_', strtolower($field->ff_label))] = '-';
                    continue;
                }

                $extraKey = $field->ff_extra_datakey;
                $extraCondition = $field->ff_extra_condition;

                $query = DB::table($baseTable . ' as a');

                preg_match_all('/\w+/', $key, $matches);
                $keys = $matches[0];
                $fullKeys = [];
                $joinedAliases = [];

                foreach ($keys as $column) {
                    $fullCol = 'a.' . $column;

                    if (isset($joinMap[$baseTable])) {
                        foreach ($joinMap[$baseTable] as $joinName => $joinData) {
                            $columns = Schema::getColumnListing($joinData['table']);
                            if (in_array($column, $columns)) {
                                if (!in_array($joinData['alias'], $joinedAliases)) {
                                    $query->join($joinData['table'] . ' as ' . $joinData['alias'], ...$joinData['on']);
                                    $joinedAliases[] = $joinData['alias'];
                                }
                                $fullCol = $joinData['alias'] . '.' . $column;
                                break;
                            }
                        }
                    }

                    $fullKeys[$column] = $fullCol;
                }

                if ($baseTable === 'students') {
                    $query->where('a.id', $student->id);
                }

                if ($baseTable === 'semesters') {
                    $query->where('a.sem_status', 1);
                }

                if ($baseTable === 'submissions') {
                    if (!in_array('b', $joinedAliases)) {
                        $joinData = $joinMap['submissions']['documents'];
                        $query->join($joinData['table'] . ' as ' . $joinData['alias'], ...$joinData['on']);
                        $joinedAliases[] = 'b';
                    }
                    $query->where('a.student_id', $student->id)
                        ->where('a.submission_status', 3)
                        ->where('b.activity_id', $act->id);
                }

                if ($baseTable === 'documents') {
                    if (!in_array('b', $joinedAliases)) {
                        $joinData = $joinMap['documents']['submissions'];
                        $query->join($joinData['table'] . ' as ' . $joinData['alias'], ...$joinData['on']);
                        $joinedAliases[] = 'b';
                    }
                    $query->where('b.student_id', $student->id)
                        ->where('b.submission_status', 3)
                        ->where('a.activity_id', $act->id)
                        ->where('a.isShowDoc', 1);
                }

                if ($baseTable === 'staff') {
                    if (!in_array('b', $joinedAliases)) {
                        $joinData = $joinMap['staff']['supervisions'];
                        $query->join($joinData['table'] . ' as ' . $joinData['alias'], ...$joinData['on']);
                        $joinedAliases[] = 'b';
                    }
                    $query->where('b.student_id', $student->id);
                }

                if (!empty($extraKey) && !empty($extraCondition)) {
                    $query->where($extraKey, $extraCondition);
                }

                $results = $query->get(array_values($fullKeys));

                $finalValue = '-';

                if (!$results->isEmpty()) {
                    $tempLines = [];

                    foreach ($results as $row) {
                        $tempParts = [];

                        foreach ($fullKeys as $col => $_alias) {
                            $val = $row->$col ?? '';

                            // Apply special value mapping if available
                            if (isset($specialMappings[$col]) && isset($specialMappings[$col][$val])) {
                                $val = $specialMappings[$col][$val];
                            }

                            // Format as date if valid
                            if ($val && strtotime($val)) {
                                $carbonDate = Carbon::parse($val);
                                $val = $carbonDate->format('j F Y g:ia');
                            }

                            $tempParts[] = $val;
                        }

                        $tempLines[] = implode(' : ', $tempParts);
                    }

                    $finalValue = implode("<br>", $tempLines);
                }

                $userData[str_replace(' ', '_', strtolower($field->ff_label))] = $finalValue ?: '-';
            }

            /* FETCH [EVALUATION] - EXTRA META DATA */
            if ($evaluationRecord && $evaluationRecord->evaluation_meta_data) {
                $extraData = json_decode($evaluationRecord->evaluation_meta_data, true);
                if (is_array($extraData)) {
                    foreach ($extraData as $key => $value) {
                        $normalizedKey = str_replace(' ', '_', strtolower($key));
                        $userData[$normalizedKey] = $value ?? '-';
                    }
                }
            }

            $pdf = Pdf::loadView('staff.sop.template.input-document', [
                'title' => $act->act_name . " Document",
                'act' => $act,
                'form_title' => $form->af_title,
                'formfields' => $formfields,
                'userData' => $userData,
                'faculty' => $faculty,
                'signatures' => $signatures,
                'signatureData' => $signatureData,
                'mode' => $mode
            ]);

            /* RETURN PATH */
            $path = "app/public/{$finalDocRelativePath}/{$fileName}";
            $pdf->save(storage_path($path));
            return $path;
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error generating evaluation form: ' . $e->getMessage());
        }
    }
}
