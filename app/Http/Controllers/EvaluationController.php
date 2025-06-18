<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\Activity;
use App\Models\Semester;
use App\Models\FormField;
use App\Models\Programme;
use App\Models\Evaluation;
use App\Models\ActivityForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                    $data->where('evaluation_status', $req->input('status'));
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
                    // STUDENT SUBMISSION DIRECTORY
                    $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Nomination';

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
                        $status = '<span class="badge bg-light-danger">' . 'Failed' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('action', function ($row) {
                    $button = '';

                    $button = '
                            <a href="' . route('evaluation-student', ['studentId' => Crypt::encrypt($row->student_id), 'actId' => Crypt::encrypt($row->activity_id), 'mode' => 5]) . '" class="avtar avtar-xs btn-light-primary">
                                <i class="ti ti-edit f-20"></i>
                            </a>
                        ';
                    return $button;
                });

                $table->rawColumns(['student_photo', 'evaluation_document', 'evaluation_date', 'evaluation_status', 'action']);

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
                    $data->where('evaluation_status', $req->input('status'));
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
                    // STUDENT SUBMISSION DIRECTORY
                    $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Nomination';

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
                        $status = '<span class="badge bg-light-danger">' . 'Failed' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }

                    return $status;
                });

                $table->addColumn('action', function ($row) {
                    $button = '';

                    $button = '
                            <a href="' . route('evaluation-student', ['studentId' => Crypt::encrypt($row->student_id), 'actId' => Crypt::encrypt($row->activity_id), 'mode' => 6]) . '" class="avtar avtar-xs btn-light-primary">
                                <i class="ti ti-edit f-20"></i>
                            </a>
                        ';
                    return $button;
                });

                $table->rawColumns(['student_photo', 'evaluation_document', 'evaluation_date', 'evaluation_status', 'action']);

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

    /* Evaluation Student */
    public function evaluationStudent($studentId, $actId, $mode)
    {
        try {

            /* GET ID'S */
            $studentId = decrypt($studentId);
            $actId = decrypt($actId);

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
                'data' => $data,
                'mode' => $mode,
                'page' => $page,
                'link' => $link
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
                ['student_id', $student->id]
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


            /* FETCH [NOMINATION] - EXTRA META DATA */
            if ($evaluationRecord && $evaluationRecord->evaluation_meta_data) {
                $extraData = json_decode($evaluationRecord->evaluation_meta_data, true);

                if (is_array($extraData)) {
                    foreach ($extraData as $key => $value) {
                        $normalizedKey = str_replace(' ', '_', strtolower($key));
                        $userData[$normalizedKey] = $value ?? '-';
                    }
                }
            }

            $html = view('staff.sop.template.nomination-form', [
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

    /* Submit Evaluation Form */
    public function submitEvaluation(Request $req, $studentId, $mode)
    {
        try {

            dd($req->all());
            $option = $req->input('opt');
            $studentId = decrypt($studentId);

            /* GET STUDENT DATA */
            $student = Student::where('id', $studentId)->first();

            if (!$student) {
                return back()->with('error', 'Oops! Student not found');
            }

            /* GET FORM FIELD DATA */
            $actID = $req->input('activity_id');
            $activity = Activity::where('id', $actID)->first()->act_name;

            if (!$activity) {
                return back()->with('error', 'Oops! Activity not found');
            }

            $form = ActivityForm::where('activity_id', $actID)->where('af_target', 3)->first();

            if (!$form) {
                return back()->with('error', 'Oops! Form not found');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error submitting evaluation: ' . $e->getMessage());
        }
    }
}
