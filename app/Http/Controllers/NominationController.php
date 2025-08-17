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
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\FormHandlerController;

class NominationController extends Controller
{

    /* Nomination Final Overview [Staff] - Route | Last Checked: 17-08-2023 */
    public function nominationFinalOverview(Request $req, $name)
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
                ->orderBy('s.student_matricno');

            if ($req->ajax()) {

                if ($req->has('faculty') && !empty($req->input('faculty'))) {
                    $data->where('c.fac_id', $req->input('faculty'));
                }
                if ($req->has('programme') && !empty($req->input('programme'))) {
                    $data->where('s.programme_id', $req->input('programme'));
                }
                if ($req->has('semester') && !empty($req->input('semester'))) {
                    $data->where('n.semester_id', $req->input('semester'));
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

                    /* HANDLE EMPTY FINAL DOCUMENT */
                    if (empty($row->nom_document)) {
                        return '-';
                    }

                    /* LOAD SEMESTER DATA */
                    $currsemester = Semester::where('id', $row->semester_id)->first();

                    /* FORMAT SEMESTER LABEL */
                    $rawLabel = $currsemester->sem_label;
                    $semesterlabel = str_replace('/', '', $rawLabel);
                    $semesterlabel = trim($semesterlabel);

                    /* SET DOCUMENT DIRECTORY */
                    $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Nomination/' . $semesterlabel;

                    /* HTML OUTPUT */
                    $final_doc =
                        '
                        <a href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->nom_document)]) . '" 
                            target="_blank" class="link-dark d-flex align-items-center">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                            <span class="fw-semibold">View Document</span>
                        </a>
                    ';

                    /* RETURN HTML */
                    return $final_doc;
                });

                $table->addColumn('nom_date', function ($row) {

                    /* HANDLE EMPTY DATE */
                    if (empty($row->nom_date)) {
                        return '-';
                    }

                    /* RETURN FORMATTED DATE */
                    return Carbon::parse($row->nom_date)->format('d M Y h:i A');
                });

                $table->addColumn('nom_status', function ($row) {

                    /* HANDLE NOMINATION STATUS */
                    if ($row->nom_status == 1) {
                        $status = '<span class="badge bg-light-warning">' . 'Pending' . '</span>';
                    } elseif ($row->nom_status == 2) {
                        $status = '<span class="badge bg-light-success">' . 'Nominated - SV' . '</span>';
                    } elseif ($row->nom_status == 3) {
                        $status = '<span class="badge bg-light-success">' . 'Reviewed - Committee' . '</span>';
                    } elseif ($row->nom_status == 4) {
                        $status = '<span class="badge bg-success">' . 'Approved & Active' . '</span>';
                    } elseif ($row->nom_status == 5) {
                        $status = '<span class="badge bg-light-danger">' . 'Rejected' . '</span>';
                    } elseif ($row->nom_status == 6) {
                        $status = '<span class="badge bg-secondary">' . 'Approve & Inactive' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    /* RETURN STATUS */
                    return $status;
                });

                $table->addColumn('nom_semester', function ($row) {
                    /* LOAD SEMESTER DATA */
                    $semesters = Semester::where('id', $row->semester_id)->first();

                    if (empty($semesters)) {
                        return 'N/A';
                    }

                    /* RETURN SEMESTER LABEL */
                    return $semesters->sem_label;
                });

                $table->addColumn('action', function ($row) {
                    /* BUILD DROPDOWN MENU BASE HTML */
                    $htmlOne = '
                        <div class="dropdown">
                            <a class="avtar avtar-xs btn-link-secondary dropdown-toggle arrow-none"
                                href="javascript: void(0)" data-bs-toggle="dropdown" 
                                aria-haspopup="true" aria-expanded="false">
                                <i class="material-icons-two-tone f-18">more_vert</i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                    ';

                    /* SETTING - ALWAYS AVAILABLE */
                    $htmlTwo = '          
                        <a href="javascript: void(0)" class="dropdown-item" data-bs-toggle="modal"
                            data-bs-target="#settingModal-' . $row->nomination_id . '">
                            Setting 
                        </a>
                    ';

                    /* DELETE - ONLY IF nom_status NOT IN RESTRICTED LIST */
                    $restrictedStatuses = [2, 3, 4, 5, 6];
                    if (!in_array($row->nom_status, $restrictedStatuses)) {
                        $htmlTwo .= '
                            <a href="javascript: void(0)" class="dropdown-item" data-bs-toggle="modal"
                                data-bs-target="#deleteModal-' . $row->nomination_id . '">
                                Delete
                            </a> 
                        ';
                    }

                    /* RENOMINATE - ONLY IF nom_status NOT IN RESTRICTED LIST */
                    $restrictedStatuses = [1, 2, 3, 5, 6];
                    if (!in_array($row->nom_status, $restrictedStatuses)) {
                        $htmlTwo .= '
                            <a href="javascript: void(0)" class="dropdown-item" data-bs-toggle="modal"
                                data-bs-target="#renominateModal-' . $row->nomination_id . '">
                                Update / Re-nominate
                            </a> 
                        ';
                    }

                    /* CLOSE DROPDOWN MENU TAGS */
                    $htmlThree = '
                            </div>
                        </div>
                    ';

                    /* RETURN HTML */
                    return $htmlOne . $htmlTwo . $htmlThree;
                });

                $table->rawColumns(['student_photo', 'nom_document', 'nom_date', 'nom_status', 'nom_semester', 'action']);

                return $table->make(true);
            }

            /* LOAD EVALUATOR DATA */
            $evaluator = DB::table('evaluators as a')
                ->join('staff as b', 'a.staff_id', '=', 'b.id')
                ->join('nominations as c', 'a.nom_id', '=', 'c.id')
                ->where('c.activity_id', $activity->id)
                ->where('a.eva_status', 3)
                ->get();

            /* RETURN VIEW */
            return view('staff.nomination.nomination-final-overview', [
                'title' => $activity->act_name . ' - Nomination',
                'studs' => Student::all(),
                'progs' => Programme::all(),
                'facs' => Faculty::all(),
                'sems' => Semester::all(),
                'act' => $activity,
                'nomination' => $data->get(),
                'evaluator' =>  $evaluator
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* Update Final Nomination [Staff] - Function | Last Checked: 16-08-2023 */
    public function updateFinalNomination(Request $req, $id)
    {
        /* DECRYPT ID */
        $id = decrypt($id);

        $validator = Validator::make($req->all(), [
            'nom_status_up' => 'required|integer|in:1,2,3,4,5,6',
        ], [], [
            'nom_status_up' => 'nomination status',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'settingModal-' . $id);
        }

        try {

            $validated = $validator->validated();

            /* LOAD NOMINATION DATA */
            $nomination = Nomination::where('id', $id)->first();

            if (!$nomination) {
                return back()->with('error', 'Error occurred: Nomination not found.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $nomination->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Error occurred: Student not found.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $nomination->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Error occurred: Activity not found.');
            }

            /* UPDATE NOMINATION DATA */
            $nomination->nom_status = $validated['nom_status_up'];
            $nomination->save();

            /* RETURN SUCCESS */
            return back()->with('success', $student->student_name . ' nomination for ' . $activity->act_name . ' successfully updated.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating final nomination: ' . $e->getMessage());
        }
    }

    /* Delete Final Nomination [Staff] - Function | Last Checked: 17-08-2023  */
    public function deleteFinalNomination($id)
    {
        /* DECRYPT ID */
        $id = decrypt($id);
        try {

            /* LOAD NOMINATION DATA */
            $nomination = Nomination::where('id', $id)->first();

            if (!$nomination) {
                return back()->with('error', 'Error occurred: Nomination not found.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $nomination->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Error occurred: Student not found.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $nomination->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Error occurred: Activity not found.');
            }

            /* DELETE EVALUATOR DATA */
            $deletedEvaluators = DB::table('evaluators')
                ->where('nom_id', $nomination->id)
                ->whereIn('eva_status', [1, 2, 3])
                ->delete();

            /* DELETE NOMINATION */
            if ($deletedEvaluators > 0) {
                $nomination->delete();
            }

            /* RETURN SUCCESS */
            return back()->with('success', $student->student_name . ' nomination fot ' . $activity->act_name . ' successfully deleted.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error deleting final nomination: ' . $e->getMessage());
        }
    }

    /* Nomination Management [Staff] - Route | Last Checked: 16-08-2025 */
    public function nominationApproval(Request $req, $name)
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
                    $data->where('n.semester_id', $req->input('semester'));
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

                    /* HANDLE EMPTY FINAL DOCUMENT */
                    if (empty($row->nom_document)) {
                        return '-';
                    }

                    /* LOAD SEMESTER DATA */
                    $currsemester = Semester::where('id', $row->semester_id)->first();

                    /* FORMAT SEMESTER LABEL */
                    $rawLabel = $currsemester->sem_label;
                    $semesterlabel = str_replace('/', '', $rawLabel);
                    $semesterlabel = trim($semesterlabel);

                    /* SET DOCUMENT DIRECTORY */
                    $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Nomination/' . $semesterlabel;

                    /* HTML OUTPUT */
                    $final_doc =
                        '
                        <a href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->nom_document)]) . '" 
                            target="_blank" class="link-dark d-flex align-items-center">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                            <span class="fw-semibold">View Document</span>
                        </a>
                    ';

                    /* RETURN HTML */
                    return $final_doc;
                });

                $table->addColumn('nom_date', function ($row) {

                    /* HANDLE EMPTY DATE */
                    if (empty($row->nom_date)) {
                        return '-';
                    }

                    /* RETURN FORMATTED DATE */
                    return Carbon::parse($row->nom_date)->format('d M Y h:i A');
                });

                $table->addColumn('nom_status', function ($row) {

                    /* HANDLE NOMINATION STATUS */
                    if ($row->nom_status == 1) {
                        $status = '<span class="badge bg-light-warning">' . 'Pending' . '</span>';
                    } elseif ($row->nom_status == 2) {
                        $status = '<span class="badge bg-light-success">' . 'Nominated - SV' . '</span>';
                    } elseif ($row->nom_status == 3) {
                        $status = '<span class="badge bg-light-success">' . 'Reviewed - Committee' . '</span>';
                    } elseif ($row->nom_status == 4) {
                        $status = '<span class="badge bg-success">' . 'Approved & Active' . '</span>';
                    } elseif ($row->nom_status == 5) {
                        $status = '<span class="badge bg-light-danger">' . 'Rejected' . '</span>';
                    } elseif ($row->nom_status == 6) {
                        $status = '<span class="badge bg-secondary">' . 'Approve & Inactive' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    /* RETURN STATUS */
                    return $status;
                });

                $table->addColumn('nom_semester', function ($row) {
                    /* LOAD SEMESTER DATA */
                    $semesters = Semester::where('id', $row->semester_id)->first();

                    if (empty($semesters)) {
                        return 'N/A';
                    }

                    /* RETURN SEMESTER LABEL */
                    return $semesters->sem_label;
                });

                $table->addColumn('action', function ($row) {

                    if (auth()->user()->staff_role == 1) {
                        /* HANDLE ACTION BUTTON */
                        if ($row->nom_status == 2 || $row->nom_status == 5) {
                            $button = '
                            <a href="' . route('nomination-student', ['nomID' => Crypt::encrypt($row->nomination_id), 'mode' => Crypt::encrypt(2)]) . '" class="avtar avtar-xs btn-light-primary">
                                <i class="ti ti-user-plus f-20"></i>
                            </a>
                        ';
                        } else {
                            $button = '<div class="fst-italic text-muted">No action required</div>';
                        }

                        /* RETURN HTML BUTTON */
                        return $button;
                    } elseif (auth()->user()->staff_role == 3) {
                        /* HANDLE ACTION BUTTON */
                        if ($row->nom_status == 3) {
                            $button = '
                            <a href="' . route('nomination-student', ['nomID' => Crypt::encrypt($row->nomination_id), 'mode' => Crypt::encrypt(3)]) . '" class="avtar avtar-xs btn-light-primary">
                                <i class="ti ti-user-plus f-20"></i>
                            </a>
                        ';
                        } else {
                            $button = '<div class="fst-italic text-muted">No action required</div>';
                        }

                        /* RETURN HTML BUTTON */
                        return $button;
                    } elseif (auth()->user()->staff_role == 4) {
                        /* HANDLE ACTION BUTTON */
                        if ($row->nom_status == 3) {
                            $button = '
                            <a href="' . route('nomination-student', ['nomID' => Crypt::encrypt($row->nomination_id), 'mode' => Crypt::encrypt(4)]) . '" class="avtar avtar-xs btn-light-primary">
                                <i class="ti ti-user-plus f-20"></i>
                            </a>
                        ';
                        } else {
                            $button = '<div class="fst-italic text-muted">No action required</div>';
                        }

                        /* RETURN HTML BUTTON */
                        return $button;
                    }
                });

                $table->rawColumns(['student_photo', 'nom_document', 'nom_date', 'nom_status', 'nom_semester', 'action']);

                return $table->make(true);
            }

            /* RETURN VIEW */
            return view('staff.nomination.nomination-approval', [
                'title' => $activity->act_name . ' - Nomination Approval',
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

    /* Nomination Student - Route | Last Checked : 17-08-2025 */
    public function nominationStudent($nomID, $mode)
    {
        try {
            /* DECRYPT ID'S */
            $nomID = decrypt($nomID);
            $mode = decrypt($mode);

            /* LOAD NOMINATION DATA */
            $nomination = Nomination::where('id', $nomID)->first();

            if (!$nomination) {
                return back()->with('error', 'Error loading nomination data: Nomination not found');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $nomination->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Error loading nomination data: Student not found');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $nomination->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Error loading nomination data: Activity not found');
            }

            /* LOAD SEMESTER DATA */
            $semester = Semester::where('id', $nomination->semester_id)->first();

            if (!$semester) {
                return back()->with('error', 'Error loading nomination data: Semester not found');
            }

            /* LOAD ACTIVITY FORM DATA */
            $form = ActivityForm::where('activity_id', $activity->id)
                ->where('af_target', 3)
                ->first();

            if (!$form) {
                return back()->with('error', 'Oops! Form for this activity were not found. Please add the form first at the Form Setting page.');
            }

            /* LINK ASSIGNMENT BASED ON MODE */
            if ($mode == 1) {
                $page = 'Supervisor';
                $link =  route('my-supervision-nomination', strtolower(str_replace(' ', '-', $activity->act_name)));
            } else if ($mode == 2 || $mode == 3 || $mode == 4) {
                $page = 'Administrator';
                $link =  route('nomination-approval', strtolower(str_replace(' ', '-', $activity->act_name)));
            } else {
                return back()->with('error', 'Error loading nomination data: Invalid Request. Please try again.');
            }

            /* RETURN VIEW */
            return view('staff.nomination.nomination-student', [
                'title' => $student->student_name . 'Nomination',
                'activity' => $activity,
                'student' => $student,
                'actform' => $form,
                'nomination' => $nomination,
                'mode' => $mode,
                'page' => $page,
                'link' => $link,
            ]);
        } catch (Exception $e) {
            dd($e);
            return abort(500, $e->getMessage());
        }
    }

    /* View Nomination Form (Method : Input) - Function | Last Checked : 16-08-2025 */
    public function viewNominationForm(Request $req)
    {
        try {

            /* GET THE DATA FROM REQUEST */
            $nomID = $req->input('nomID');
            $mode = $req->input('mode');

            /* LOAD NOMINATION DATA */
            $nomination = Nomination::where('id', $nomID)->first();

            if (!$nomination) {
                return back()->with('error', 'Error loading nomination form: Nomination not found.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $nomination->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Error loading nomination form: Student not found.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $nomination->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Error loading nomination form: Activity not found.');
            }

            /* LOAD ACTIVITY FORM DATA */
            $form = ActivityForm::where('activity_id', $activity->id)
                ->where('af_target', 3)
                ->first();

            if (!$form) {
                return back()->with('error', 'Error loading nomination form: Activity Form not found.');
            }

            /* LOAD FACULTY DATA */
            $faculty = Faculty::where('fac_status', 3)->first();

            if (!$faculty) {
                return back()->with('error', 'Error loading nomination form: Faculty not found.');
            }

            /* GET FORM FIELD */
            $formfields = FormField::where('af_id', $form->id)
                ->orderBy('ff_order')
                ->get();

            /* GET FORM SIGNATURE */
            $signatures = $formfields->where('ff_category', 6);

            /* GET NOMINATION SIGNATURE DATA */
            $signatureData = $nomination ? json_decode($nomination->nom_signature_data) : null;

            /* MAPPING PROCESS - SUBSTITUTE DATA */
            $userData = [];
            $fhc = new FormHandlerController();
            $userData = $fhc->joinMap($formfields, $student, $activity);

            /* FETCH [NOMINATION] - CHAIR / EXAMINER / PANEL MEMBERS */
            if ($nomination) {
                $evaluators = Evaluator::where('nom_id', $nomination->id)
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
            if ($nomination && $nomination->nom_extra_data) {
                $extraData = json_decode($nomination->nom_extra_data, true);

                if (is_array($extraData)) {
                    foreach ($extraData as $key => $value) {
                        $normalizedKey = str_replace(' ', '_', strtolower($key));
                        $userData[$normalizedKey] = $value ?? '-';
                    }
                }
            }

            /* GENERATE HTML */
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

            /* RETURN HTML */
            return response()->json(['html' => $html]);
        } catch (Exception $e) {
            dd($e);
            return back()->with('error', 'Oops! Error fetching nomination form: ' . $e->getMessage());
        }
    }

    /* Nomination Form Submission [Staff] - Function | Email : Yes With Works | Last Checked : 17-08-2025 */
    public function submitNomination(Request $req, $nomID, $mode)
    {
        try {

            /* GET REQUEST DATA */
            $option = $req->input('opt');

            /* DECRYPT IDs */
            $nomID = decrypt($nomID);
            $mode = decrypt($mode);

            /* LOAD NOMINATION DATA */
            $nomination = Nomination::where('id', $nomID)->first();

            if (!$nomination) {
                return back()->with('error', 'Error submitting nomination form: Nomination not found.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $nomination->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Error submitting nomination form: Student not found.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $nomination->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Error submitting nomination form: Activity not found.');
            }

            /* LOAD ACTIVITY FORM DATA */
            $form = ActivityForm::where('activity_id', $activity->id)
                ->where('af_target', 3)
                ->first();

            if (!$form) {
                return back()->with('error', 'Error submitting nomination form: Activity Form not found.');
            }

            /* LOAD SEMESTER DATA */
            $semester = Semester::where('id', $nomination->semester_id)->first();

            if (!$semester) {
                return back()->with('error', 'Error submitting nomination data: Semester not found');
            }

            /* LOAD PROCDURE DATA */
            $procedure = DB::table('procedures as a')
                ->where('a.programme_id', $student->programme_id)
                ->where('a.activity_id',  $activity->id)
                ->first();

            if (!$procedure) {
                return back()->with('error', 'Error submitting nomination form: Procedure not found.');
            }

            if ($option == 1) {
                /* APPROVAL LOGIC */

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

                /* UPDATE NOMINATION DATA & CREATE EVALUATOR */
                if ($mode == 1) {
                    $nomination->nom_status = 2;
                } else if ($mode == 2) {
                    if (in_array('deputy_dean_signature', $formSignatureFields) || in_array('dean_signature', $formSignatureFields)) {
                        $nomination->nom_status = 3;
                    } else {
                        $nomination->nom_status = 4;

                        $evaluator = Evaluator::where('nom_id', $nomination->id)->where('eva_status', 3)->get();
                        foreach ($evaluator as $eva) {
                            if ($procedure->evaluation_mode == 1 || ($procedure->evaluation_mode == 2 && $eva->eva_role == 1)) {
                                $evaluation = new Evaluation();
                                $evaluation->student_id = $student->id;
                                $evaluation->staff_id = $eva->staff_id;
                                $evaluation->activity_id = $activity->id;
                                $evaluation->semester_id = $semester->id;
                                $evaluation->evaluation_status = 1;
                                $evaluation->save();
                            }
                        }
                    }
                } else if ($mode == 3 || $mode == 4) {
                    $nomination->nom_status = 4;
                    $evaluator = Evaluator::where('nom_id', $nomination->id)->where('eva_status', 3)->get();
                    foreach ($evaluator as $eva) {
                        if ($procedure->evaluation_mode == 1 || ($procedure->evaluation_mode == 2 && $eva->eva_role == 1)) {
                            $evaluation = new Evaluation();
                            $evaluation->student_id = $student->id;
                            $evaluation->staff_id = $eva->staff_id;
                            $evaluation->activity_id = $activity->id;
                            $evaluation->semester_id = $semester->id;
                            $evaluation->evaluation_status = 1;
                            $evaluation->save();
                        }
                    }
                } else {
                    $nomination->nom_status = 1;
                }

                $fileName = 'Nomination_Form_' . $student->student_matricno .  '_' . time() . '.pdf';
                $nomination->nom_document = $fileName;
                $nomination->nom_date = Carbon::now();
                $nomination->save();

                /* FORMAT PROGRAMME CODE */
                $progcode = strtoupper($student->programmes->prog_code);

                /* FORMAT SEMESTER LABEL */
                $rawLabel = $semester->sem_label;
                $semesterlabel = str_replace('/', '', $rawLabel);
                $semesterlabel = trim($semesterlabel);

                /* SET & LOAD NOMINATION DIRECTORY */
                $relativeDir = "{$student->student_directory}/{$progcode}/{$activity->act_name}/Nomination/{$semesterlabel}";
                $fullPath = storage_path("app/public/{$relativeDir}");

                if (!File::exists($fullPath)) {
                    File::makeDirectory($fullPath, 0755, true, true);
                }

                /* GENERATE NOMINATION FORM */
                $this->generateNominationForm($nomination, $form, $mode, $relativeDir, $fileName);


                /* RETURN REDIRECT BASED ON MODE */
                if ($mode == 1) {
                    return redirect()->route('my-supervision-nomination', strtolower(str_replace(' ', '-',  $activity->act_name)))->with('success', 'Nomination submitted successfully!');
                } else if ($mode == 2 || $mode == 3 || $mode == 4) {
                    return redirect()->route('nomination-approval', strtolower(str_replace(' ', '-',  $activity->act_name)))->with('success', 'Nomination submitted successfully!');
                } else {
                    return back()->with('error', 'Error submitting nomination: Invalid Request. Please try again.');
                }
            } elseif ($option == 2) {
                $nomination->nom_status = 5;
                $nomination->save();
                if ($mode == 3 || $mode == 4) {
                    return redirect()->route('nomination-approval', strtolower(str_replace(' ', '-',  $activity->act_name)))->with('success', 'Nomination rejected successfully!');
                } else {
                    return back()->with('error', 'Error submitting nomination: Invalid Request. Please try again.');
                }
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error submitting nomination: ' . $e->getMessage() . ' ' . $e->getLine());
        }
    }

    /* Get Evaluator Fields - Function | Last Checked: 16-08-2025 */
    protected function getEvaluatorFields($form)
    {
        /* GET EVALUATOR FIELDS */
        $field = FormField::where('af_id', $form->id)
            ->where('ff_category', 1)
            ->where(function ($query) {
                $query->where('ff_label', 'like', '%examiner%')
                    ->orWhere('ff_label', 'like', '%panel%')
                    ->orWhere('ff_label', 'like', '%chair%');
            })
            ->get();

        /* RETURN FIELD */
        return $field;
    }

    /* Process Evaluator (Fuzzy Match) - Function | Last Checked: 17-08-2025 */
    protected function processEvaluators($req, $evaluatorFields, $nomination, $formSignatureFields, $mode)
    {
        /* DELETE EXISTING NOMINATION [IF ANY] */
        if ($mode == 1) {
            Evaluator::where('nom_id', $nomination->id)
                ->where('eva_status', 1)
                ->delete();
            $status = 1;
        } elseif ($mode == 2) {
            Evaluator::where('nom_id', $nomination->id)
                ->where('eva_status', 2)
                ->delete();

            $status = in_array('deputy_dean_signature', $formSignatureFields)
                || in_array('dean_signature', $formSignatureFields) ? 2 : 3;
        } elseif (in_array($mode, [3, 4])) {
            Evaluator::where('nom_id', $nomination->id)
                ->where('eva_status', 2)
                ->delete();
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

    /* Determine Evaluator Role By Keywords - Function | Last Checked: 16-08-2025 */
    protected function determineEvaluatorRole($fieldLabel)
    {
        /* GET FIELD LABEL IN LOWERCASE */
        $fieldLabel = strtolower($fieldLabel);

        /* RETURN DECISION BASED ON KEYWORDS */
        if (str_contains($fieldLabel, 'examiner') || str_contains($fieldLabel, 'panel')) {
            return 1; // Examiner 
        } elseif (str_contains($fieldLabel, 'chair')) {
            return 2; // Chairman
        }

        /* RETURN DEFAULT */
        return 1;
    }

    /* Find Staff (Fuzzy Match) - Function | Last Checked: 16-08-2025 */
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

        /* RETURN STAFF */
        return $staff;
    }

    /* Store Nomination Signature - Function | Last Checked: 16-08-2025 */
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

    /* Process Extra Meta Data - Function | Last Checked: 16-08-2025 */
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

    /* Generate Nomination Document - Function | Last Checked: 16-08-2025 */
    public function generateNominationForm($nomination, $form, $mode, $finalDocRelativePath, $fileName)
    {
        try {

            /* LOAD FACULTY DATA */
            $faculty = Faculty::where('fac_status', 3)->first();

            if (!$faculty) {
                return back()->with('error', 'Error generating nomination form: Faculty not found.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $nomination->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Error generating nomination form: Student not found.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $nomination->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Error generating nomination form: Activity not found.');
            }

            /* LOAD FORM FIELD DATA */
            $formfields = FormField::where('af_id', $form->id)
                ->orderBy('ff_order')
                ->get();

            /* GET FORM SIGNATURE */
            $signatures = $formfields->where('ff_category', 6);

            /* GET NOMINATION SIGNATURE */
            $signatureData = $nomination ? json_decode($nomination->nom_signature_data) : null;

            /* MAPPING PROCESS - SUBSTITUTE DATA */
            $userData = [];
            $fhc = new FormHandlerController();
            $userData = $fhc->joinMap($formfields, $student, $activity);

            /* FETCH [NOMINATION] - CHAIR / EXAMINER / PANEL MEMBERS */
            if ($nomination) {
                $evaluators = Evaluator::where('nom_id', $nomination->id)
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
            if ($nomination && $nomination->nom_extra_data) {
                $extraData = json_decode($nomination->nom_extra_data, true);

                if (is_array($extraData)) {
                    foreach ($extraData as $key => $value) {
                        $normalizedKey = str_replace(' ', '_', strtolower($key));
                        $userData[$normalizedKey] = $value ?? '-';
                    }
                }
            }

            $pdf = Pdf::loadView('staff.sop.template.input-document', [
                'title' => $activity->act_name . " Document",
                'act' => $activity,
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

    /* Renominate Process - Function | Last Checked: 17-08-2025 */
    public function renominateProcess($nomID)
    {

        /* DECRYPT IDs */
        $nomID = Crypt::decrypt($nomID);

        try {

            /* SET STAFF ROLE */
            $staffrole = auth()->user()->staff_role;

            /* LOAD NOMINATION DATA */
            $nomination = Nomination::where('id', $nomID)->first();

            if (!$nomination) {
                return back()->with('error', 'Error occurred: Nomination not found.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $nomination->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Error occurred: Student not found.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $nomination->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Error occurred: Activity not found.');
            }

            /* LOAD SEMESTER DATA */
            $currsemester = Semester::where('sem_status', 1)->first();

            if (!$currsemester) {
                return back()->with('error', 'Error occurred: Current semester not found.');
            }

            /* CHECK IF NOMINATION FOR CURRENT SEMESTER ALREADY EXISTS */
            // $existNom = Nomination::where('student_id', $nomination->student_id)
            //     ->where('semester_id', $currsemester->id)
            //     ->where('activity_id', $nomination->activity_id)
            //     ->exists();

            // if ($existNom) {
            //     return back()->with('error', 'Update / Re-nomination for ' . $student->student_name . ' is not allowed for the current semester. Please try again in the next semester.');
            // }

            /* LOAD CURRENT EVALUATOR DATA */
            $currEvaluators = Evaluator::where('nom_id', $nomination->id)->where('eva_status', 3)->get();

            if ($currEvaluators) {
                foreach ($currEvaluators as $curreva) {

                    /* CHANGE STATUS OF EVALUATOR TO INACTIVE */
                    $curreva->eva_status = 2;
                    $curreva->save();

                    /* DELETE ANY PENDING EVALUATION DATA FOR CURRENT EVALUATORS */
                    Evaluation::where('staff_id', $curreva->staff_id)
                        ->where('activity_id', $activity->id)
                        ->where('student_id', $student->id)
                        ->where('semester_id', $currsemester->id)
                        ->where('evaluation_status', 1)
                        ->where('evaluation_isFinal', 0)
                        ->delete();
                }
            }

            /* REMOVE HIGHER UPS [COMMITTEE / DEPUTY DEAN / DEAN] SIGNATURES */
            $originalSignatures = json_decode($nomination->nom_signature_data, true);
            $filteredSignatures = array_filter(
                $originalSignatures,
                fn($key) => str_starts_with($key, 'sv_signature'),
                ARRAY_FILTER_USE_KEY
            );

            /* CREATE NEW NOMINATION RECORD */
            $newnomination = new Nomination();
            $newnomination->student_id = $nomination->student_id;
            $newnomination->semester_id = $currsemester->id;
            $newnomination->activity_id = $nomination->activity_id;
            $newnomination->nom_signature_data = json_encode($filteredSignatures);
            $newnomination->nom_extra_data = $nomination->nom_extra_data;
            $newnomination->nom_status = 2;
            $newnomination->save();

            /* CREATE NEW EVALUATOR RECORD */
            $evaluators = Evaluator::where('nom_id', $nomination->id)->get();
            foreach ($evaluators as $evaluator) {
                $newevaluator = new Evaluator();
                $newevaluator->nom_id = $newnomination->id;
                $newevaluator->staff_id = $evaluator->staff_id;
                $newevaluator->eva_status = $evaluator->eva_status;
                $newevaluator->eva_role = $evaluator->eva_role;
                $newevaluator->eva_meta = $evaluator->eva_meta;
                $newevaluator->save();
            }

            /* UPDATE NOMINATION STATUS FROM ACTIVE TO INACTIVE */
            $nomination->nom_status = 6;
            $nomination->save();

            /* REDIRECT BASED ON STAFF ROLE */
            if ($staffrole == 1) {
                return redirect()->route('nomination-student', ['nomID' => Crypt::encrypt($newnomination->id), 'mode' => Crypt::encrypt(2)])
                    ->with('success', 'Your update/ re-nomination request has been created successfully. Please complete the nomination process.');
            } elseif ($staffrole == 3) {
                return redirect()->route('nomination-student', ['nomID' => Crypt::encrypt($newnomination->id), 'mode' => Crypt::encrypt(3)])
                    ->with('success', 'Your update/ re-nomination request has been created successfully. Please complete the nomination process.');
            } elseif ($staffrole == 4) {
                return redirect()->route('nomination-student', ['nomID' => Crypt::encrypt($newnomination->id), 'mode' => Crypt::encrypt(4)])
                    ->with('success', 'Your update/ re-nomination request has been created successfully. Please complete the nomination process.');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error making request for nomination update/re-nomination: ' . $e->getMessage());
        }
    }
}
