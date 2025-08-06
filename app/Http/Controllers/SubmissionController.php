<?php

namespace App\Http\Controllers;

use Exception;
use ZipArchive;
use Carbon\Carbon;
use App\Models\Faculty;
use App\Models\Student;
use setasign\Fpdi\Fpdi;
use App\Models\Activity;
use App\Models\Document;
use App\Models\Semester;
use App\Models\FormField;
use App\Models\Procedure;
use App\Models\Programme;
use App\Models\Evaluation;
use App\Models\Nomination;
use App\Models\Submission;
use App\Models\Supervision;
use Illuminate\Support\Str;
use App\Mail\SubmissionMail;
use App\Models\ActivityForm;
use Illuminate\Http\Request;
use App\Models\StudentActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SubmissionReview;
use App\Models\ActivityCorrection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use setasign\Fpdi\PdfParser\StreamReader;


class SubmissionController extends Controller
{
    /* General Function [REQUIRE CHECKING] */
    // EMAIL NOTOFICATION
    private function sendSubmissionNotification($data, $userType, $actName, $emailType, $approvalRole)
    {
        //USER TYPE 
        // 1 - Student
        // 2 - Staff

        if ($userType == 1) {
            $name = $data->student_name;
            $email = $data->student_email;
        } elseif ($userType == 2) {
            $name = $data->staff_name;
            $email = $data->staff_email;
        } else {
            $name = null;
            $email = null;
        }

        // APPROVAL ROLE 
        if ($approvalRole == 2) {
            $approvalUser = 'Supervisor';
        } elseif ($approvalRole == 3) {
            $approvalUser = 'Co-Supervisor';
        } elseif ($approvalRole == 4) {
            $approvalUser = 'Committee';
        } elseif ($approvalRole == 5) {
            $approvalUser = 'Deputy Dean';
        } elseif ($approvalRole == 6) {
            $approvalUser = 'Dean';
        } else {
            $approvalUser = null;
        }

        //EMAIL TYPE
        // 1 - SUBMISSION REMINDER
        // 2 - SUBMISSION CONFIRMED
        // 3 - SUBMISSION APPROVED
        // 4 - SUBMISSION REJECTED
        // 5 - SUBMISSION REVERTED
        // 6 - ACTIVITY COMPLETED
        // 7 - STUDENT SUBMISSION OPENED
        // 8 - STUDENT SUBMISSION CLOSED



        if ($emailType == 2) {
            $submissionDate = $data->submission_date ?? Carbon::now()->format('d F Y g:i A');
            $studentname = $data->student_name ?? 'Student';
            $studentMatricno = $data->student_matricno ?? 'Matric No';
        }

        if (env('MAIL_ENABLE') == 'true') {
            Mail::to($email)->send(new SubmissionMail([
                'eType' => $emailType,
                'act_name' => $actName,
                'approvalUser' => $approvalUser,
                'name' => Str::headline($name),
                'sa_date' => Carbon::now()->format('d F Y g:i A'),
                'student_name' => $studentname ?? '-',
                'student_matricno' => $studentMatricno ?? '-',
                'submission_date' => $submissionDate ?? Carbon::now()->format('d F Y g:i A'),
            ]));
        }
    }

