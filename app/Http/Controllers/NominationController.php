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
use App\Models\Programme;
use App\Models\Evaluation;
use App\Models\Nomination;
use App\Models\ActivityForm;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class NominationController extends Controller
{

    /* Committee - Nomination */
    public function committeeNomination(Request $req, $name)
    {
        if (auth()->user()->staff_role != 1) {
            return abort(401, 'Unauthorized');
        }

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
                    'n.semester_id',
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
                ->where('s.student_status', '=', 1)
                ->where('a.id', '=', $id)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('supervisions as e')
                        ->whereColumn('e.student_id', 's.id')
                        ->where('e.staff_id', auth()->user()->id);
                })
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
                    $currsemester = Semester::find($row->semester_id);
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
                    $semesters = Semester::where('id', $row->semester_id)->first();

                    if (empty($semesters)) {
                        return 'N/A';
                    }

                    return $semesters->sem_label;
                });

                $table->addColumn('action', function ($row) {
                    $button = '';

                    if ($row->nom_status == 2 || $row->nom_status == 5) {
                        $button = '
                            <a href="' . route('nomination-student', ['studentId' => Crypt::encrypt($row->student_id), 'actId' => Crypt::encrypt($row->activity_id), 'semesterId' => Crypt::encrypt($row->semester_id), 'mode', 'mode' => 2]) . '" class="avtar avtar-xs btn-light-primary">
                                <i class="ti ti-user-plus f-20"></i>
                            </a>
                        ';
                    } elseif ($row->nom_status == 4) {
                        $button = '
                            <button href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#updateNominationModal-' . $row->nomination_id . '-' . $row->semester_id . '" class="btn btn-sm btn-light-warning">
                                <small>Update / Re-nominate</small>
                            </button>
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

            // dd($data->get());
            return view('staff.nomination.committee-nomination-management', [
                'title' => 'Committee Nomination Management',
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

    /* Deputy Dean - Nomination */
    public function deputydeanNomination(Request $req, $name)
    {

        if (auth()->user()->staff_role != 3) {
            return abort(401, 'Unauthorized');
        }

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
                    'n.semester_id',
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
                ->where('s.student_status', '=', 1)
                ->where('a.id', '=', $id)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('supervisions as e')
                        ->whereColumn('e.student_id', 's.id')
                        ->where('e.staff_id', auth()->user()->id);
                })
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
                    $currsemester = Semester::find($row->semester_id);
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
                    $semesters = Semester::where('id', $row->semester_id)->first();

                    if (empty($semesters)) {
                        return 'N/A';
                    }

                    return $semesters->sem_label;
                });

                $table->addColumn('action', function ($row) {
                    $button = '';

                    if ($row->nom_status == 3) {
                        $button = '
                            <a href="' . route('nomination-student', ['studentId' => Crypt::encrypt($row->student_id), 'actId' => Crypt::encrypt($row->activity_id), 'semesterId' => Crypt::encrypt($row->semester_id), 'mode' => 3]) . '" class="avtar avtar-xs btn-light-primary">
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

            return view('staff.nomination.deputydean-nomination-management', [
                'title' => 'Deputy Dean - Nomination Management',
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

    /* Dean - Nomination */
    public function deanNomination(Request $req, $name)
    {

        if (auth()->user()->staff_role != 4) {
            return abort(401, 'Unauthorized');
        }

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
                    'n.semester_id',
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
                ->where('s.student_status', '=', 1)
                ->where('a.id', '=', $id)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('supervisions as e')
                        ->whereColumn('e.student_id', 's.id')
                        ->where('e.staff_id', auth()->user()->id);
                })
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
                    $currsemester = Semester::find($row->semester_id);
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
                    $semesters = Semester::where('id', $row->semester_id)->first();

                    if (empty($semesters)) {
                        return 'N/A';
                    }

                    return $semesters->sem_label;
                });

                $table->addColumn('action', function ($row) {
                    $button = '';

                    if ($row->nom_status == 3) {
                        $button = '
                            <a href="' . route('nomination-student', ['studentId' => Crypt::encrypt($row->student_id), 'actId' => Crypt::encrypt($row->activity_id), 'semesterId' => Crypt::encrypt($row->semester_id), 'mode' => 4]) . '" class="avtar avtar-xs btn-light-primary">
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

            return view('staff.nomination.dean-nomination-management', [
                'title' => 'Dean - Nomination Management',
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

    /* Nomination Student */
    public function nominationStudent($studentId, $actId, $semesterId, $mode)
    {
        try {

            /* GET ID'S */
            $studentId = decrypt($studentId);
            $actId = decrypt($actId);
            $semesterId = decrypt($semesterId);

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
            $actForm = ActivityForm::where('activity_id', $actId)
                ->where('af_target', 3)
                ->first();

            if (!$actForm) {
                return back()->with('error', 'Oops! Form for this activity were not found. Please add the form first at the Form Setting page.');
            }

            /* LINK ASSIGNMENT */
            $page = '';
            $link = '';

            if ($mode == 1) {
                $page = 'My Supervision';
                $link =  route('my-supervision-nomination', strtolower(str_replace(' ', '-', $act->act_name)));
            } else if ($mode == 2) {
                $page = 'Committee';
                $link =  route('committee-nomination', strtolower(str_replace(' ', '-', $act->act_name)));
            } else if ($mode == 3) {
                $page = 'Deputy Dean';
                $link =  route('deputydean-nomination', strtolower(str_replace(' ', '-', $act->act_name)));
            } else if ($mode == 4) {
                $page = 'Dean';
                $link =  route('dean-nomination', strtolower(str_replace(' ', '-', $act->act_name)));
            }

            return view('staff.nomination.nomination-student', [
                'title' => $data->student_name . 'Nomination',
                'act' => $act,
                'actform' => $actForm,
                'data' => $data,
                'mode' => $mode,
                'page' => $page,
                'link' => $link,
                'semid' => $semesterId
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* View Nomination Form */
    public function viewNominationForm(Request $req)
    {
        try {

            $mode = $req->input('mode');

            $semesterId = $req->input('semesterid');

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

            /* FETCH - NOMINATION */
            $nominationRecord = Nomination::where([
                ['activity_id', $actID],
                ['student_id', $student->id],
                ['semester_id', $semesterId]
            ])->first();

            $signatureData = $nominationRecord ? json_decode($nominationRecord->nom_signature_data) : null;

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

            /* FETCH [NOMINATION] - CHAIR / EXAMINER / PANEL MEMBERS */
            if ($nominationRecord) {
                $evaluators = Evaluator::where('nom_id', $nominationRecord->id)
                    ->join('staff', 'staff.id', '=', 'evaluators.staff_id')
                    ->select('evaluators.*', 'staff.staff_name', 'evaluators.eva_meta')
                    ->get();

                foreach ($evaluators as $evaluator) {
                    $meta = json_decode($evaluator->eva_meta, true);
                    $fieldLabel = $meta['field_label'] ?? null;

                    if ($fieldLabel) {
                        $key = str_replace(' ', '_', strtolower($fieldLabel));

                        // Find the corresponding form field
                        $field = $formfields->firstWhere('ff_label', $fieldLabel);

                        if ($field) {
                            if ($field->ff_component_type === 'checkbox') {
                                // For checkboxes - append to existing values
                                $existing = isset($userData[$key]) ? (array)$userData[$key] : [];
                                $existing[] = $evaluator->staff_name;
                                $userData[$key] = implode(', ', $existing);
                            } else {
                                // For other field types - overwrite with staff name
                                $userData[$key] = $evaluator->staff_name;
                            }
                        }
                    }
                }
            }

            /* FETCH [NOMINATION] - EXTRA META DATA */
            if ($nominationRecord && $nominationRecord->nom_extra_data) {
                $extraData = json_decode($nominationRecord->nom_extra_data, true);

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

    ## SEND EMAIL - SV
    /* Submit Nomination Form */
    public function submitNomination(Request $req, $studentId, $mode)
    {
        try {

            $option = $req->input('opt');
            $studentId = decrypt($studentId);
            $semesterId = $req->input('semester_id');

            /* GET STUDENT DATA */
            $student = Student::where('id', $studentId)->first();

            if (!$student) {
                return back()->with('error', 'Oops! Student not found');
            }

            /* GET ACTIVITY DATA */
            $actID = $req->input('activity_id');
            $activity = Activity::where('id', $actID)->first()->act_name;

            if (!$activity) {
                return back()->with('error', 'Oops! Activity not found');
            }

            /* GET ACTIVITY FORM DATA */
            $form = ActivityForm::where('activity_id', $actID)->where('af_target', 3)->first();

            if (!$form) {
                return back()->with('error', 'Oops! Form not found');
            }

            /* GET NOMINATION DATA */
            $nomination = Nomination::where('student_id', $studentId)
                ->where('activity_id', $actID)
                ->where('semester_id', $semesterId)
                ->first();

            if (!$nomination) {
                return back()->with('error', 'Oops! Nomination not found');
            }

            $currsemester = Semester::where('id', $semesterId)->first();

            if (!$currsemester) {
                return back()->with('error', 'Oops! Current semester not found');
            }

            /* GET PROCDURE DATA */
            $procedure = DB::table('procedures as a')
                ->where('a.programme_id', $student->programme_id)
                ->where('a.activity_id',  $actID)
                ->first();

            if ($option == 1) {
                /* GET SIGNATURE DATA */
                $formSignatureFields = FormField::where('af_id', $form->id)
                    ->where('ff_category', 6)
                    ->pluck('ff_signature_key')
                    ->toArray();

                /* PROCESS EVALUATOR DATA */
                $evaluatorFields = $this->getEvaluatorFields($form);
                $this->processEvaluators($req, $evaluatorFields, $nomination, $formSignatureFields, $mode);

                /* PROCESS SIGNATURE DATA */
                $signatureData = $req->input('signatureData', []);
                if (!empty($signatureData)) {
                    if ($mode == 1) {
                        $this->storeNominationSignature($student, $form, $signatureData, $nomination, 2, auth()->user());
                    } else if ($mode == 2) {
                        $this->storeNominationSignature($student, $form, $signatureData, $nomination, 4, auth()->user());
                    } else if ($mode == 3) {
                        $this->storeNominationSignature($student, $form, $signatureData, $nomination, 5, auth()->user());
                    } else if ($mode == 4) {
                        $this->storeNominationSignature($student, $form, $signatureData, $nomination, 6, auth()->user());
                    }
                }

                /* STORE UNHANDLED FIELDS IN nom_extra_data */
                $unhandledFields = $this->getUnhandledFields($req, $form);
                if (!empty($unhandledFields)) {
                    $existingExtraData = $nomination->nom_extra_data
                        ? json_decode($nomination->nom_extra_data, true)
                        : [];

                    $nomination->nom_extra_data = json_encode(array_merge(
                        $existingExtraData,
                        $unhandledFields
                    ));
                }

                /* UPDATE NOMINATION DATA */
                if ($mode == 1) {
                    $nomination->nom_status = 2;
                } else if ($mode == 2) {
                    if (in_array('deputy_dean_signature', $formSignatureFields) || in_array('dean_signature', $formSignatureFields)) {
                        $nomination->nom_status = 3;
                    } else {
                        $nomination->nom_status = 4;

                        $evaluator = Evaluator::where('nom_id', $nomination->id)->where('eva_status', 3)->get();
                        foreach ($evaluator as $eva) {
                            if ($procedure->evaluation_mode == 1) {
                                $evaluation = new Evaluation();
                                $evaluation->student_id = $studentId;
                                $evaluation->staff_id = $eva->staff_id;
                                $evaluation->activity_id = $actID;
                                $evaluation->semester_id = $currsemester->id;
                                $evaluation->evaluation_status = 1;
                                $evaluation->save();
                            } elseif ($procedure->evaluation_mode == 2 && $eva->eva_role == 1) {

                                $evaluation = new Evaluation();
                                $evaluation->student_id = $studentId;
                                $evaluation->staff_id = $eva->staff_id;
                                $evaluation->activity_id = $actID;
                                $evaluation->semester_id = $currsemester->id;
                                $evaluation->evaluation_status = 1;
                                $evaluation->save();
                            }
                        }
                    }
                } else if ($mode == 3 || $mode == 4) {
                    $nomination->nom_status = 4;
                    $evaluator = Evaluator::where('nom_id', $nomination->id)->where('eva_status', 3)->get();
                    foreach ($evaluator as $eva) {
                        if ($procedure->evaluation_mode == 1) {
                            $evaluation = new Evaluation();
                            $evaluation->student_id = $studentId;
                            $evaluation->staff_id = $eva->staff_id;
                            $evaluation->activity_id = $actID;
                            $evaluation->semester_id = $currsemester->id;
                            $evaluation->evaluation_status = 1;
                            $evaluation->save();
                        } elseif ($procedure->evaluation_mode == 2 && $eva->eva_role == 1) {

                            $evaluation = new Evaluation();
                            $evaluation->student_id = $studentId;
                            $evaluation->staff_id = $eva->staff_id;
                            $evaluation->activity_id = $actID;
                            $evaluation->semester_id = $currsemester->id;
                            $evaluation->evaluation_status = 1;
                            $evaluation->save();
                        }
                    }
                } else {
                    $nomination->nom_status = 1;
                }

                $fileName = 'Nomination_Form_' . $student->student_matricno . '.pdf';
                $nomination->nom_document = $fileName;
                $nomination->nom_date = Carbon::now();
                $nomination->save();

                /* GENERATE NOMINATION FORM */
                $progcode = strtoupper($student->programmes->prog_code);

                // SEMESTER LABEL
                $rawLabel = $currsemester->sem_label;
                $semesterlabel = str_replace('/', '', $rawLabel);
                $semesterlabel = trim($semesterlabel);

                $relativeDir = "{$student->student_directory}/{$progcode}/{$activity}/Nomination/{$semesterlabel}";
                $fullPath = storage_path("app/public/{$relativeDir}");

                if (!File::exists($fullPath)) {
                    File::makeDirectory($fullPath, 0755, true);
                }

                $this->generateNominationForm($actID, $student, $semesterId, $form, $mode, $relativeDir, $fileName);


                if ($mode == 1) {
                    return redirect()->route('my-supervision-nomination', strtolower(str_replace(' ', '-',  $activity)))->with('success', 'Nomination submitted successfully!');
                } else if ($mode == 2) {
                    return redirect()->route('committee-nomination', strtolower(str_replace(' ', '-',  $activity)))->with('success', 'Nomination submitted successfully!');
                } else if ($mode == 3) {
                    return redirect()->route('deputydean-nomination', strtolower(str_replace(' ', '-',  $activity)))->with('success', 'Nomination approved successfully!');
                } else if ($mode == 4) {
                    return redirect()->route('dean-nomination', strtolower(str_replace(' ', '-',  $activity)))->with('success', 'Nomination approved successfully!');
                } else {
                    return back()->with('error', 'Oops! Error submitting nomination');
                }
            } elseif ($option == 2) {
                $nomination->nom_status = 5;
                $nomination->save();
                if ($mode == 3) {
                    return redirect()->route('deputydean-nomination', strtolower(str_replace(' ', '-',  $activity)))->with('success', 'Nomination rejected successfully!');
                } else if ($mode == 4) {
                    return redirect()->route('dean-nomination', strtolower(str_replace(' ', '-',  $activity)))->with('success', 'Nomination rejected successfully!');
                } else {
                    return back()->with('error', 'Oops! Error submitting nomination');
                }
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error submitting nomination: ' . $e->getMessage() . ' ' . $e->getLine());
        }
    }

    /* Get Evaluator Fields */
    protected function getEvaluatorFields($form)
    {
        $field = FormField::where('af_id', $form->id)
            ->where('ff_category', 1)
            ->where(function ($query) {
                $query->where('ff_label', 'like', '%examiner%')
                    ->orWhere('ff_label', 'like', '%panel%')
                    ->orWhere('ff_label', 'like', '%chair%');
            })
            ->get();

        return $field;
    }

    /* Process Evaluator - Fuzzy Match */
    protected function processEvaluators($req, $evaluatorFields, $nomination, $formSignatureFields, $mode)
    {
        /* DELETE EXISTING NOMINATION [IF ANY] */
        if ($mode == 1) {
            Evaluator::where('nom_id', $nomination->id)->where('eva_status', 1)->delete();
            $status = 1;
        } else if ($mode == 2) {
            if (in_array('deputy_dean_signature', $formSignatureFields) || in_array('dean_signature', $formSignatureFields)) {
                Evaluator::where('nom_id', $nomination->id)->where('eva_status', 2)->delete();
                $status = 2;
            } else {
                Evaluator::where('nom_id', $nomination->id)->where('eva_status', 2)->update(['eva_status' => 3]);
                $status = 3;
            }
        } else if ($mode == 3 || $mode == 4) {
            Evaluator::where('nom_id', $nomination->id)->where('eva_status', 2)->update(['eva_status' => 3]);
            $status = 3;
        }

        foreach ($evaluatorFields as $field) {

            /* GET STAFF NAME */
            $fieldKey = str_replace(' ', '_', strtolower($field->ff_label));
            $staffName = $req->input($fieldKey);

            if (!$staffName) continue;

            /* DETERMINE EVALUATOR ROLE */
            $role = $this->determineEvaluatorRole($field->ff_label);

            /* FIND STAFF USING FUZZY MATCH */
            $staff = $this->findStaffByName($staffName);

            if ($staff) {
                Evaluator::create([
                    'eva_role' => $role,
                    'eva_status' => $status,
                    'staff_id' => $staff->id,
                    'nom_id' => $nomination->id,
                    'eva_meta' => json_encode([
                        'field_id' => $field->id,
                        'field_label' => $field->ff_label,
                        'input_value' => $staffName
                    ])
                ]);
            }
        }
    }

    /* Process Extra Meta Data */
    protected function getUnhandledFields(Request $request, $form)
    {
        /* IDENTIFY EVALUATOR FIELDS TO HANDLED KEYS */
        $handledKeys = [
            '_token',
            'activity_id',
            'semester_id',
            'opt',
            'signatureData'
        ];

        $evaluatorFields = $this->getEvaluatorFields($form);
        foreach ($evaluatorFields as $field) {
            $handledKeys[] = str_replace(' ', '_', strtolower($field->ff_label));
        }

        /* ADD SIGNATURE FIELDS TO HANDLED KEYS */
        $signatureFields = FormField::where('af_id', $form->id)
            ->where('ff_category', 6)
            ->pluck('ff_signature_key')
            ->toArray();

        $handledKeys = array_merge($handledKeys, $signatureFields);

        /* GET ALL REQUEST KEYS */
        $allKeys = array_keys($request->all());

        return collect($request->all())
            ->reject(function ($value, $key) use ($handledKeys) {
                return in_array($key, $handledKeys);
            })
            ->toArray();
    }

    /* Handle Nomination Signature */
    public function storeNominationSignature($student, $form, $signatureData, $nomination, $signatureRole, $userData)
    {
        try {
            if ($signatureData && is_array($signatureData)) {

                /* CHECK IF SIGNATURE DATA EXISTS */
                $signatureField = FormField::where([
                    ['af_id', $form->id],
                    ['ff_category', 6],
                    ['ff_signature_role', $signatureRole]
                ])->first();

                $existingSignatureData = [];
                if ($nomination->nom_signature_data) {
                    $existingSignatureData = json_decode($nomination->nom_signature_data, true);
                }

                $isCrossApproval = false;

                /* CROSS APPROVAL LOGIC */
                if (!$signatureField) {
                    $allSignatureFields = FormField::where([
                        ['af_id', $form->id],
                        ['ff_category', 6],
                    ])->get();

                    foreach ($allSignatureFields as $field) {
                        $key = $field->ff_signature_key;

                        if (in_array($field->ff_signature_role, [2, 3]) && empty($existingSignatureData[$key])) {
                            $signatureField = $field;
                            $isCrossApproval = true;
                        }
                    }
                }

                /* STORE SIGNATURE LOGIC */
                if ($signatureField) {
                    $signatureKey = $signatureField->ff_signature_key;
                    $dateKey = $signatureField->ff_signature_date_key;

                    $signatureString = $signatureData[$signatureKey] ?? null;

                    if ($signatureString) {
                        if ($signatureRole == 1) {
                            $newSignatureData = [
                                $signatureKey => $signatureString,
                                $dateKey => now()->format('d M Y'),
                                $signatureKey . '_name' => $student->student_name,
                                $signatureKey . '_role' => 'Student',
                                $signatureKey . '_is_cross_approval' => $isCrossApproval
                            ];
                        } else {
                            $role = match ($userData->staff_role) {
                                1 => "Committee",
                                2 => "Lecturer",
                                3 => "Deputy Dean",
                                4 => "Dean",
                                default => "N/A",
                            };

                            $newSignatureData = [
                                $signatureKey => $signatureString,
                                $dateKey => now()->format('d M Y'),
                                $signatureKey . '_name' => $userData->staff_name,
                                $signatureKey . '_role' => $role,
                                $signatureKey . '_is_cross_approval' => $isCrossApproval
                            ];
                        }

                        /* MERGE & SAVE SIGNATURE */
                        $mergedSignatureData = array_merge($existingSignatureData, $newSignatureData);
                        $nomination->nom_signature_data = json_encode($mergedSignatureData);
                        $nomination->save();
                    }
                }
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error storing signature: ' . $e->getMessage());
        }
    }

    /* Generate Nomination Document */
    public function generateNominationForm($actID, $student, $semesterId, $form, $mode, $finalDocRelativePath, $fileName)
    {
        try {

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

            /* FETCH - NOMINATION */
            $nominationRecord = Nomination::where([
                ['activity_id', $actID],
                ['student_id', $student->id],
                ['semester_id', $semesterId],
            ])->first();

            $signatureData = $nominationRecord ? json_decode($nominationRecord->nom_signature_data) : null;

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

            /* FETCH [NOMINATION] - CHAIR / EXAMINER / PANEL MEMBERS */
            if ($nominationRecord) {
                $evaluators = Evaluator::where('nom_id', $nominationRecord->id)
                    ->join('staff', 'staff.id', '=', 'evaluators.staff_id')
                    ->select('evaluators.*', 'staff.staff_name', 'evaluators.eva_meta')
                    ->get();

                foreach ($evaluators as $evaluator) {
                    $meta = json_decode($evaluator->eva_meta, true);
                    $fieldLabel = $meta['field_label'] ?? null;

                    if ($fieldLabel) {
                        $key = str_replace(' ', '_', strtolower($fieldLabel));

                        // Find the corresponding form field
                        $field = $formfields->firstWhere('ff_label', $fieldLabel);

                        if ($field) {
                            if ($field->ff_component_type === 'checkbox') {
                                // For checkboxes - append to existing values
                                $existing = isset($userData[$key]) ? (array)$userData[$key] : [];
                                $existing[] = $evaluator->staff_name;
                                $userData[$key] = implode(', ', $existing);
                            } else {
                                // For other field types - overwrite with staff name
                                $userData[$key] = $evaluator->staff_name;
                            }
                        }
                    }
                }
            }

            /* FETCH [NOMINATION] - EXTRA META DATA */
            if ($nominationRecord && $nominationRecord->nom_extra_data) {
                $extraData = json_decode($nominationRecord->nom_extra_data, true);

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
            return back()->with('error', 'Oops! Error generating nomination form: ' . $e->getMessage());
        }
    }

    /* Determine Evaluator Role by keywords */
    protected function determineEvaluatorRole($fieldLabel)
    {
        $fieldLabel = strtolower($fieldLabel);

        if (str_contains($fieldLabel, 'examiner') || str_contains($fieldLabel, 'panel')) {
            return 1; // Examiner 
        } elseif (str_contains($fieldLabel, 'chair')) {
            return 2; // Chairman
        }

        return 1;
    }

    /* Find Staff - Fuzzy Match */
    protected function findStaffByName($name)
    {
        /* HANDLE MULTIPLE NAMES */
        if (is_array($name)) {
            $name = $name[0] ?? '';
        }

        /* CLEAN NAME */
        $cleanName = preg_replace('/^(Prof|Prof\.|Dr|Dr\.|Mr|Mr\.|Ms|Ms\.|Mrs|Mrs\.)\s*/i', '', $name);
        $cleanName = trim(preg_replace('/\s+/', ' ', $cleanName));

        /* SPLIT NAME INTO FIRST NAME AND LAST NAME */
        $nameParts = explode(' ', $cleanName);
        $firstName = array_shift($nameParts);
        $lastName = implode(' ', $nameParts);

        /* FIND STAFF USING FUZZY MATCH */
        $staff = Staff::where('staff_name', 'LIKE', "%$cleanName%")
            ->orWhere(function ($query) use ($firstName, $lastName) {
                $query->where('staff_name', 'LIKE', "%$firstName%")
                    ->where('staff_name', 'LIKE', "%$lastName%");
            })
            ->first();

        return $staff;
    }

    public function reNominatedStudent($nominationId)
    {

        $nomId = Crypt::decrypt($nominationId);

        try {
            $nomination = Nomination::find($nomId);

            if (!$nomination) {
                return back()->with('error', 'Nomination not found.');
            }

            $currsemester = Semester::where('sem_status', 1)->first();

            $existNom = Nomination::where('student_id', $nomination->student_id)
                ->where('semester_id', $currsemester->id)
                ->where('activity_id', $nomination->activity_id)
                ->exists();

            if ($existNom) {
                return back()->with('error', 'Updating the nomination for this student is not allowed for the current semester. Please try again in the next semester.');
            }



            // UPDATE CURRENT EVALUATOR STATUS
            Evaluator::where('nom_id', $nomId)
                ->where('eva_status', 3)
                ->update(['eva_status' => 2]);

            // COPY ALL CURRENT DATA FROM NOMINATION
            $newnomination = new Nomination();
            $newnomination->student_id = $nomination->student_id;
            $newnomination->semester_id = $currsemester->id;
            $newnomination->activity_id = $nomination->activity_id;

            // REMOVE HIGHER UPS [COMMITTEE / DEPUTY DEAN / DEAN] SIGNATURES
            $originalSignatures = json_decode($nomination->nom_signature_data, true);
            $filteredSignatures = array_filter(
                $originalSignatures,
                fn($key) => str_starts_with($key, 'sv_signature'),
                ARRAY_FILTER_USE_KEY
            );

            $newnomination->nom_signature_data = json_encode($filteredSignatures);
            $newnomination->nom_extra_data = $nomination->nom_extra_data;
            $newnomination->nom_status = 2;
            $newnomination->save();

            // COPYING ALL CURRENT EVALUATOR DETAILS
            $evaluators = Evaluator::where('nom_id', $nomId)->get();
            foreach ($evaluators as $evaluator) {
                $newevaluator = new Evaluator();
                $newevaluator->nom_id = $newnomination->id;
                $newevaluator->staff_id = $evaluator->staff_id;
                $newevaluator->eva_status = $evaluator->eva_status;
                $newevaluator->eva_role = $evaluator->eva_role;
                $newevaluator->eva_meta = $evaluator->eva_meta;
                $newevaluator->save();
            }

            return redirect()->route('nomination-student', ['studentId' => Crypt::encrypt($nomination->student_id), 'actId' => Crypt::encrypt($nomination->activity_id), 'semesterId' => Crypt::encrypt($currsemester->id), 'mode' => 2])->with('success', 'Your update request has been created successfully. Please complete the nomination process.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error making request for nomination update: ' . $e->getMessage());
        }
    }
}