    /* Programme Overview [Student] */
    public function studentProgrammeOverview()
    {
        try {
            $programmeActivity = DB::table('procedures as a')
                ->join('programmes as b', 'a.programme_id', '=', 'b.id')
                ->join('activities as c', 'a.activity_id', '=', 'c.id')
                ->where('b.id', auth()->user()->programme_id)
                ->orderBy('act_seq')
                ->get();

            $document = DB::table('procedures as a')
                ->join('programmes as b', 'a.programme_id', '=', 'b.id')
                ->join('activities as c', 'a.activity_id', '=', 'c.id')
                ->join('documents as d', 'c.id', '=', 'd.activity_id')
                ->join('submissions as e', 'd.id', '=', 'e.document_id')
                ->where('b.id', auth()->user()->programme_id)
                ->where('e.student_id', auth()->user()->id)
                ->select(
                    'c.id as activity_id',
                    'c.act_name as activity_name',
                    'd.doc_name as document_name',
                    'd.isRequired',
                    'e.id as submission_id',
                    'e.submission_status',
                    'e.submission_duedate',
                    'e.submission_document',
                    'e.submission_date',
                )
                ->get()
                ->groupBy('activity_id');

            $student_activity = StudentActivity::where('student_id', auth()->user()->id)->get();
            $documentQueryTwo = DB::table('documents as a')
                ->join('submissions as b', 'a.id', '=', 'b.document_id')
                ->where('b.student_id', auth()->user()->id)
                ->get();

            $submissionReview = DB::table('submission_reviews as a')
                ->join('staff as b', 'a.staff_id', '=', 'b.id')
                ->select('a.id as review_id', 'a.*', 'b.staff_name')
                ->get();

            $activityCorrections = DB::table('activity_corrections')
                ->where('student_id', auth()->user()->id)
                ->get();

            foreach ($programmeActivity as $activity) {
                $activitySubmissions = $document->get($activity->activity_id);
                $studentAct = $student_activity->firstWhere('activity_id', $activity->activity_id);
                $requiredDocument = $documentQueryTwo->where('activity_id', $activity->activity_id)->where('isRequired', 1)->count();
                $optionalDocument = $documentQueryTwo->where('activity_id', $activity->activity_id)->where('isRequired', 0)->count();
                $submittedRequiredDocument = $documentQueryTwo->where('activity_id', $activity->activity_id)->where('isRequired', 1)->where('submission_status', 3)->count();
                $submittedOptionalDocument = $documentQueryTwo->where('activity_id', $activity->activity_id)->where('isRequired', 0)->where('submission_status', 3)->count();

                $activityCorrection = $activityCorrections->firstWhere('activity_id', $activity->activity_id);
                if ($activityCorrection) {
                    $correctionStatusMap = [
                        1 => 8,  // Evaluation : Minor / Major Correction
                        2 => 12, // Correction: Pending SV 
                        3 => 13, // Correction: Pending Examiners/Panels 
                        4 => 14, // Correction: Pending Committee/Dean/Deputy Dean
                        5 => 15, // Approved & Completed
                        6 => 16, // Correction: Rejected by Supervisor
                        7 => 17, // Correction: Rejected by Examiners/Panels
                        8 => 18, // Correction: Rejected by Committee/Dean/Deputy Dean
                    ];

                    $activity->init_status = $correctionStatusMap[$activityCorrection->ac_status] ?? 10;

                    // SEMESTER LABEL
                    $currsemester = Semester::find($activityCorrection->semester_id);
                    $rawLabel = $currsemester->sem_label;
                    $semesterlabel = str_replace('/', '', $rawLabel);
                    $semesterlabel = trim($semesterlabel);

                    // CONFIRMED DOCUMENT
                    if ($activityCorrection->ac_final_submission != null) {
                        $activity->confirmed_corrected_document = $semesterlabel . '/' . $activityCorrection->ac_final_submission;
                    }
                } elseif ($studentAct) {
                    // Change status based on SA status
                    $activity->init_status = $studentAct->sa_status;
                    $activity->confirmed_document = $studentAct->sa_final_submission;
                } else {
                    // No confirmation yet
                    if ($activitySubmissions) {
                        $lockedSubmission = $activitySubmissions?->firstWhere('submission_status', 2);

                        $activity->init_status = $lockedSubmission ? 11 : 10;
                    } else {
                        $activity->init_status = 11;
                    }
                }

                $activity->required_document = $requiredDocument;
                $activity->optional_document = $optionalDocument;
                $activity->submitted_required_document = $submittedRequiredDocument;
                $activity->submitted_optional_document = $submittedOptionalDocument;
                $activity->student_activity_id = $studentAct->id ?? null;
            }

            // Filter out submissions with 'submission_status' of 2 or 5
            $filtered_documents = $document->map(function ($activityGroup) {
                return $activityGroup->filter(function ($submission) {
                    return !in_array($submission->submission_status, [2, 5]);
                });
            });

            $evaluationReport = Evaluation::where('student_id', auth()->user()->id)
                ->where('evaluation_isFinal', 1)
                ->get();

            return view('student.programme.programme-index', [
                'title' => 'Programme Overview',
                'acts' => $programmeActivity,
                'docs' => $filtered_documents,
                'sa' => $student_activity,
                'submissionReview' => $submissionReview,
                'evaluationReport' => $evaluationReport,
            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
            return abort(500, $e->getMessage());
        }
    }

    public function documentSubmission($id)
    {
        try {

            $id = decrypt($id);
            $document = DB::table('procedures as a')
                ->join('programmes as b', 'a.programme_id', '=', 'b.id')
                ->join('activities as c', 'a.activity_id', '=', 'c.id')
                ->join('documents as d', 'c.id', '=', 'd.activity_id')
                ->join('submissions as e', 'd.id', '=', 'e.document_id')
                ->where('b.id', auth()->user()->programme_id)
                ->where('e.id', $id)
                ->select(
                    'c.id as activity_id',
                    'c.act_name as activity_name',
                    'd.id as document_id',
                    'd.doc_name as document_name',
                    'd.isRequired',
                    'e.id as submission_id',
                    'e.submission_status',
                    'e.submission_duedate',
                    'e.submission_document',
                    'e.submission_date',
                )
                ->first();

            // STUDENT SUBMISSION DIRECTORY
            $submission_dir = auth()->user()->student_directory . '/' . auth()->user()->programmes->prog_code . '/' . $document->activity_name;

            return view('student.programme.document-submission', [
                'title' => $document->document_name . ' Submission',
                'doc' => $document,
                'submission_dir' => $submission_dir
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    public function submitDocument(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'file' => 'required|file|mimes:pdf,docx|max:102400',
                'activity_id' => 'required|integer|exists:activities,id',
                'document_id' => 'required|integer|exists:documents,id',
                'submission_id' => 'required|integer|exists:submissions,id',
            ], [], [
                'file' => 'submission document',
                'activity_id' => 'activity',
                'document_id' => 'document',
                'submission_id' => 'submission',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // INITIALIZE VARIABLES
            $validated = $validator->validated();
            $student = auth()->user();
            $originalFile = $req->file('file');

            $programme_name = Str::upper($student->programmes->prog_code);
            $activity = Activity::where('id', $validated['activity_id'])->first();
            $document = Document::where('id', $validated['document_id'])->first();
            $semester = Semester::where('sem_status', 1)->first();

            $procedure = DB::table('procedures as a')
                ->join('programmes as b', 'a.programme_id', '=', 'b.id')
                ->join('activities as c', 'a.activity_id', '=', 'c.id')
                ->join('documents as d', 'c.id', '=', 'd.activity_id')
                ->where('b.id', auth()->user()->programme_id)
                ->where('c.id', $validated['activity_id'])
                ->where('d.id', $validated['document_id'])
                ->first();

            $activity_name = $activity->act_name ?? 'UNKNOWN';
            $document_name = $document->doc_name ?? 'UNKNOWN';
            $semester_name = $semester->sem_label ?? 'UNKNOWN';

            // 1 - CUSTOMIZE FILENAME
            $activity_clean = preg_replace('/[\/\s]+/', '', $activity_name);
            $document_clean = preg_replace('/[\/\s]+/', '', $document_name);
            $filename = "{$student->student_matricno}_{$document_clean}_{$activity_clean}." . $originalFile->getClientOriginalExtension();

            // 2 - SAVE SUBMISSION FILE INTO DIRECTORY
            $semesterlabel = str_replace('/', '', $semester_name);
            $semesterlabel = trim($semesterlabel);
            $safe_path = "{$student->student_directory}/{$programme_name}/{$activity_name}";

            if ($procedure->is_repeatable == 1) {
                $safe_path = $safe_path . '/' . $semesterlabel;
            }

            $originalFile->storeAs($safe_path, $filename);

            if ($procedure->is_repeatable == 1) {
                $filename = $semesterlabel . '/' . $filename;
            }

            // 3 - SAVE SUBMISSION DATA INTO DATABASE
            Submission::where('id', $validated['submission_id'])->update([
                'submission_document' => $filename,
                'submission_date' => now()->toDateTimeString(),
                'submission_status' => 3,
                'semester_id' => $semester->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Submission document successfully uploaded!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Oops! Error uploading submission document: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function removeDocument($id, $filename)
    {
        try {
            $id = decrypt($id);
            $filename = decrypt($filename);

            $submission = Submission::where('id', $id)->first();

            // DETERMINE SUBMISSION STATUS
            $sub_status = 1;
            if (Carbon::parse($submission->submission_duedate)->lessThan(now())) {
                $sub_status = 4;
            } elseif (Carbon::parse($submission->submission_duedate)->greaterThan(now())) {
                $sub_status = 1;
            }

            //DELETE THE SUBMISSIOM DOCUMENT
            if (Storage::exists($filename)) {
                Storage::delete($filename);
            }

            Submission::where('id', $id)->update([
                'submission_document' => '-',
                'submission_date' => null,
                'submission_status' => $sub_status,
            ]);

            return back()->with('success', 'Submission has been removed successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error removing submission: ' . $e->getMessage());
        }
    }

    // ## SEND EMAIL - SV --> Partially done
    public function confirmStudentSubmission(Request $req, $actID)
    {
        try {
            $actID = decrypt($actID);
            $student = auth()->user();

            if (!$student) {
                return back()->with('error', 'Unauthorized access : Student record is not found.');
            }

            $activity = Activity::where('id', $actID)->first()->act_name;
            $form = ActivityForm::where([
                ['activity_id', $actID],
                ['af_status', 1],
                ['af_target', 1],
            ])->first();

            if (!$form) {
                return back()->with('error', 'Activity form not found. Submission could not be confirmed. Please contact administrator for further assistance.');
            }

            $documentName = $student->student_matricno . '_' . str_replace(' ', '_', $activity) . '.pdf';

            //---------------------------------------------------------------------------//
            //------------------- SAVE SIGNATURE TO STUDENT_ACTIVITY --------------------//
            //---------------------------------------------------------------------------//

            $signatureData = $req->input('signatureData');

            // 1 - Signature Role [Student]
            // 1 - Document Status [Pending]
            $this->storeSignature($actID, $student, $form, $signatureData, $documentName, 1, null, 1);

            //---------------------------------------------------------------------------//
            //--------------------------GENERATE ACTIVITY FORM CODE----------------------//
            //---------------------------------------------------------------------------//

            // RETRIEVE ACTIVITY PATH
            $progcode = strtoupper($student->programmes->prog_code);
            $basePath = storage_path("app/public/{$student->student_directory}/{$progcode}/{$activity}");

            if (!File::exists($basePath)) {
                return back()->with('error', 'Activity folder not found.');
            }

            // CREATE A NEW FOLDER (FINAL DOCUMENT)
            $finalDocPath = $basePath . '/Final Document';

            if (!File::exists($finalDocPath)) {
                File::makeDirectory($finalDocPath, 0755, true);
            }

            $relativePath = "{$student->student_directory}/{$progcode}/{$activity}/";

            $this->generateActivityForm($actID, $student, $form, $relativePath);

            //---------------------------------------------------------------------------//
            //--------------------------MERGE PDF DOCUMENTS CODE-------------------------//
            //---------------------------------------------------------------------------//

            // RETRIEVE PDF FILES
            $pdfFiles = File::files($basePath);

            $pdfFiles = array_filter($pdfFiles, function ($file) {
                return strtolower($file->getExtension()) === 'pdf';
            });

            if (empty($pdfFiles)) {
                return back()->with('error', 'No PDF documents found in the activity folder.' .  $basePath);
            }

            $pdf = new Fpdi();

            foreach ($pdfFiles as $file) {
                $pageCount = $pdf->setSourceFile(StreamReader::createByFile($file->getPathname()));
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $template = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($template);

                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($template);
                }
            }

            // SAVE THE MERGED PDF
            $mergedPath =  $finalDocPath . '/' . $documentName;
            $pdf->Output($mergedPath, 'F');

            // SEND EMAIL SECTION
            $supervision = DB::table('supervisions as a')
                ->join('staff as b', 'a.staff_id', '=', 'b.id')
                ->where('a.student_id', $student->id)
                ->where('a.supervision_role', 1)
                ->select('b.staff_name', 'b.staff_email')
                ->first();

            if ($supervision) {
                $data = [
                    'student_name' => $student->student_name,
                    'student_matricno' => $student->student_matricno,
                    'submission_date' => Carbon::now()->format('d F Y g:i A'),
                    'staff_name' => $supervision->staff_name,
                    'staff_email' => $supervision->staff_email,
                ];
                // dd($data);
                $this->sendSubmissionNotification((object)$data, 2, $activity, 2, null);
            }

            return back()->with('success', 'Submission has been confirmed successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error confirming submission: ' . $e->getMessage());
        }
    }

    /* Document [Activity Form] Handler Function [Start] */
    public function mergeStudentSubmission($actID, $student, $signatureData, $role, $userName, $status)
    {
        try {
            $actID = decrypt($actID);

            if (!$student) {
                return back()->with('error', 'Unauthorized access : Student record is not found.');
            }

            $activity = Activity::where('id', $actID)->first()->act_name;
            $form = ActivityForm::where([
                ['activity_id', $actID],
                ['af_status', 1],
                ['af_target', 1],
            ])->first();

            if (!$form) {
                return back()->with('error', 'Activity form not found. Submission could not be confirmed. Please contact administrator for further assistance.');
            }

            $documentName = $student->student_matricno . '_' . str_replace(' ', '_', $activity) . '.pdf';

            //---------------------------------------------------------------------------//
            //------------------- SAVE SIGNATURE TO STUDENT_ACTIVITY --------------------//
            //---------------------------------------------------------------------------//

            // 1 - Signature Role [Student]
            // 1 - Document Status [Pending]
            $this->storeSignature($actID, $student, $form, $signatureData, $documentName, $role, $userName, $status);

            //---------------------------------------------------------------------------//
            //--------------------------GENERATE ACTIVITY FORM CODE----------------------//
            //---------------------------------------------------------------------------//

            // RETRIEVE ACTIVITY PATH
            $progcode = strtoupper($student->programmes->prog_code);
            $basePath = storage_path("app/public/{$student->student_directory}/{$progcode}/{$activity}");

            if (!File::exists($basePath)) {
                return back()->with('error', 'Activity folder not found.');
            }

            // CREATE A NEW FOLDER (FINAL DOCUMENT)
            $finalDocPath = $basePath . '/Final Document';

            if (!File::exists($finalDocPath)) {
                File::makeDirectory($finalDocPath, 0755, true);
            }

            $relativePath = "{$student->student_directory}/{$progcode}/{$activity}/";

            $this->generateActivityForm($actID, $student, $form, $relativePath);

            //---------------------------------------------------------------------------//
            //--------------------------MERGE PDF DOCUMENTS CODE-------------------------//
            //---------------------------------------------------------------------------//

            // RETRIEVE PDF FILES
            $pdfFiles = File::files($basePath);

            $pdfFiles = array_filter($pdfFiles, function ($file) {
                return strtolower($file->getExtension()) === 'pdf';
            });

            if (empty($pdfFiles)) {
                return back()->with('error', 'No PDF documents found in the activity folder.' .  $basePath);
            }

            $pdf = new Fpdi();

            foreach ($pdfFiles as $file) {
                $pageCount = $pdf->setSourceFile(StreamReader::createByFile($file->getPathname()));
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $template = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($template);

                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($template);
                }
            }

            // SAVE THE MERGED PDF
            $mergedPath =  $finalDocPath . '/' . $documentName;
            return $pdf->Output($mergedPath, 'F');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error confirming submission: ' . $e->getMessage());
        }
    }

    public function generateActivityForm($actID, $student, $form, $finalDocRelativePath)
    {
        try {

            $act = Activity::where('id', $actID)->first();

            if (!$act) {
                return back()->with('error', 'Activity not found.');
            }

            $formfields = FormField::where('af_id', $form->id)
                ->orderBy('ff_order')
                ->get();

            $faculty = Faculty::where('fac_status', 3)->first();
            $signatures = $formfields->where('ff_category', 6);

            $signatureRecord = StudentActivity::where([
                ['activity_id', $actID],
                ['student_id', $student->id]
            ])->select('sa_signature_data')->first();

            $signatureData = $signatureRecord ? json_decode($signatureRecord->sa_signature_data) : null;

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
                'corrections' => [
                    'activities' => [
                        'alias' => 'b',
                        'table' => 'activities',
                        'on' => ['a.activity_id', '=', 'b.id'],
                    ],
                    'students' => [
                        'alias' => 'c',
                        'table' => 'students',
                        'on' => ['a.student_id', '=', 'c.id'],
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

                if ($baseTable === 'corrections') {
                    $query->where('a.student_id', $student->id)
                        ->where('a.activity_id', $act->id);
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

            $pdf = Pdf::loadView('student.programme.form-template.activity-document', [
                'title' => $act->act_name . " Document",
                'act' => $act,
                'form_title' => $form->af_title,
                'formfields' => $formfields,
                'userData' => $userData,
                'faculty' => $faculty,
                'signatures' => $signatures,
                'signatureData' => $signatureData
            ]);

            $fileName = 'Activity_Form_' . $student->student_matricno . '_' . '.pdf';
            $relativePath = $finalDocRelativePath . '/' . $fileName;

            Storage::disk('public')->put($relativePath, $pdf->output());

            return $pdf->stream($fileName . '.pdf');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error generating activity form: ' . $e->getMessage());
        }
    }

    public function storeSignature($actID, $student, $form, $signatureData, $documentName, $signatureRole, $userData, $status)
    {
        try {
            if ($signatureData) {

                $signatureField = FormField::where([
                    ['af_id', $form->id],
                    ['ff_category', 6],
                    ['ff_signature_role', $signatureRole]
                ])->first();

                $studentActivity = StudentActivity::firstOrNew([
                    'activity_id' => $actID,
                    'student_id' => $student->id
                ]);

                $existingSignatureData = [];
                if ($studentActivity->sa_signature_data) {
                    $existingSignatureData = json_decode($studentActivity->sa_signature_data, true);
                }

                $isCrossApproval = false;

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

                if ($signatureField) {
                    $signatureKey = $signatureField->ff_signature_key;
                    $dateKey = $signatureField->ff_signature_date_key;

                    if ($signatureRole == 1) {
                        $newSignatureData = [
                            $signatureKey => $signatureData,
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
                            $signatureKey => $signatureData,
                            $dateKey => now()->format('d M Y'),
                            $signatureKey . '_name' => $userData->staff_name,
                            $signatureKey . '_role' => $role,
                            $signatureKey . '_is_cross_approval' => $isCrossApproval
                        ];
                    }

                    // Merge and save
                    if ($signatureRole == 1) {
                        $mergedSignatureData = $newSignatureData;
                    } else {
                        $mergedSignatureData = array_merge($existingSignatureData, $newSignatureData);
                    }
                    $studentActivity->sa_signature_data = json_encode($mergedSignatureData);
                    $studentActivity->sa_final_submission = $documentName;
                    $studentActivity->sa_status = $status;
                    $studentActivity->save();
                }
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error storing signature: ' . $e->getMessage());
        }
    }

    /* Document [Activity Form] Handler Function [End] */


    public function viewFinalDocument($actID, $filename, $opt)
    {
        $actID = decrypt($actID);
        $filename = Crypt::decrypt($filename);

        try {
            $student = auth()->user();
            $activity = Activity::where('id', $actID)->first()->act_name;
            $progcode = strtoupper($student->programmes->prog_code);
            if ($opt == 1) {
                $basePath = storage_path("app/public/{$student->student_directory}/{$progcode}/{$activity}/Final Document/{$filename}");
            } else if ($opt == 2) {
                $basePath = storage_path("app/public/{$student->student_directory}/{$progcode}/{$activity}/Evaluation/{$filename}");
            } else if ($opt == 3) {
                $basePath = storage_path("app/public/{$student->student_directory}/{$progcode}/{$activity}/Correction/{$filename}");
            }

            if (!file_exists($basePath)) {
                abort(404, 'File not found.');
            }

            return response()->file($basePath);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* Submission Management [Staff] [Committee/DD/DEAN] */
    public function submissionManagement(Request $req)
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
                ->orderBy('f.act_name');

            if ($req->ajax()) {

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
                    $data->where('submission_status', $req->input('status'));
                } else {
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
                                        data-bs-target="#archiveModal-' . $row->submission_id . '">
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
                                        data-bs-target="#archiveModal-' . $row->submission_id . '">
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
            return view('staff.submission.submission-management', [
                'title' => 'Submission Management',
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
            dd($e->getMessage());
            return abort(500);
        }
    }

    public function getStudentSubmissionEligibility($matricno, $activityid)
    {
        try {
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
                    's.student_semcount',
                    'b.sem_label',
                    'c.prog_code',
                    'c.prog_mode',
                    'c.fac_id',
                    's.student_semcount',
                    'p.timeline_sem',
                    'p.programme_id',
                    'a.id as activity_id',
                    'a.act_name as activity_name',
                    'p.act_seq',
                    'p.init_status',
                    DB::raw("
                    CASE
                        WHEN EXISTS (
                            SELECT 1 FROM student_activities sa_current
                            WHERE sa_current.student_id = s.id
                            AND sa_current.activity_id = p.activity_id
                            AND sa_current.sa_status = 3
                        ) THEN 5
                        WHEN EXISTS (
                            SELECT 1 FROM documents d
                            JOIN submissions sub ON sub.document_id = d.id
                            WHERE d.activity_id = p.activity_id
                            AND sub.student_id = s.id
                            AND sub.submission_status = 5
                        ) THEN 6
                        WHEN EXISTS (
                            SELECT 1 FROM student_activities sa_current
                            WHERE sa_current.student_id = s.id
                            AND sa_current.activity_id = p.activity_id
                        ) THEN 4
                        WHEN EXISTS (
                            SELECT 1 FROM documents d
                            JOIN submissions sub ON sub.document_id = d.id
                            WHERE d.activity_id = p.activity_id
                            AND sub.student_id = s.id
                            AND sub.submission_status IN (1, 4)
                        ) 
                        AND NOT EXISTS (
                            SELECT 1 FROM student_activities sa
                            WHERE sa.student_id = s.id
                            AND sa.activity_id = p.activity_id
                        ) THEN 2
                        WHEN EXISTS (
                            SELECT 1 FROM procedures p_prev
                            WHERE p_prev.programme_id = s.programme_id
                            AND p_prev.act_seq < p.act_seq
                            AND NOT EXISTS (
                                SELECT 1 FROM student_activities sa_prev
                                WHERE sa_prev.student_id = s.id
                                AND sa_prev.activity_id = p_prev.activity_id
                                AND sa_prev.sa_status = 3
                            )
                        ) THEN 3
                        ELSE 1
                    END as suggestion_status
                ")
                ])
                ->leftJoinSub($latestSemesterSub, 'latest', function ($join) {
                    $join->on('s.id', '=', 'latest.student_id');
                })
                ->leftJoin('student_semesters as ss', function ($join) {
                    $join->on('ss.student_id', '=', 's.id')
                        ->on('ss.semester_id', '=', 'latest.latest_semester_id');
                })
                ->leftJoin('semesters as b', 'b.id', '=', 'ss.semester_id')
                ->join('procedures as p', function ($join) {
                    $join->on('s.programme_id', '=', 'p.programme_id')
                        ->whereRaw('s.student_semcount >= p.timeline_sem')
                        ->where('p.init_status', '=', 2);
                })
                ->join('activities as a', 'p.activity_id', '=', 'a.id')
                ->join('programmes as c', 'c.id', '=', 's.programme_id')
                ->where('s.student_matricno', '=', $matricno)
                ->where('a.id', '=', $activityid)
                ->where('s.student_status', '=', 1)
                ->orderBy('s.student_matricno')
                ->orderBy('p.act_seq')
                ->first();

            return $data ? $data->suggestion_status : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function assignSubmission()
    {
        try {
            /** LOAD CURRENT SEMESTER **/
            $currsemester = Semester::where('sem_status', 1)->first();

            if (!$currsemester) {
                return back()->with('error', 'No current semester found.');
            }

            /** LOAD PROCEDURES DATA **/
            $data = DB::table('procedures as a')
                ->join('activities as b', 'a.activity_id', '=', 'b.id')
                ->join('documents as c', 'b.id', '=', 'c.activity_id')
                ->join('programmes as d', 'a.programme_id', '=', 'd.id')
                ->join('students as e', 'd.id', '=', 'e.programme_id')
                ->where('e.student_status', '=', 1)
                ->select('e.student_matricno', 'a.timeline_week', 'a.init_status', 'a.is_repeatable', 'e.id as student_id', 'c.id as document_id', 'b.id as activity_id')
                ->get();

            /** ASSIGNING SUBMISSION **/
            foreach ($data as $sub) {
                $createdSubmission = null;

                if ($sub->is_repeatable == 1) {
                    $checkExists = Submission::where('student_id', $sub->student_id)
                        ->where('document_id', $sub->document_id)
                        ->where('semester_id', $currsemester->id)
                        ->exists();
                } else {
                    $checkExists = Submission::where('student_id', $sub->student_id)
                        ->where('document_id', $sub->document_id)
                        ->exists();
                }

                if (!$checkExists) {
                    $days = $sub->timeline_week * 7;
                    $submissionDate = Carbon::parse($currsemester->sem_startdate)->addDays($days);

                    if ($sub->init_status == 2) {
                        $status = 2;
                    } elseif ($sub->init_status == 1) {
                        $status = $submissionDate->lt(Carbon::today()) ? 4 : 1;
                    } else {
                        $status = $sub->init_status;
                    }

                    $createdSubmission = Submission::create([
                        'submission_document' => '-',
                        'submission_duedate' => $submissionDate,
                        'submission_status' => $status,
                        'student_id' => $sub->student_id,
                        'document_id' => $sub->document_id,
                        'semester_id' => $currsemester->id
                    ]);
                }

                $createdSubmission = Submission::where('student_id', $sub->student_id)
                    ->where('document_id', $sub->document_id)
                    ->where('semester_id', $currsemester->id)
                    ->first();

                if ($sub->is_repeatable == 1 && $createdSubmission) {
                    $decision = $this->getStudentSubmissionEligibility($sub->student_matricno, $sub->activity_id);

                    if ($decision == 1) {
                        $submissionDate = Carbon::parse($currsemester->sem_startdate)->addDays($sub->timeline_week * 7);
                        $status = $submissionDate->lt(Carbon::today()) ? 4 : 1;

                        $createdSubmission->update([
                            'submission_status' => $status,
                            'submission_duedate' => $submissionDate
                        ]);
                    } elseif (in_array($decision, [0, 3])) {
                        $createdSubmission->delete();
                    }
                }
            }

            return back()->with('success', 'Submission has been assigned successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error assigning students with submission: ' . $e->getMessage());
        }
    }

    public function assignStudentSubmission($matricno)
    {
        try {

            /** LOAD CURRENT SEMESTER **/
            $currsemester = Semester::where('sem_status', 1)->first();

            if (!$currsemester) {
                return back()->with('error', 'No current semester found.');
            }

            /** LOAD PROCEDURES DATA **/
            $data = DB::table('procedures as a')
                ->join('activities as b', 'a.activity_id', '=', 'b.id')
                ->join('documents as c', 'b.id', '=', 'c.activity_id')
                ->join('programmes as d', 'a.programme_id', '=', 'd.id')
                ->join('students as e', 'd.id', '=', 'e.programme_id')
                ->where('e.student_status', '=', 1)
                ->where('e.student_matricno', '=', $matricno)
                ->select('e.student_matricno', 'a.timeline_week', 'a.init_status', 'a.is_repeatable', 'e.id as student_id', 'c.id as document_id', 'b.id as activity_id')
                ->get();

            /** ASSIGNING SUBMISSION **/
            foreach ($data as $sub) {
                $createdSubmission = null;

                if ($sub->is_repeatable == 1) {
                    $checkExists = Submission::where('student_id', $sub->student_id)
                        ->where('document_id', $sub->document_id)
                        ->where('semester_id', $currsemester->id)
                        ->exists();
                } else {
                    $checkExists = Submission::where('student_id', $sub->student_id)
                        ->where('document_id', $sub->document_id)
                        ->exists();
                }

                if (!$checkExists) {
                    $days = $sub->timeline_week * 7;
                    $submissionDate = Carbon::parse($currsemester->sem_startdate)->addDays($days);

                    if ($sub->init_status == 2) {
                        $status = 2;
                    } elseif ($sub->init_status == 1) {
                        $status = $submissionDate->lt(Carbon::today()) ? 4 : 1;
                    } else {
                        $status = $sub->init_status;
                    }

                    $createdSubmission = Submission::create([
                        'submission_document' => '-',
                        'submission_duedate' => $submissionDate,
                        'submission_status' => $status,
                        'student_id' => $sub->student_id,
                        'document_id' => $sub->document_id,
                        'semester_id' => $currsemester->id
                    ]);
                }

                $createdSubmission = Submission::where('student_id', $sub->student_id)
                    ->where('document_id', $sub->document_id)
                    ->where('semester_id', $currsemester->id)
                    ->first();

                if ($sub->is_repeatable == 1 && $createdSubmission) {
                    $decision = $this->getStudentSubmissionEligibility($sub->student_matricno, $sub->activity_id);

                    if ($decision == 1) {
                        $submissionDate = Carbon::parse($currsemester->sem_startdate)->addDays($sub->timeline_week * 7);
                        $status = $submissionDate->lt(Carbon::today()) ? 4 : 1;

                        $createdSubmission->update([
                            'submission_status' => $status,
                            'submission_duedate' => $submissionDate
                        ]);
                    } elseif (in_array($decision, [0, 3])) {
                        $createdSubmission->delete();
                    }
                }
            }

            return back()->with('success', 'Submission has been assigned successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error assigning students with submission: ' . $e->getMessage());
        }
    }

    public function updateSubmission(Request $req, $id)
    {
        $id = decrypt($id);

        $validator = Validator::make($req->all(), [
            'submission_status_up' => 'required|integer|in:1,2,3,4,5',
            'submission_duedate_up' => 'required',
        ], [], [
            'submission_status_up' => 'submission status',
            'submission_duedate_up' => 'submission due date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'settingModal-' . $id);
        }

        try {

            // DETERMINE SUBMISSION STATUS
            $sub_status = 1;
            if (Carbon::parse($req->submission_duedate_up)->lessThan(now()) && ($req->submission_status_up == 1 || $req->submission_status_up == 4)) {
                $sub_status = 4;
            } elseif (Carbon::parse($req->submission_duedate_up)->greaterThan(now()) && ($req->submission_status_up == 1 || $req->submission_status_up == 4)) {
                $sub_status = 1;
            } elseif ($req->submission_status_up == 3) {
                $sub_status = 3;
            } else {
                $sub_status = $req->submission_status_up;
            }

            Submission::where('id', $id)->update([
                'submission_status' =>  $sub_status,
                'submission_duedate' => $req->submission_duedate_up
            ]);

            return back()->with('success', 'Submission has been updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating submission: ' . $e->getMessage());
        }
    }

    public function archiveSubmission($id, $opt)
    {
        try {
            $id = decrypt($id);
            $submission = Submission::where('id', $id)->first();

            if ($opt == 1) // Archive Submission
            {
                $submission->update(['submission_status' => 5]);

                $message = "Submission has been archived successfully.";
            } elseif ($opt == 2) // Unarchive Submission
            {
                if ($submission->submission_date == null) {
                    $submission->update(['submission_status' => 2]);
                } else {
                    $submission->update(['submission_status' => 3]);
                }

                $message = "Submission has been unarchived successfully.";
            }


            return back()->with('success', $message);
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error archiving submission: ' . $e->getMessage());
        }
    }

    public function updateMultipleSubmission(Request $req)
    {
        $submissionIds = $req->input('selectedIds');

        $rules = [];
        $attributes = [];

        if ($req->has('submission_status_ups') && !empty($req->input('submission_status_ups'))) {
            $rules['submission_status_ups'] = 'integer|in:1,2,3,4,5';
            $attributes['submission_status_ups'] = 'submission status';
        }

        if ($req->has('submission_duedate_ups') && !empty($req->input('submission_duedate_ups'))) {
            $rules['submission_duedate_ups'] = 'nullable|date';
            $attributes['submission_duedate_ups'] = 'submission due date';
        }

        if (!empty($rules)) {
            $validator = Validator::make($req->all(), $rules, [], $attributes);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                    'message' => 'Validation failed.',
                ], 422);
            }
        }

        try {
            $newStatusInput = $req->input('submission_status_ups');
            $newDueDateInput = $req->input('submission_duedate_ups');

            $submissions = Submission::whereIn('id', $submissionIds)->get();

            foreach ($submissions as $submission) {
                $originalStatus = $submission->submission_status;
                $newStatus = $newStatusInput ?? $originalStatus;
                $newDueDate = $newDueDateInput ?? $submission->submission_duedate;

                // Default to current values
                $finalStatus = $originalStatus;

                // Only update status if it's not locked (status 3)
                if ($originalStatus != 3) {
                    if (Carbon::parse($newDueDate)->lessThan(now()) && in_array($newStatus, [1, 4])) {
                        $finalStatus = 4;
                    } elseif (Carbon::parse($newDueDate)->greaterThan(now()) && in_array($newStatus, [1, 4])) {
                        $finalStatus = 1;
                    } elseif ($newStatus == 3) {
                        $finalStatus = 3;
                    } else {
                        $finalStatus = $newStatus;
                    }

                    $submission->submission_status = $finalStatus;
                }

                // Update due date if provided
                if ($newDueDateInput) {
                    $submission->submission_duedate = $newDueDateInput;
                }

                $submission->save();
            }

            return response()->json([
                'message' => 'All selected submissions have been updated successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Oops! Error updating submissions: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function archiveMultipleSubmission(Request $req)
    {
        try {
            $submissionIds = $req->input('selectedIds');
            $opt = $req->input('option');
            $submissions = Submission::whereIn('id', $submissionIds)->get();

            foreach ($submissions as $submission) {
                if ($opt == 1) // Archive Submission
                {
                    $submission->update(['submission_status' => 5]);

                    $message = "Submission has been archived successfully.";
                } elseif ($opt == 2) // Unarchive Submission
                {
                    if ($submission->submission_date == null) {
                        $submission->update(['submission_status' => 2]);
                    } else {
                        $submission->update(['submission_status' => 3]);
                    }

                    $message = "Submission has been unarchived successfully.";
                }
            }

            return back()->with('success', $message);
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error archiving selected submissions: ' . $e->getMessage());
        }
    }

    public function downloadMultipleSubmission(Request $req)
    {
        try {
            $submissionIds = json_decode($req->query('ids'), true);
            if (!$submissionIds || count($submissionIds) === 0) {
                return back()->with('error', 'No submissions selected.');
            }

            // Create ZIP file
            $zipFile = storage_path('app/public/ePGS_SELECTED_SUBMISSION.zip');

            if (File::exists($zipFile)) {
                File::delete($zipFile);
            }

            $zip = new ZipArchive;
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return back()->with('error', 'Failed to create ZIP file.');
            }

            // Add each implant directory to the ZIP
            foreach ($submissionIds as $id) {
                $submission = DB::table('students as a')
                    ->join('programmes as c', 'c.id', '=', 'a.programme_id')
                    ->join('submissions as d', 'd.student_id', '=', 'a.id')
                    ->join('documents as e', 'e.id', '=', 'd.document_id')
                    ->join('activities as f', 'f.id', '=', 'e.activity_id')
                    ->select(
                        'a.*',
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
                    ->where('d.id', $id)
                    ->first();

                // STUDENT SUBMISSION DIRECTORY
                $submission_dir = $submission->student_directory . '/' . $submission->prog_code . '/' . $submission->activity_name;


                if (!$submission || empty($submission->submission_document)) {
                    continue;
                }

                $folderPath = public_path("storage/" . $submission_dir);

                if (!File::exists($folderPath)) {
                    continue;
                }

                $files = File::allFiles($folderPath);

                foreach ($files as $file) {
                    if ($submission->submission_document == $file->getFilename()) {
                        $path = Str::upper($submission->activity_name . '/' . $submission->student_matricno . '_' . str_replace(' ', '_', $submission->student_name));
                        $relativePath = $path . '/' . $file->getFilename();
                        $zip->addFile($file->getPathname(), $relativePath);
                    }
                }
            }

            $zip->close();

            return response()->download($zipFile)->deleteFileAfterSend(true);
        } catch (Exception $e) {
            return back()->with('error', 'Error generating ZIP: ' . $e->getMessage());
        }
    }

    /* Submission Approval [Staff] [Committee/DD/DEAN] */
    public function submissionApproval(Request $req)
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
                ->select(
                    'a.id as student_id',
                    'a.*',
                    'b.prog_code',
                    'b.prog_mode',
                    'sem.sem_label',
                    'd.id as activity_id',
                    'd.act_name as activity_name',
                    'c.id as student_activity_id',
                    'c.sa_status',
                    'c.sa_final_submission',
                    'c.sa_signature_data',
                    'c.activity_id',
                    'c.updated_at',
                )
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('supervisions as e')
                        ->whereColumn('e.student_id', 'a.id')
                        ->where('e.staff_id', auth()->user()->id);
                })
                ->orderBy('d.act_name');


            if ($req->ajax()) {

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

                    $svname = DB::table('supervisions as a')
                        ->join('staff as b', 'b.id', '=', 'a.staff_id')
                        ->where('a.student_id', $row->student_id)
                        ->where('a.supervision_role', 1)
                        ->select('b.staff_name')
                        ->first()
                        ->staff_name ?? 'N/A';

                    $cosvname = DB::table('supervisions as a')
                        ->join('staff as b', 'b.id', '=', 'a.staff_id')
                        ->where('a.student_id', $row->student_id)
                        ->where('a.supervision_role', 2)
                        ->select('b.staff_name')
                        ->first()
                        ->staff_name ?? 'N/A';


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
                                <small class="text-muted d-block fw-bold mt-2 mb-2">Main Supervisor: <br><span class="fw-normal">' .  $svname . '</span></small>
                                <small class="text-muted d-block fw-bold mb-2">Co-Supervisor: <br><span class="fw-normal">' .  $cosvname . '</span></small>
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
                        1 => "
                            <div class='alert alert-light' role='alert'>
                                <i class='ti ti-alert-circle me-1'></i>
                                <small>By approving this, Supervisor and Co-Supervisor approvals will be skipped.</small>
                            </div>
                            <span class='badge bg-light-warning d-block mb-1'>Pending Approval: <br> Supervisor</span>
                        ",
                        2 => "<span class='badge bg-light-warning d-block mb-1'>Pending Approval: <br> (Comm/DD/Dean)</span>",
                        3 => "<span class='badge bg-success d-block mb-1'>Approved & Completed</span>",
                        4 => "<span class='badge bg-danger d-block mb-1'>Rejected: <br> Supervisor</span>",
                        5 => "<span class='badge bg-danger d-block mb-1'>Rejected: <br> (Comm/DD/Dean)</span>",
                        7 => "<span class='badge bg-light-warning d-block mb-1'>Pending: <br> Evaluation</span>",
                        8 => "<span class='badge bg-light-warning d-block mb-1'>Evaluation: <br> Minor/Major Correction</span>",
                        9 => "<span class='badge bg-light-danger d-block mb-1'>Evaluation: <br> Resubmit/Represent</span>",

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
                    $studentActivityId = $row->student_activity_id;
                    $userRoleId = auth()->user()->staff_role; // 4=comm, 5=deputy dean, 6=dean (assumption)

                    // Step 1: Get required signature roles
                    $formFields = DB::table('activity_forms as a')
                        ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                        ->where('a.activity_id', $activityId)
                        ->where('b.ff_category', 6)
                        ->pluck('b.ff_signature_role')
                        ->toArray();

                    $requiredRoles = collect($formFields)->unique()->values()->toArray();

                    $hasCommfield = in_array(4, $requiredRoles);
                    $hasDeputyDeanfield = in_array(5, $requiredRoles);
                    $hasDeanfield = in_array(6, $requiredRoles);

                    // Step 2: Decode signature data
                    $signatureData = json_decode($row->sa_signature_data ?? '[]', true);

                    $hasCommSignature = isset($signatureData['comm_signature_date']);
                    $hasDeputyDeanSignature = isset($signatureData['deputy_dean_signature_date']);
                    $hasDeanSignature = isset($signatureData['dean_signature_date']);

                    $alreadySigned = false;
                    $isRequiredToSign = false;

                    if ($userRoleId == 1) {
                        $alreadySigned = $hasCommSignature;
                        $isRequiredToSign = $hasCommfield;
                    } elseif ($userRoleId == 3) {
                        $alreadySigned = $hasDeputyDeanSignature;
                        $isRequiredToSign = $hasDeputyDeanfield;
                    } elseif ($userRoleId == 4) {
                        $alreadySigned = $hasDeanSignature;
                        $isRequiredToSign = $hasDeanfield;
                    }

                    if ($row->sa_status == 1) {
                        return '
                            <div class="d-flex flex-column gap-2 text-start p-1">
                                <button type="button" class="btn btn-light-success btn-sm w-100"
                                    data-bs-toggle="modal" data-bs-target="#approveModal-' . $studentActivityId . '">
                                    <i class="ti ti-circle-check me-2"></i> Approve
                                </button>

                                <button type="button" class="btn btn-light-danger btn-sm w-100"
                                    data-bs-toggle="modal" data-bs-target="#rejectModal-' . $studentActivityId . '">
                                    <i class="ti ti-circle-x me-2"></i> Reject
                                </button>
                            </div>
                        ';
                    } elseif ($row->sa_status == 4 | $row->sa_status == 5) {
                        return '<span class="fst-italic text-muted">No action required</span>';
                    }

                    // Step 3: Show appropriate action
                    if ($isRequiredToSign && $alreadySigned) {
                        return '
                            <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                            onclick="loadReviews(' . $studentActivityId . ')">
                                <i class="ti ti-eye me-2"></i>
                                <span class="me-2">Review</span>
                            </button>
                        ';
                    }

                    if ($isRequiredToSign && !$alreadySigned) {
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

                            <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                                onclick="loadReviews(' . $studentActivityId . ')">
                                <i class="ti ti-eye me-2"></i>
                                <span class="me-2">Review</span>
                            </button>
                        ';
                    }

                    return '
                            <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                            onclick="loadReviews(' . $studentActivityId . ')">
                                <i class="ti ti-eye me-2"></i>
                                <span class="me-2">Review</span>
                            </button>
                        ';
                });

                $table->rawColumns(['checkbox', 'student_photo', 'sa_final_submission', 'confirm_date', 'sa_status', 'action']);

                return $table->make(true);
            }

            return view('staff.submission.submission-approval', [
                'title' => 'Submission Approval',
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

    private function determineApprovalRoleStatus($supervision, $evaluator, $staffRole, $option)
    {
        // === SUBMISSION APPROVAL (option 1) — leave unchanged ===
        if ($option === 1) {
            if (! $supervision) {
                return match ($staffRole) {
                    1 => [4, 3], // Committee → status 3
                    3 => [5, 3], // Deputy Dean → status 3
                    4 => [6, 3], // Dean → status 3
                    default => [0, 1],
                };
            }
            return match ($supervision->supervision_role) {
                1 => [2, 1], // SV → status 1
                2 => [3, 1], // CoSV → status 1
                default => [0, 1],
            };
        }

        // === CORRECTION APPROVAL (option 2) ===
        if ($option === 2) {
            // 1) Supervisor / CoSV approval first
            if ($supervision) {
                return match ($supervision->supervision_role) {
                    1 => [2, 3], // SV 
                    2 => [3, 3], // CoSV
                    default => [0, 2],
                };
            }
            // 2) Examiner(s)
            if ($evaluator) {
                return [8, 4];   // Examiner
            }
            // 3) Committee / Deputy Dean / Dean
            return match ($staffRole) {
                1 => [4, 5],   // Committee 
                3 => [5, 5],   // Deputy Dean 
                4 => [6, 5],   // Dean 
                default => [0, 4],
            };
        }

        // fallback
        return [0, 2];
    }

    private function determineRejectionRoleStatus($supervision, $evaluator, $staffRole, $option)
    {

        /* ACTIVITY SUBMISSION REJECTION */
        if ($option == 1) {
            if (!$supervision) {
                return match ($staffRole) {
                    1 => [4, 5], // Committee
                    3 => [5, 5], // Deputy Dean
                    4 => [6, 5], // Dean
                    default => [0, 1],
                };
            }

            return match ($supervision->supervision_role) {
                1 => [2, 4], // SV
                2 => [3, 4], // CoSV
                default => [0, 1],
            };
        }
        /* ACTIVITY CORRECTION REJECTION */ elseif ($option == 2) {
            // 1) Supervisor / CoSV approval first
            if ($supervision) {
                return match ($supervision->supervision_role) {
                    1 => [2, 6], // SV 
                    2 => [3, 6], // CoSV
                    default => [0, 6],
                };
            }
            // 2) Examiner(s)
            if ($evaluator) {
                return [8, 7];   // Examiner
            }
            // 3) Committee / Deputy Dean / Dean
            return match ($staffRole) {
                1 => [4, 8],   // Committee 
                3 => [5, 8],   // Deputy Dean 
                4 => [6, 8],   // Dean 
                default => [0, 8],
            };
        }
    }

    public function finalizeSubmission($student, $activityId)
    {
        DB::table('submissions as a')
            ->join('documents as b', 'a.document_id', '=', 'b.id')
            ->join('activities as c', 'b.activity_id', '=', 'c.id')
            ->where('a.student_id', $student->id)
            ->where('c.id', $activityId)
            ->update(['a.submission_status' => 5]);
    }

    public function studentActivitySubmissionApproval(Request $request, $stuActID, $option)
    {
        $stuActID = Crypt::decrypt($stuActID);

        try {
            // Load core data
            $studentActivity = StudentActivity::findOrFail($stuActID);
            $actID = Crypt::encrypt($studentActivity->activity_id);
            $student = Student::findOrFail($studentActivity->student_id);
            $activity = Activity::whereId($studentActivity->activity_id)->first();
            $authUser = auth()->user();
            $afID = ActivityForm::where('activity_id', $studentActivity->activity_id)->where('af_target', 1)->first()?->id;

            if (!$afID) {
                return back()->with('error', 'Activity form not found for this activity.');
            }

            // Check supervision role (SV or CoSV)
            $supervision = Supervision::where('student_id', $student->id)
                ->where('staff_id', $authUser->id)->first();

            // Check procedure for is_haveEva
            $procedure = Procedure::where('activity_id', $activity->id)
                ->where('programme_id', $student->programme_id)
                ->first();
            $isHaveEvaluation = $procedure?->is_haveEva == 1;

            // Check if CoSV required in form
            $hasSvfield = DB::table('activity_forms as a')
                ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                ->where('a.activity_id', $studentActivity->activity_id)
                ->where('b.ff_category', 6)
                ->where('b.ff_signature_role', 2)
                ->where('a.id', $afID)
                ->exists();

            $hasCoSvfield = DB::table('activity_forms as a')
                ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                ->where('a.activity_id', $studentActivity->activity_id)
                ->where('b.ff_category', 6)
                ->where('b.ff_signature_role', 3)
                ->where('a.id', $afID)
                ->exists();

            $hasCoSv = $hasSvfield && $hasCoSvfield;

            // Decode current signature data
            $signatureData = json_decode($studentActivity->sa_signature_data ?? '[]', true);

            // === APPROVAL === //
            if ($option == 1) {

                // Determine role and status code
                [$role, $status] = $this->determineApprovalRoleStatus($supervision, null, $authUser->staff_role, 1);

                // Merge signature
                $this->mergeStudentSubmission($actID, $student, $request->input('signatureData'), $role, $authUser, $status);

                // Save review comment if present
                if ($request->filled('comment')) {
                    SubmissionReview::create([
                        'student_activity_id' => $stuActID,
                        'sr_comment' => $request->input('comment'),
                        'sr_date' => now()->toDateString(),
                        'staff_id' => $authUser->id
                    ]);
                }

                // Reload latest updated data
                $updatedActivity = StudentActivity::findOrFail($stuActID);
                $updatedSignatureData = json_decode($updatedActivity->sa_signature_data ?? '[]', true);

                // Handle SV / CoSV logic
                if (in_array($role, [2, 3])) {
                    $formRoles = DB::table('activity_forms as a')
                        ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                        ->where('a.activity_id', $studentActivity->activity_id)
                        ->where('b.ff_category', 6)
                        ->where('a.id', $afID)
                        ->pluck('b.ff_signature_role')
                        ->unique()
                        ->toArray();

                    $hasHigherRoles = collect($formRoles)->intersect([4, 5, 6])->isNotEmpty();

                    $hasSvSignature = isset($updatedSignatureData['sv_signature']);
                    $hasCoSvSignature = isset($updatedSignatureData['cosv_signature']);

                    if ($hasCoSv) {
                        $allSigned = $hasSvSignature && $hasCoSvSignature;
                    } else {
                        $allSigned = $hasSvSignature;
                    }

                    if ($allSigned) {
                        if (!$hasHigherRoles) {
                            $finalStatus = $isHaveEvaluation ? 7 : 3;
                        } else {
                            $finalStatus = 2;
                        }
                    } else {
                        $finalStatus = 1;
                    }

                    $updatedActivity->update(['sa_status' => $finalStatus]);

                    if ($finalStatus == 3) {
                        $this->finalizeSubmission($student, $studentActivity->activity_id);
                        $this->sendSubmissionNotification($student, 1, $activity->act_name, 6, $role);
                    }
                }

                // Handle Committee / Deputy Dean / Dean logic
                else {

                    $formRoles = DB::table('activity_forms as a')
                        ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                        ->where('a.activity_id', $studentActivity->activity_id)
                        ->where('b.ff_category', 6)
                        ->pluck('b.ff_signature_role')
                        ->where('a.id', $afID)
                        ->unique()->toArray();

                    $roleSignatures = [
                        4 => in_array(4, $formRoles) ? isset($updatedSignatureData['comm_signature_date']) : true,
                        5 => in_array(5, $formRoles) ? isset($updatedSignatureData['deputy_dean_signature_date']) : true,
                        6 => in_array(6, $formRoles) ? isset($updatedSignatureData['dean_signature_date']) : true,
                    ];

                    $allSigned = collect($roleSignatures)->only($formRoles)->every(fn($signed) => $signed);

                    $finalStatus = $allSigned ? ($isHaveEvaluation ? 7 : 3) : 2;
                    $updatedActivity->update(['sa_status' => $finalStatus]);

                    if ($finalStatus == 3) {
                        $this->finalizeSubmission($student, $studentActivity->activity_id);
                        $this->sendSubmissionNotification($student, 1, $activity->act_name, 6, $role);
                    }
                }

                $this->sendSubmissionNotification($student, 1, $activity->act_name, 3, $role);
                return back()->with('success', 'Submission has been approved successfully.');
            }

            // === REJECTION === //
            elseif ($option == 2) {
                [$role, $status] = $this->determineRejectionRoleStatus($supervision, null, $authUser->staff_role, 1);

                $signatureData = json_decode($studentActivity->sa_signature_data ?? '[]', true);
                $keysToRemove = [
                    'sv_signature',
                    'sv_signature_date',
                    'cosv_signature',
                    'cosv_signature_date',
                    'comm_signature',
                    'comm_signature_date',
                    'deputy_dean_signature',
                    'deputy_dean_signature_date',
                    'dean_signature',
                    'dean_signature_date',
                ];
                foreach ($keysToRemove as $key) {
                    unset($signatureData[$key]);
                }

                StudentActivity::whereId($stuActID)->update([
                    'sa_status' => $status,
                    'sa_signature_data' => json_encode($signatureData)
                ]);

                if ($request->filled('comment')) {
                    SubmissionReview::create([
                        'student_activity_id' => $stuActID,
                        'sr_comment' => $request->input('comment'),
                        'sr_date' => now()->toDateString(),
                        'staff_id' => $authUser->id
                    ]);
                }

                $this->sendSubmissionNotification($student, 1, $activity->act_name, 4, $role);
                return back()->with('success', 'Submission has been rejected successfully.');
            }

            // === REVERT === //
            elseif ($option == 3) {
                SubmissionReview::where('student_activity_id', $stuActID)->delete();
                StudentActivity::whereId($stuActID)->delete();

                $this->sendSubmissionNotification($student, 1, $activity->act_name, 5, 0);
                return back()->with('success', 'The student submission has been successfully reverted.');
            }

            return back()->with('error', 'Oops! Invalid option.');
        } catch (Exception $e) {
            return back()->with('error', 'Error occurred: ' . $e->getMessage());
        }
    }

    public function downloadMultipleFinalDocument(Request $req)
    {
        try {
            $submissionIds = json_decode($req->query('ids'), true);

            $option = $req->query('option');

            if (!$submissionIds || count($submissionIds) === 0) {
                return back()->with('error', 'No students selected.');
            }

            // Create ZIP file
            $zipFile = storage_path('app/public/ePGS_SELECTED_STUDENT_FINAL_DOCUMENT.zip');

            if (File::exists($zipFile)) {
                File::delete($zipFile);
            }

            $zip = new ZipArchive;
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return back()->with('error', 'Failed to create ZIP file.');
            }

            if ($option == 1) {
                foreach ($submissionIds as $id) {
                    $submission = DB::table('students as a')
                        ->join('programmes as c', 'c.id', '=', 'a.programme_id')
                        ->join('student_activities as g', 'g.student_id', '=', 'a.id')
                        ->join('activities as f', 'f.id', '=', 'g.activity_id')
                        ->select(
                            'a.*',
                            'c.prog_code',
                            'c.prog_mode',
                            'f.id as activity_id',
                            'f.act_name as activity_name',
                            'g.id as student_activity_id',
                            'g.sa_final_submission',
                        )
                        ->where('g.id', $id)
                        ->first();

                    // STUDENT SUBMISSION DIRECTORY
                    $submission_dir = $submission->student_directory . '/' . $submission->prog_code . '/' . $submission->activity_name . '/Final Document';


                    if (!$submission || empty($submission->sa_final_submission)) {
                        continue;
                    }

                    $folderPath = public_path("storage/" . $submission_dir);

                    if (!File::exists($folderPath)) {
                        continue;
                    }

                    $files = File::allFiles($folderPath);

                    foreach ($files as $file) {
                        if ($submission->sa_final_submission == $file->getFilename()) {
                            $path = Str::upper($submission->activity_name . '/' . $submission->student_matricno . '_' . str_replace(' ', '_', $submission->student_name));
                            $relativePath = $path . '/' . $file->getFilename();
                            $zip->addFile($file->getPathname(), $relativePath);
                        }
                    }
                }
            } elseif ($option == 2) {
                foreach ($submissionIds as $id) {
                    $submission = DB::table('students as a')
                        ->join('programmes as c', 'c.id', '=', 'a.programme_id')
                        ->join('activity_corrections as g', 'g.student_id', '=', 'a.id')
                        ->join('activities as f', 'f.id', '=', 'g.activity_id')
                        ->select(
                            'a.*',
                            'c.prog_code',
                            'c.prog_mode',
                            'f.id as activity_id',
                            'f.act_name as activity_name',
                            'g.id as activity_correction_id',
                            'g.ac_final_submission',
                            'g.semester_id',
                        )
                        ->where('g.id', $id)
                        ->first();


                    // SEMESTER LABEL
                    $currsemester = Semester::find($submission->semester_id);
                    $rawLabel = $currsemester->sem_label;
                    $semesterlabel = str_replace('/', '', $rawLabel);
                    $semesterlabel = trim($semesterlabel);

                    // STUDENT SUBMISSION DIRECTORY
                    $submission_dir = $submission->student_directory . '/' . $submission->prog_code . '/' . $submission->activity_name . '/Correction/' . $semesterlabel;


                    if (!$submission || empty($submission->ac_final_submission)) {
                        continue;
                    }

                    $folderPath = public_path("storage/" . $submission_dir);

                    if (!File::exists($folderPath)) {
                        continue;
                    }

                    $files = File::allFiles($folderPath);

                    foreach ($files as $file) {
                        if ($submission->ac_final_submission == $file->getFilename()) {
                            $path = Str::upper($submission->activity_name . '/' . $submission->student_matricno . '_' . str_replace(' ', '_', $submission->student_name));
                            $relativePath = $path . '/' . $file->getFilename();
                            $zip->addFile($file->getPathname(), $relativePath);
                        }
                    }
                }
            }

            $zip->close();

            return response()->download($zipFile)->deleteFileAfterSend(true);
        } catch (Exception $e) {
            return back()->with('error', 'Error generating ZIP: ' . $e->getMessage());
        }
    }

    /* Submission Approval - Review Functions [Staff] [Committee/DD/DEAN] */
    public function getReview(Request $req)
    {
        try {
            $review = DB::table('submission_reviews as a')
                ->join('staff as b', 'a.staff_id', '=', 'b.id')
                ->where('a.student_activity_id', $req->sa_id)
                ->select('a.id as review_id', 'a.*', 'b.staff_name', 'b.id as staff_table_id')
                ->get();
            return response()->json([
                'success' => true,
                'review' => $review
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Oops! Error getting review: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateReview(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'review_id' => 'required|integer|exists:submission_reviews,id',
            'sr_comment' => 'required|string',
        ], [], [
            'review_id' => 'review',
            'sr_comment' => 'comment',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $validated = $validator->validated();

            SubmissionReview::where('id', $validated['review_id'])->update([
                'sr_comment' => $validated['sr_comment'],
                'sr_date' => now(),
            ]);

            $review = SubmissionReview::where('id', $validated['review_id'])->first();

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully.',
                'review' => $review,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating the review: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteReview(Request $req)
    {
        try {
            $checkExists = SubmissionReview::where('id', $req->review_id)->exists();

            if (!$checkExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found.',
                ], 200);
            }
            SubmissionReview::where('id', $req->review_id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting the review: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* Submission Suggestion */
    public function submissionSuggestion(Request $req)
    {
        try {
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
                    's.student_semcount',
                    'b.sem_label',
                    'c.prog_code',
                    'c.prog_mode',
                    'c.fac_id',
                    's.student_semcount',
                    'p.timeline_sem',
                    'p.programme_id',
                    'a.id as activity_id',
                    'a.act_name as activity_name',
                    'p.act_seq',
                    'p.init_status',
                    DB::raw(
                        'CASE
                        WHEN EXISTS (
                            SELECT 1 FROM student_activities sa_current
                            WHERE sa_current.student_id = s.id
                            AND sa_current.activity_id = p.activity_id
                            AND sa_current.sa_status = 3
                        ) THEN 5
                        WHEN EXISTS (
                            SELECT 1 FROM documents d
                            JOIN submissions sub ON sub.document_id = d.id
                            WHERE d.activity_id = p.activity_id
                            AND sub.student_id = s.id
                            AND sub.submission_status = 5
                        ) THEN 6
                        WHEN EXISTS (
                            SELECT 1 FROM student_activities sa_current
                            WHERE sa_current.student_id = s.id
                            AND sa_current.activity_id = p.activity_id
                        ) THEN 4
                        WHEN EXISTS (
                            SELECT 1 FROM documents d
                            JOIN submissions sub ON sub.document_id = d.id
                            WHERE d.activity_id = p.activity_id
                            AND sub.student_id = s.id
                            AND sub.submission_status IN (1, 4)
                        ) 
                        AND NOT EXISTS (
                            SELECT 1 FROM student_activities sa
                            WHERE sa.student_id = s.id
                            AND sa.activity_id = p.activity_id
                        ) THEN 2
                        WHEN EXISTS (
                            SELECT 1 FROM procedures p_prev
                            WHERE p_prev.programme_id = s.programme_id
                            AND p_prev.act_seq < p.act_seq
                            AND NOT EXISTS (
                                SELECT 1 FROM student_activities sa_prev
                                WHERE sa_prev.student_id = s.id
                                AND sa_prev.activity_id = p_prev.activity_id
                                AND sa_prev.sa_status = 3
                            )
                        ) THEN 3
                        ELSE 1
                    END as suggestion_status'
                    )
                ])
                ->leftJoinSub($latestSemesterSub, 'latest', function ($join) {
                    $join->on('s.id', '=', 'latest.student_id');
                })
                ->leftJoin('student_semesters as ss', function ($join) {
                    $join->on('ss.student_id', '=', 's.id')
                        ->on('ss.semester_id', '=', 'latest.latest_semester_id');
                })
                ->leftJoin('semesters as b', 'b.id', '=', 'ss.semester_id')
                ->join('procedures as p', function ($join) {
                    $join->on('s.programme_id', '=', 'p.programme_id')
                        ->whereRaw('s.student_semcount >= p.timeline_sem')
                        ->where('p.init_status', '=', 2);
                })
                ->join('activities as a', 'p.activity_id', '=', 'a.id')
                ->join('programmes as c', 'c.id', '=', 's.programme_id')
                ->where('s.student_status', '=', 1)
                ->orderBy('s.student_matricno')
                ->orderBy('p.act_seq');

            if ($req->ajax()) {

                if ($req->has('activity') && !empty($req->input('activity'))) {
                    $data->where('a.id', $req->input('activity'));
                }
                if ($req->has('faculty') && !empty($req->input('faculty'))) {
                    $data->where('c.fac_id', $req->input('faculty'));
                }
                if ($req->has('programme') && !empty($req->input('programme'))) {
                    $data->where('p.programme_id', $req->input('programme'));
                }
                if ($req->has('semester') && !empty($req->input('semester'))) {
                    $data->where('semester_id', $req->input('semester'));
                }
                if ($req->has('status') && $req->input('status') !== null && $req->input('status') !== '') {
                    $data->having('suggestion_status', $req->input('status'));
                }

                $data = $data->get();

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('checkbox', function ($row) {

                    if ($row->suggestion_status == 1 || $row->suggestion_status == 2) {
                        return '<input type="checkbox" class="user-checkbox form-check-input" value="' . $row->student_id . '">';
                    } else {
                        return '<input type="checkbox" class="user-checkbox-d form-check-input" disabled>';
                    }
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
                                <small class="text-muted d-block fw-medium"> Enrolled Semesters: ' . $row->student_semcount . '</small>
                            </div>
                        </div>
                    ';
                });

                $table->addColumn('suggestion_status', function ($row) {
                    $status = '';

                    if ($row->suggestion_status == 1) {
                        $status = '<span class="badge bg-light-success">' . 'Eligible' . '</span>';
                    } elseif ($row->suggestion_status == 2) {
                        $status = '<span class="badge bg-success">' . 'Submission Opened' . '</span>';
                    } elseif ($row->suggestion_status == 3) {
                        $status = '<span class="badge bg-light-warning">' . 'Prerequisite Pending' . '</span>';
                    } elseif ($row->suggestion_status == 4) {
                        $status = '<span class="badge bg-light-warning">' . 'Under Review' . '</span>';
                    } elseif ($row->suggestion_status == 5) {
                        $status = '<span class="badge bg-light-secondary">' . 'Completed' . '</span>';
                    } elseif ($row->suggestion_status == 6) {
                        $status = '<span class="badge bg-light-danger">' . 'Submission Archived' . '</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">' . 'N/A' . '</span>';
                    }
                    return $status;
                });

                $table->addColumn('action', function ($row) {
                    $button = '';

                    if ($row->suggestion_status == 1) {
                        $button = '
                            <button type="button" class="btn btn-light-success btn-sm d-flex justify-content-center align-items-center w-100"
                                data-bs-toggle="modal" data-bs-target="#approveModal-' . $row->student_id . $row->activity_id . '">
                                <i class="ti ti-circle-check me-2"></i> Approve
                            </button>
                        ';
                    } elseif ($row->suggestion_status == 2) {
                        $button = '
                            <button type="button" class="btn btn-light-warning btn-sm d-flex justify-content-center align-items-center w-100"
                                data-bs-toggle="modal" data-bs-target="#revertModal-' . $row->student_id . $row->activity_id . '">
                                <i class="ti ti-rotate me-2"></i> Revert
                            </button>
                        ';
                    } else {
                        $button = '<span class="fst-italic text-muted">No Action Required</span>';
                    }

                    return $button;
                });

                $table->rawColumns(['checkbox', 'student_photo', 'suggestion_status', 'action']);

                return $table->make(true);
            }

            $act =  DB::table('activities as a')->join('procedures as b', 'a.id', '=', 'b.activity_id')
                ->select('a.id', 'a.act_name')
                ->where('b.init_status', 2)
                ->orderBy('a.act_name')
                ->distinct()
                ->get();

            return view('staff.submission.submission-suggestion', [
                'title' => 'Submission Suggestion',
                'studs' => Student::all(),
                'progs' => Programme::all(),
                'facs' => Faculty::all(),
                'sems' => Semester::all(),
                'acts' => $act,
                'data' => $data->get(),
            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
            return abort(500, $e->getMessage());
        }
    }

    // ## SEND EMAIL - STUDENT --> Partially done
    public function studentSubmissionSuggestionApproval($studentID, $activityID, $option)
    {
        $studentID = Crypt::decrypt($studentID);
        $activityID = Crypt::decrypt($activityID);

        try {
            $submissions = DB::table('students as a')
                ->join('submissions as b', 'a.id', '=', 'b.student_id')
                ->join('documents as c', 'b.document_id', '=', 'c.id')
                ->join('student_semesters as d', 'a.id', '=', 'd.student_id')
                ->join('semesters as e', 'd.semester_id', '=', 'e.id')
                ->where('a.id', $studentID)
                ->where('c.activity_id', $activityID)
                ->where('d.ss_status', 1)
                ->select('a.programme_id', 'b.*', 'e.id as sem_id', 'e.sem_startdate', 'e.sem_enddate')
                ->get();

            $activity = Activity::whereId($activityID)->first();
            $student = Student::whereId($studentID)->first();

            if ($submissions->isEmpty()) {
                return back()->with('error', 'No submission found for this student.');
            }

            if ($option == 1) {
                /* APPROVE OPENING */
                foreach ($submissions as $sub) {
                    $submission = Submission::whereId($sub->id)->first();
                    $procedures = Procedure::where('programme_id', $sub->programme_id)
                        ->where('activity_id', $activityID)
                        ->where('init_status', 2)
                        ->first();

                    $days = $procedures->timeline_week * 7;
                    $submissionDate = Carbon::parse($sub->sem_startdate)->addDays($days);
                    $submission->submission_duedate = $submissionDate;

                    // DETERMINE SUBMISSION STATUS
                    $sub_status = 1;
                    if (Carbon::parse($submissionDate)->lessThan(now())) {
                        $sub_status = 4;
                    } else {
                        $sub_status = 1;
                    }

                    $submission->submission_status = $sub_status;
                    $submission->save();
                }

                /* NOMINATION OPENING */
                $nom_message = "";
                $procedure = DB::table('procedures as a')
                    ->where('a.programme_id', $student->programme_id)
                    ->where('a.activity_id', $activityID)
                    ->where('a.is_haveEva', 1)
                    ->exists();

                if ($procedure) {
                    Nomination::create([
                        'nom_status' => 1,
                        'student_id' => $studentID,
                        'activity_id' => $activityID,
                        'semester_id' => $sub->sem_id
                    ]);

                    $nom_message = "Take note that nomination is now open for " . $student->student_name . ".";
                }

                // SEND EMAIL SECTION - STUDENT 
                $this->sendSubmissionNotification($student, 1, $activity->act_name, 7, null);

                return back()->with('success', $student->student_name . ' has been approved for ' . $activity->act_name . ' submission. The submission is now open for this student. ' . $nom_message);
            } elseif ($option == 2) {
                /* REVERT SUBMISSION */
                foreach ($submissions as $sub) {
                    $submission = Submission::whereId($sub->id)->first();
                    $submission->submission_status = 2;
                    $submission->submission_document = '-';
                    $submission->save();
                }

                /* REVERT NOMINATION */
                $nom_message = "";
                $procedure = DB::table('procedures as a')
                    ->where('a.programme_id', $student->programme_id)
                    ->where('a.activity_id', $activityID)
                    ->where('a.is_haveEva', 1)
                    ->exists();

                if ($procedure) {
                    Nomination::where('student_id', $studentID)->where('activity_id', $activityID)->delete();
                    $nom_message = "Take note that nomination is now closed for " . $student->student_name . ".";
                }

                // SEND EMAIL SECTION
                $this->sendSubmissionNotification($student, 1, $activity->act_name, 8, null);

                return back()->with('success', $student->student_name . ' submission for ' . $activity->act_name . ' has been reverted. The submission is now hidden for this student. ' . $nom_message);
            } else {
                return back()->with('error', 'Oops! Invalid option. Please try again.');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error approving student submission opening: ' . $e->getMessage());
        }
    }

    // ## SEND EMAIL - STUDENT --> Partially done
    public function multipleStudentSubmissionSuggestionApproval(Request $request)
    {
        $studentIDs = $request->input('selectedIds');
        $activityID = $request->input('activityId');
        $option = $request->input('option');

        try {
            $submissions = DB::table('students as a')
                ->join('submissions as b', 'a.id', '=', 'b.student_id')
                ->join('documents as c', 'b.document_id', '=', 'c.id')
                ->join('student_semesters as d', 'a.id', '=', 'd.student_id')
                ->join('semesters as e', 'd.semester_id', '=', 'e.id')
                ->whereIn('a.id', $studentIDs)
                ->where('c.activity_id', $activityID)
                ->where('d.ss_status', 1)
                ->select('a.id as student_id', 'a.student_name', 'a.student_email', 'a.programme_id', 'b.*', 'e.id as sem_id', 'e.sem_startdate', 'e.sem_enddate')
                ->get();

            $activity = Activity::find($activityID);

            if ($submissions->isEmpty()) {
                return back()->with('error', 'No submission found for the selected students.');
            }

            $studentNames = [];

            foreach ($submissions as $sub) {
                $submission = Submission::find($sub->id);
                $studentNames[] = $sub->student_name;
                $nom_message = "";

                if ($option == 1) {
                    /* APPROVE OPENNING */
                    $procedure = Procedure::where('programme_id', $sub->programme_id)
                        ->where('activity_id', $activityID)
                        ->where('init_status', 2)
                        ->first();

                    if ($procedure) {
                        $dueDate = Carbon::parse($sub->sem_startdate)->addDays($procedure->timeline_week * 7);
                        $submission->submission_duedate = $dueDate;

                        // DETERMINE SUBMISSION STATUS
                        $sub_status = 1;
                        if (Carbon::parse($dueDate)->lessThan(now())) {
                            $sub_status = 4;
                        } else {
                            $sub_status = 1;
                        }
                        $submission->submission_status =  $sub_status;
                    }

                    /* NOMINATION OPENING */
                    $procedureEva = DB::table('procedures as a')
                        ->where('a.programme_id', $sub->programme_id)
                        ->where('a.activity_id', $activityID)
                        ->where('a.is_haveEva', 1)
                        ->exists();

                    if ($procedureEva) {

                        $checkExists = Nomination::where('student_id', $sub->student_id)
                            ->where('activity_id', $activityID)
                            ->exists();

                        if (!$checkExists) {
                            Nomination::create([
                                'nom_status' => 1,
                                'student_id' => $sub->student_id,
                                'activity_id' => $activityID,
                                'semester_id' => $sub->sem_id
                            ]);
                        }

                        $nom_message = "Take note that nomination is now open for this student.";
                    }
                    // SEND EMAIL SECTION - STUDENT
                    $this->sendSubmissionNotification($sub, 1, $activity->act_name, 7, null);
                } elseif ($option == 2) {
                    /* REVERT SUBMISSION */
                    $submission->submission_status = 2;
                    $submission->submission_document = '-';

                    /* REVERT NOMINATION */
                    $procedure = DB::table('procedures as a')
                        ->where('a.programme_id', $sub->programme_id)
                        ->where('a.activity_id', $activityID)
                        ->where('a.is_haveEva', 1)
                        ->exists();

                    if ($procedure) {
                        Nomination::where('student_id', $sub->student_id)->where('activity_id', $activityID)->delete();

                        $nom_message = "Take note that nomination is now closed for this student.";
                    }

                    // SEND EMAIL SECTION - STUDENT
                    $this->sendSubmissionNotification($sub, 1, $activity->act_name, 8, null);
                }

                $submission->save();
            }

            $uniqueNames = implode(', ', array_unique($studentNames));

            if ($option == 1) {
                return response()->json([
                    'success' => true,
                    'message' => "Submission for {$uniqueNames} has been approved for {$activity->act_name}. The submission is now open. " . $nom_message
                ], 200);
            } elseif ($option == 2) {
                return response()->json([
                    'success' => true,
                    'message' => "Submission for {$uniqueNames} has been reverted for {$activity->act_name}. It is now hidden. " . $nom_message
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid option selected.'
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving student submission opening: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Correction Confirmation - Function [Student]  */
    public function confirmStudentCorrection(Request $req, $actID)
    {
        try {
            $actID = decrypt($actID);
            $student = auth()->user();

            if (!$student) {
                return back()->with('error', 'Unauthorized access : Student record is not found.');
            }

            $activity = Activity::where('id', $actID)->first()->act_name;
            $form = ActivityForm::where([
                ['activity_id', $actID],
                ['af_status', 1],
                ['af_target', 2],
            ])->first();

            if (!$form) {
                return back()->with('error', 'Activity form not found. Submission could not be confirmed. Please contact administrator for further assistance.');
            }

            $currsemester = Semester::where('sem_status', 1)->first();

            if (!$currsemester) {
                return back()->with('error', 'No active semester found.');
            }

            $documentName = 'Correction-' . $student->student_matricno . '_' . str_replace(' ', '_', $activity) . '.pdf';

            //---------------------------------------------------------------------------//
            //------------------- SAVE SIGNATURE TO STUDENT_ACTIVITY --------------------//
            //---------------------------------------------------------------------------//

            $signatureData = $req->input('signatureData');

            // 1 - Signature Role [Student]
            // 1 - Document Status [Pending]
            $this->storeCorrectionSignature($actID, $student, $currsemester, $form, $signatureData, $documentName, 1, null, 2);

            //---------------------------------------------------------------------------//
            //--------------------------GENERATE ACTIVITY FORM CODE----------------------//
            //---------------------------------------------------------------------------//

            // RETRIEVE ACTIVITY PATH
            $progcode = strtoupper($student->programmes->prog_code);
            $basePath = storage_path("app/public/{$student->student_directory}/{$progcode}/{$activity}");

            if (!File::exists($basePath)) {
                return back()->with('error', 'Activity folder not found.');
            }

            // CREATE A NEW FOLDER (CORRECTION)
            $rawLabel = $currsemester->sem_label;
            $semesterlabel = str_replace('/', '', $rawLabel);
            $semesterlabel = trim($semesterlabel);
            $finalDocPath = $basePath . '/Correction/' . $semesterlabel;

            if (!File::exists($finalDocPath)) {
                File::makeDirectory($finalDocPath, 0755, true);
            }

            $relativePath = "{$student->student_directory}/{$progcode}/{$activity}/";

            $this->generateCorrectionForm($actID, $student, $currsemester, $form, $relativePath);

            //---------------------------------------------------------------------------//
            //--------------------------MERGE PDF DOCUMENTS CODE-------------------------//
            //---------------------------------------------------------------------------//

            // RETRIEVE PDF FILES
            $pdfFiles = File::files($basePath);

            $pdfFiles = array_filter($pdfFiles, function ($file) {
                return strtolower($file->getExtension()) === 'pdf';
            });

            if (empty($pdfFiles)) {
                return back()->with('error', 'No PDF documents found in the activity folder.' .  $basePath);
            }

            usort($pdfFiles, function ($a, $b) {
                return strcmp($a->getFilename(), $b->getFilename());
            });

            $pdf = new Fpdi();

            foreach ($pdfFiles as $file) {
                $pageCount = $pdf->setSourceFile(StreamReader::createByFile($file->getPathname()));
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $template = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($template);

                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($template);
                }
            }

            // Save the merged file
            $mergedPath = $finalDocPath . '/' . $documentName;
            $pdf->Output($mergedPath, 'F');

            // SEND EMAIL SECTION
            $supervision = DB::table('supervisions as a')
                ->join('staff as b', 'a.staff_id', '=', 'b.id')
                ->where('a.student_id', $student->id)
                ->where('a.supervision_role', 1)
                ->select('b.staff_name', 'b.staff_email')
                ->first();

            if ($supervision) {
                $data = [
                    'student_name' => $student->student_name,
                    'student_matricno' => $student->student_matricno,
                    'submission_date' => Carbon::now()->format('d F Y g:i A'),
                    'staff_name' => $supervision->staff_name,
                    'staff_email' => $supervision->staff_email,
                ];
                // dd($data);
                // $this->sendSubmissionNotification((object)$data, 2, $activity, 2, null);
            }

            return back()->with('success', 'Correction has been confirmed successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error confirming corrections: ' . $e->getMessage());
        }
    }

    /* Document [Correction Form] Handler Function [Start] */
    public function mergeStudentCorrection($actID, $student, $semester, $signatureData, $role, $userName, $status, $evaluatorIndex = null)
    {
        try {
            $actID = decrypt($actID);

            if (!$student) {
                return back()->with('error', 'Unauthorized access : Student record is not found.');
            }

            $activity = Activity::where('id', $actID)->first()->act_name;
            $form = ActivityForm::where([
                ['activity_id', $actID],
                ['af_status', 1],
                ['af_target', 2],
            ])->first();

            if (!$form) {
                return back()->with('error', 'Activity form not found. Submission could not be confirmed. Please contact administrator for further assistance.');
            }

            $documentName = 'Correction-' . $student->student_matricno . '_' . str_replace(' ', '_', $activity) . '.pdf';

            //---------------------------------------------------------------------------//
            //------------------- SAVE SIGNATURE TO STUDENT_ACTIVITY --------------------//
            //---------------------------------------------------------------------------//

            // 1 - Signature Role [Student]
            // 1 - Document Status [Pending]
            $this->storeCorrectionSignature($actID, $student, $semester, $form, $signatureData, $documentName, $role, $userName, $status, $evaluatorIndex);

            //---------------------------------------------------------------------------//
            //--------------------------GENERATE ACTIVITY FORM CODE----------------------//
            //---------------------------------------------------------------------------//

            // RETRIEVE ACTIVITY PATH
            $progcode = strtoupper($student->programmes->prog_code);
            $basePath = storage_path("app/public/{$student->student_directory}/{$progcode}/{$activity}");

            if (!File::exists($basePath)) {
                return back()->with('error', 'Activity folder not found.');
            }

            // CREATE A NEW FOLDER (CORRECTION)
            $rawLabel = $semester->sem_label;
            $semesterlabel = str_replace('/', '', $rawLabel);
            $semesterlabel = trim($semesterlabel);
            $finalDocPath = $basePath . '/Correction/' . $semesterlabel;

            if (!File::exists($finalDocPath)) {
                File::makeDirectory($finalDocPath, 0755, true);
            }

            $relativePath = "{$student->student_directory}/{$progcode}/{$activity}/";

            $this->generateCorrectionForm($actID, $student, $semester, $form, $relativePath);

            //---------------------------------------------------------------------------//
            //--------------------------MERGE PDF DOCUMENTS CODE-------------------------//
            //---------------------------------------------------------------------------//

            // RETRIEVE PDF FILES
            $pdfFiles = File::files($basePath);

            $pdfFiles = array_filter($pdfFiles, function ($file) {
                return strtolower($file->getExtension()) === 'pdf';
            });

            if (empty($pdfFiles)) {
                return back()->with('error', 'No PDF documents found in the activity folder.' .  $basePath);
            }

            $pdf = new Fpdi();

            foreach ($pdfFiles as $file) {
                $pageCount = $pdf->setSourceFile(StreamReader::createByFile($file->getPathname()));
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $template = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($template);

                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($template);
                }
            }

            // SAVE THE MERGED PDF
            $mergedPath =  $finalDocPath . '/' . $documentName;
            return $pdf->Output($mergedPath, 'F');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error confirming submission: ' . $e->getMessage());
        }
    }

    public function generateCorrectionForm($actID, $student, $semester, $form, $finalDocRelativePath)
    {
        try {

            $act = Activity::where('id', $actID)->first();

            if (!$act) {
                return back()->with('error', 'Activity not found.');
            }

            $formfields = FormField::where('af_id', $form->id)
                ->orderBy('ff_order')
                ->get();

            $faculty = Faculty::where('fac_status', 3)->first();
            $signatures = $formfields->where('ff_category', 6);

            $signatureRecord = ActivityCorrection::where([
                ['activity_id', $actID],
                ['student_id', $student->id],
                ['semester_id', $semester->id],
            ])->select('ac_signature_data')->first();

            $signatureData = $signatureRecord ? json_decode($signatureRecord->ac_signature_data) : null;

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
                'corrections' => [
                    'activities' => [
                        'alias' => 'b',
                        'table' => 'activities',
                        'on' => ['a.activity_id', '=', 'b.id'],
                    ],
                    'students' => [
                        'alias' => 'c',
                        'table' => 'students',
                        'on' => ['a.student_id', '=', 'c.id'],
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

                if ($baseTable === 'corrections') {
                    $query->where('a.student_id', $student->id)
                        ->where('a.activity_id', $act->id);
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

            $pdf = Pdf::loadView('student.programme.form-template.activity-document', [
                'title' => "Correction-" . $act->act_name . " Document",
                'act' => $act,
                'form_title' => $form->af_title,
                'formfields' => $formfields,
                'userData' => $userData,
                'faculty' => $faculty,
                'signatures' => $signatures,
                'signatureData' => $signatureData
            ]);

            $fileName = 'Activity_Correction_Form_' . $student->student_matricno . '_' . '.pdf';
            $relativePath = $finalDocRelativePath . '/' . $fileName;

            Storage::disk('public')->put($relativePath, $pdf->output());

            return $pdf->stream($fileName . '.pdf');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error generating correction form: ' . $e->getMessage());
        }
    }


    public function storeCorrectionSignature($actID, $student, $semester, $form, $signatureData, $documentName, $signatureRole, $userData, $status, $evaluatorIndex = null)
    {
        try {
            if (! $signatureData) return;

            $correction = ActivityCorrection::firstOrNew([
                'activity_id' => $actID,
                'student_id' => $student->id,
                'semester_id' => $semester->id,
            ]);

            $existing = $correction->ac_signature_data
                ? json_decode($correction->ac_signature_data, true)
                : [];

            // 1) Load all signature fields for this role, ordered by ff_order:
            $fields = FormField::where('af_id', $form->id)
                ->where('ff_category', 6)
                ->where('ff_signature_role', $signatureRole)
                ->orderBy('ff_order')
                ->get();  // [Field1, Field2, ...]

            // 2) Pick your field:
            if ($signatureRole === 8 && is_int($evaluatorIndex)) {
                // Examiner: use your slot
                $signatureField = $fields->get($evaluatorIndex);
            } else {
                // SV/CoSV/Committee/etc: first empty
                $signatureField = null;
                foreach ($fields as $f) {
                    if (empty($existing[$f->ff_signature_key])) {
                        $signatureField = $f;
                        break;
                    }
                }
            }

            if (! $signatureField) {
                return back()->with(
                    'error',
                    'All required signatures for your role are already completed.'
                );
            }

            $sigKey  = $signatureField->ff_signature_key;
            $dateKey = $signatureField->ff_signature_date_key;

            // 3) Build new block
            if ($signatureRole === 1) {
                $block = [
                    $sigKey        => $signatureData,
                    $dateKey       => now()->format('d M Y'),
                    "{$sigKey}_name" => $student->student_name,
                    "{$sigKey}_role" => 'Student',
                    "{$sigKey}_is_cross_approval" => false,
                ];
            } else {
                $names = [
                    1 => 'Committee',
                    2 => 'Lecturer',
                    3 => 'Deputy Dean',
                    4 => 'Dean'
                ];
                $roleName = $names[$userData->staff_role] ?? 'Staff';
                $block = [
                    $sigKey        => $signatureData,
                    $dateKey       => now()->format('d M Y'),
                    "{$sigKey}_name" => $userData->staff_name,
                    "{$sigKey}_role" => $roleName,
                    "{$sigKey}_is_cross_approval" => false,
                ];
            }

            // 4) Merge + save
            $merged = array_merge($existing, $block);
            $correction->ac_signature_data   = json_encode($merged);
            $correction->ac_final_submission  = $documentName;
            $correction->ac_status            = $status;
            $correction->save();
        } catch (Exception $e) {
            return back()->with('error', 'Error storing signature: ' . $e->getMessage());
        }
    }
    /* Document [Correction Form] Handler Function [End] */


    /* Correction Approval [Staff] [Committee/DD/DEAN] */
    public function correctionApproval(Request $req)
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
                ->select(
                    'a.id as student_id',
                    'a.*',
                    'b.prog_code',
                    'b.prog_mode',
                    'sem.sem_label',
                    'd.id as activity_id',
                    'd.act_name as activity_name',
                    'c.id as activity_correction_id',
                    'c.ac_status',
                    'c.ac_final_submission',
                    'c.ac_signature_data',
                    'c.activity_id',
                    'c.semester_id',
                    'c.updated_at',
                )
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('supervisions as e')
                        ->whereColumn('e.student_id', 'a.id')
                        ->where('e.staff_id', auth()->user()->id);
                })
                ->orderBy('d.act_name');


            if ($req->ajax()) {

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

                    $svname = DB::table('supervisions as a')
                        ->join('staff as b', 'b.id', '=', 'a.staff_id')
                        ->where('a.student_id', $row->student_id)
                        ->where('a.supervision_role', 1)
                        ->select('b.staff_name')
                        ->first()
                        ->staff_name ?? 'N/A';

                    $cosvname = DB::table('supervisions as a')
                        ->join('staff as b', 'b.id', '=', 'a.staff_id')
                        ->where('a.student_id', $row->student_id)
                        ->where('a.supervision_role', 2)
                        ->select('b.staff_name')
                        ->first()
                        ->staff_name ?? 'N/A';


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
                                <small class="text-muted d-block fw-bold mt-2 mb-2">Main Supervisor: <br><span class="fw-normal">' .  $svname . '</span></small>
                                <small class="text-muted d-block fw-bold mb-2">Co-Supervisor: <br><span class="fw-normal">' .  $cosvname . '</span></small>
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
                    // Status codes
                    $PENDING_DECISION = 4;

                    $activityId   = $row->activity_id;
                    $correctionId = $row->activity_correction_id;  // or activity_correction_id
                    $myStaffRole  = auth()->user()->staff_role; // 1=Committee, 3=Deputy Dean, 4=Dean

                    // 1) Map staff_role → ff_signature_role
                    $roleMap = [
                        1 => 4,  // Committee
                        3 => 5,  // Deputy Dean
                        4 => 6,  // Dean
                    ];
                    $myFFRole = $roleMap[$myStaffRole] ?? null;

                    // 2) Which ff_signature_roles does this form require?
                    $requiredFFRoles = DB::table('activity_forms as a')
                        ->join('form_fields as f', 'a.id', '=', 'f.af_id')
                        ->where('a.activity_id',    $activityId)
                        ->where('a.af_target',       2)      // correction form
                        ->where('f.ff_category',     6)      // signature fields
                        ->whereIn('f.ff_signature_role', [4, 5, 6])
                        ->pluck('f.ff_signature_role')
                        ->unique()
                        ->toArray();

                    // 3) Decode existing signatures
                    $sigData = json_decode($row->ac_signature_data ?? '[]', true);

                    // 4) Helper: ff_role → signature_date_key
                    $dateKeyMap = [
                        4 => 'comm_signature_date',
                        5 => 'deputy_dean_signature_date',
                        6 => 'dean_signature_date',
                    ];

                    // 5) Has the entire decision level finished?
                    $levelComplete = collect($requiredFFRoles)->every(function ($ffRole) use ($sigData, $dateKeyMap) {
                        $key = $dateKeyMap[$ffRole] ?? null;
                        return $key && ! empty($sigData[$key]);
                    });

                    // 6) Am I required to sign? And have I already signed?
                    $iAmRequired  = $myFFRole && in_array($myFFRole, $requiredFFRoles, true);
                    $iHaveSigned  = $myFFRole && ! empty($sigData[$dateKeyMap[$myFFRole]] ?? null);

                    // 7) Only in Pending Decision, required, not yet signed, and level not done:
                    if (
                        $row->ac_status === $PENDING_DECISION
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
                            <button class="btn btn-light-danger btn-sm w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#rejectModal-' . $correctionId . '">
                                <i class="ti ti-circle-x me-2"></i>Reject
                            </button>
                        ';
                    }

                    // 8) Otherwise
                    return '<span class="fst-italic text-muted">No action required</span>';
                });

                $table->rawColumns(['checkbox', 'student_photo', 'ac_final_submission', 'confirm_date', 'ac_status', 'action']);

                return $table->make(true);
            }


            return view('staff.submission.correction-approval', [
                'title' => 'Correction Approval',
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

    /* Correction Approval - [Staff] [Examiners/Panels] */
    public function examinerPanelCorrectionApproval(Request $req)
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
                ->select(
                    'a.id as student_id',
                    'a.*',
                    'b.prog_code',
                    'b.prog_mode',
                    'sem.sem_label',
                    'd.id as activity_id',
                    'd.act_name as activity_name',
                    'c.id as activity_correction_id',
                    'c.ac_status',
                    'c.ac_final_submission',
                    'c.ac_signature_data',
                    'c.activity_id',
                    'c.semester_id',
                    'c.updated_at',
                )
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('supervisions as e')
                        ->whereColumn('e.student_id', 'a.id')
                        ->where('e.staff_id', auth()->user()->id);
                })
                ->orderBy('d.act_name');


            if ($req->ajax()) {

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

                    $svname = DB::table('supervisions as a')
                        ->join('staff as b', 'b.id', '=', 'a.staff_id')
                        ->where('a.student_id', $row->student_id)
                        ->where('a.supervision_role', 1)
                        ->select('b.staff_name')
                        ->first()
                        ->staff_name ?? 'N/A';

                    $cosvname = DB::table('supervisions as a')
                        ->join('staff as b', 'b.id', '=', 'a.staff_id')
                        ->where('a.student_id', $row->student_id)
                        ->where('a.supervision_role', 2)
                        ->select('b.staff_name')
                        ->first()
                        ->staff_name ?? 'N/A';


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
                                <small class="text-muted d-block fw-bold mt-2 mb-2">Main Supervisor: <br><span class="fw-normal">' .  $svname . '</span></small>
                                <small class="text-muted d-block fw-bold mb-2">Co-Supervisor: <br><span class="fw-normal">' .  $cosvname . '</span></small>
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
                    $PENDING_EXAMINER = 3;

                    $activityId   = $row->activity_id;
                    $studentId    = $row->student_id;
                    $correctionId = $row->activity_correction_id;
                    $userId       = auth()->user()->id;

                    // 1) Gather all nominated examiners in order
                    $examinerIds = DB::table('nominations as a')
                        ->join('evaluators as b', 'a.id', '=', 'b.nom_id')
                        ->where('a.activity_id', $activityId)
                        ->where('a.student_id',  $studentId)
                        ->where('b.eva_status',   3)
                        ->where('b.eva_role',     1)
                        ->orderBy('b.updated_at')
                        ->pluck('b.staff_id')
                        ->toArray();

                    // 2) If I'm not one of the examiners, no buttons
                    if (! in_array($userId, $examinerIds, true)) {
                        return '<span class="fst-italic text-muted">No action required</span>';
                    }

                    // 3) Find my “slot” (0-based index)
                    $myIndex = array_search($userId, $examinerIds, true);

                    // 4) Lookup all signature keys for examiners
                    $signatureKeys = DB::table('activity_forms as a')
                        ->join('form_fields as f', 'a.id', '=', 'f.af_id')
                        ->where('a.activity_id',       $activityId)
                        ->where('a.af_target',         2)
                        ->where('f.ff_category',       6)
                        ->where('f.ff_signature_role', 8)
                        ->orderBy('f.ff_order')
                        ->pluck('f.ff_signature_key')
                        ->toArray();

                    $myKey     = $signatureKeys[$myIndex] ?? null;
                    $sigData   = json_decode($row->ac_signature_data ?? '[]', true);
                    $hasSigned = $myKey && ! empty($sigData[$myKey]);

                    // 5) Only in PENDING_EXAMINER do we possibly show buttons
                    if ($row->ac_status === $PENDING_EXAMINER && ! $hasSigned) {
                        return '
                        <button class="btn btn-light-success btn-sm mb-1 w-100"
                            data-bs-toggle="modal" data-bs-target="#approveModal-' . $correctionId . '">
                            <i class="ti ti-circle-check me-2"></i>Approve
                        </button>
                        <button class="btn btn-light-danger btn-sm w-100"
                            data-bs-toggle="modal" data-bs-target="#rejectModal-' . $correctionId . '">
                            <i class="ti ti-circle-x me-2"></i>Reject
                        </button>
                    ';
                    }

                    // 6) In all other cases, show "No action required"
                    return '<span class="fst-italic text-muted">No action required</span>';
                });

                $table->rawColumns(['checkbox', 'student_photo', 'ac_final_submission', 'confirm_date', 'ac_status', 'action']);

                return $table->make(true);
            }

            return view('staff.correction.examiner-correction-approval', [
                'title' => 'Correction Approval',
                'studs' => Student::all(),
                'progs' => Programme::all(),
                'facs' => Faculty::all(),
                'sems' => Semester::all(),
                'acts' => Activity::all(),
                'subs' => $data->get(),
            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
            return abort(500, $e->getMessage());
        }
    }

    /* Correction Approval - Function [Staff]  */
    public function studentActivityCorrectionApproval(Request $request, $actCorrID, $option)
    {
        $actCorrID = Crypt::decrypt($actCorrID);

        try {
            /* LOAD ACTIVITY CORRECTION DATA */
            $activityCorrection = ActivityCorrection::findOrFail($actCorrID);

            /* LOAD ACTIVITY DATA */
            $actID = Crypt::encrypt($activityCorrection->activity_id); // ENCRYPT ACTIVITY ID
            $activity = Activity::whereId($activityCorrection->activity_id)->first();

            /* LOAD STUDENT DATA */
            $student = Student::findOrFail($activityCorrection->student_id);
            $authUser = auth()->user();

            /* LOAD SEMESTER DATA */
            $semester = Semester::findOrFail($activityCorrection->semester_id);

            /* LOAD ACTIVITY FORM DATA */
            $afID = ActivityForm::where('activity_id', $activityCorrection->activity_id)->where('af_target', 2)->first()?->id;
            if (!$afID) {
                return back()->with('error', 'Activity form not found for this activity.');
            }

            /* CHECK EVALUATORS */
            $allExaminers = DB::table('nominations as a')
                ->join('evaluators as b', 'a.id', '=', 'b.nom_id')
                ->where('a.activity_id', $activityCorrection->activity_id)
                ->where('a.student_id', $student->id)
                ->where('b.eva_status', 3)
                ->where('b.eva_role', 1)
                ->orderBy('b.updated_at')
                ->pluck('b.staff_id')
                ->toArray();

            $evaluatorIndex = array_search($authUser->id, $allExaminers, true);
            if ($evaluatorIndex === false) {
                $evaluatorIndex = null;
            }

            $evaluator = DB::table('nominations as a')
                ->join('evaluators as b', 'a.id', '=', 'b.nom_id')
                ->where('a.activity_id', $activityCorrection->activity_id)
                ->where('a.student_id', $student->id)
                ->where('b.eva_status', 3)
                ->where('b.eva_role', 1)
                ->where('b.staff_id', $authUser->id)
                ->first();

            /* CHECK SUPERVISION ROLE (SV OR COSV) */
            $supervision = Supervision::where('student_id', $student->id)
                ->where('staff_id', $authUser->id)->first();

            /* CHECK IF SV's REQUIRED IN FORM */
            $hasSvfield = DB::table('activity_forms as a')
                ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                ->where('a.id', $afID)
                ->where('b.ff_category', 6)
                ->where('b.ff_signature_role', 2)
                ->exists();

            /* CHECK IF COSV's REQUIRED IN FORM */
            $hasCoSvfield = DB::table('activity_forms as a')
                ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                ->where('a.id', $afID)
                ->where('b.ff_category', 6)
                ->where('b.ff_signature_role', 3)
                ->exists();

            $hasCoSv = $hasSvfield && $hasCoSvfield;

            // === APPROVAL === //
            if ($option == 1) {

                /* INDICATOR [STATUS] */
                // 1: CORRECTION : PENDING STUDENT ACTION
                // 2: CORRECTION : PENDING SUPERVISION ACTION
                // 3: CORRECTION : PENDING EXAMINER/PANEL ACTION
                // 4: CORRECTION : PENDING COMM/DD/DEAN ACTION
                // 5: CORRECTION : APPROVE & COMPLETED


                /* INDICATOR [ROLE] */
                // 1: STUDENT [NOT APPLICABLE FOR THIS LOGIC]
                // 2: MAIN SUPERVISOR
                // 3: COSV
                // 8: EXAMINER/PANEL (EXAMINER 1 / PANEL 1 / EXAMINER 2 / PANEL 2 .. etc)
                // 4: COMMITTEE
                // 5: DEPUTY DEAN
                // 6: DEAN

                /* INDICATOR [ROLE] AND STATUS AFTER APPROVAL */
                // 2: MAIN SUPERVISOR [3]
                // 3: COSV [3]
                // 8: EXAMINER/PANEL (EXAMINER 1 / PANEL 1 / EXAMINER 2 / PANEL 2 .. etc) [4]
                // 4: COMMITTEE [5]
                // 5: DEPUTY DEAN [5]
                // 6: DEAN [5]

                /* DETERMINE APPROVAL STATUS BASED ON ROLE */
                [$role, $status] = $this->determineApprovalRoleStatus($supervision, $evaluator, $authUser->staff_role, 2);

                /* MERGE SIGNATURE DATA AND GENERATE CORRECTION FORM */
                $this->mergeStudentCorrection(
                    $actID,
                    $student,
                    $semester,
                    $request->input('signatureData'),
                    $role,
                    $authUser,
                    $status,
                    $evaluatorIndex
                );

                /* LOAD UPDATED DATA */
                $updatedCorrection = ActivityCorrection::findOrFail($actCorrID);
                $updatedSignatureData = json_decode($updatedCorrection->ac_signature_data ?? '[]', true);

                /* HANDLE SV / COSV LOGIC [SV: 2, COSV: 3] */
                if (in_array($role, [2, 3])) {
                    $formRoles = DB::table('activity_forms as a')
                        ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                        ->where('a.id', $afID)
                        ->where('b.ff_category', 6)
                        ->pluck('b.ff_signature_role')
                        ->unique()->toArray();

                    $hasHigherRoles = collect($formRoles)->intersect([4, 5, 6, 8])->isNotEmpty();

                    $hasSvSignature = isset($updatedSignatureData['sv_signature']);
                    $hasCoSvSignature = isset($updatedSignatureData['cosv_signature']);

                    if ($hasCoSv) {
                        $allSigned = $hasSvSignature && $hasCoSvSignature;
                    } else {
                        $allSigned = $hasSvSignature;
                    }

                    if ($allSigned) {
                        if (!$hasHigherRoles) {
                            $finalStatus = 5;
                        } else {
                            $finalStatus = 3;
                        }
                    } else {
                        $finalStatus = 2;
                    }

                    $updatedCorrection->update(['ac_status' => $finalStatus]);

                    if ($finalStatus == 5) {
                        $this->finalizeCorrection($student, $updatedCorrection, $activityCorrection->activity_id);
                    }
                }
                /* HANDLE EXAMINER/PANEL LOGIC */ elseif ($role == 8) {
                    // 1) Load all roles from the form
                    $formRoles = DB::table('activity_forms as a')
                        ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                        ->where('a.id', $afID)
                        ->where('b.ff_category', 6)
                        ->pluck('b.ff_signature_role')
                        ->unique()
                        ->toArray();

                    // 2) Check if there *are* any higher‐level approvers
                    $hasHigherRoles = collect($formRoles)
                        ->intersect([4, 5, 6])   // Committee=4, Deputy Dean=5, Dean=6
                        ->isNotEmpty();

                    // 3) Check all examiners have signed
                    $examKeys = DB::table('form_fields')
                        ->where('af_id', $afID)
                        ->where('ff_category', 6)
                        ->where('ff_signature_role', 8)
                        ->pluck('ff_signature_key')
                        ->toArray();

                    $allSigned = collect($examKeys)->every(
                        fn($key) =>
                        isset($updatedSignatureData[$key]) && !empty($updatedSignatureData[$key])
                    );

                    // 4) Determine new status
                    if (! $allSigned) {
                        // still waiting on at least one examiner
                        $newStatus = 3;
                    } elseif ($hasHigherRoles) {
                        // examiners done, move to Committee/DD/Dean
                        $newStatus = 4;
                    } else {
                        // examiners were the last approvers → complete
                        $newStatus = 5;
                    }

                    // 5) Persist and finalize if complete
                    $updatedCorrection->update(['ac_status' => $newStatus]);

                    if ($newStatus === 5) {
                        $this->finalizeCorrection($student, $updatedCorrection, $activityCorrection->activity_id);
                    }
                }
                /* HANDLE COMM/DD/DEAN LOGIC */ elseif (in_array($role, [4, 5, 6])) {

                    $formRoles = DB::table('activity_forms as a')
                        ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                        ->where('a.id', $afID)
                        ->where('b.ff_category', 6)
                        ->pluck('b.ff_signature_role')
                        ->unique()->toArray();

                    $roleSignatures = [
                        4 => in_array(4, $formRoles) ? isset($updatedSignatureData['comm_signature_date']) : true,
                        5 => in_array(5, $formRoles) ? isset($updatedSignatureData['deputy_dean_signature_date']) : true,
                        6 => in_array(6, $formRoles) ? isset($updatedSignatureData['dean_signature_date']) : true,
                    ];

                    $allSigned = collect($roleSignatures)->only($formRoles)->every(fn($signed) => $signed);

                    $finalStatus = $allSigned ? 5 : 4;
                    $updatedCorrection->update(['ac_status' => $finalStatus]);

                    if ($finalStatus == 5) {
                        $this->finalizeCorrection($student, $updatedCorrection, $activityCorrection->activity_id);
                    }
                }
                return back()->with('success', 'Correction for ' . $student->student_name . ' has been approved successfully.');
            }
            // === REJECTION ===
            elseif ($option == 2) {
                // 1) figure out which status to go to
                [$role, $status] = $this->determineRejectionRoleStatus(
                    $supervision,
                    $evaluator,
                    $authUser->staff_role,
                    2
                );

                // 2) clear every signature slot
                ActivityCorrection::whereId($actCorrID)
                    ->update([
                        'ac_status'         => $status,
                        'ac_signature_data' => json_encode([]),
                    ]);

                return back()->with('success', 'Correction for ' . $student->student_name . ' has been rejected successfully.');
            }
            // === REVERT ===
            elseif ($option == 3) {
                ActivityCorrection::whereId($actCorrID)
                    ->update([
                        'ac_status'         => 1,           // back to “Pending Student Action”
                        'ac_signature_data' => json_encode([]),
                    ]);

                return back()->with('success', 'Correction for ' . $student->student_name . ' has been successfully reverted.');
            }

            return back()->with('error', 'Oops! Invalid option.');
        } catch (Exception $e) {
            return back()->with('error', 'Error occurred: ' . $e->getMessage());
        }
    }

    /* Finalize Correction - Function [Staff]  */
    public function finalizeCorrection($student, $correction, $activityID)
    {
        DB::table('submissions as a')
            ->join('documents as b', 'a.document_id', '=', 'b.id')
            ->join('activities as c', 'b.activity_id', '=', 'c.id')
            ->where('a.student_id', $student->id)
            ->where('c.id', $activityID)
            ->update(['a.submission_status' => 5]);

        $studentactivity = StudentActivity::where('student_id', $student->id)
            ->where('activity_id', $activityID)
            ->first();

        if (!$studentactivity) {
            return back()->with('error', 'Error occurred: Student activity not found.');
        }

        // SEMESTER LABEL
        $currsemester = Semester::find($correction->semester_id);
        $rawLabel = $currsemester->sem_label;
        $semesterlabel = str_replace('/', '', $rawLabel);
        $semesterlabel = trim($semesterlabel);

        $corrPath = '../Correction/' . $semesterlabel . '/' . $correction->ac_final_submission;

        $studentactivity->update(['sa_status' => 3, 'sa_final_submission' => $corrPath]);
    }
}
