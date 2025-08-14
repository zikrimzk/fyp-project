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
use App\Models\JournalPublication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use setasign\Fpdi\PdfParser\StreamReader;
use App\Http\Controllers\FormHandlerController;


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

    /* Programme Overview [Student] - Route */
    public function studentProgrammeOverview()
    {
        try {
            /* LOAD CURRENT SEMESTER */
            $currsemester = Semester::where('sem_status', 1)->first();

            if (!$currsemester) {
                return abort(404, 'Semester not found. Could not process request. Please contact administrator for further assistance.');
            }

            /* LOAD PROGRAMME ACTIVITY */
            $programmeActivity = DB::table('procedures as a')
                ->join('programmes as b', 'a.programme_id', '=', 'b.id')
                ->join('activities as c', 'a.activity_id', '=', 'c.id')
                ->where('b.id', auth()->user()->programme_id)
                ->orderBy('act_seq')
                ->get();

            /* LOAD DOCUMENT DATA */
            $document = DB::table('procedures as a')
                ->join('programmes as b', 'a.programme_id', '=', 'b.id')
                ->join('activities as c', 'a.activity_id', '=', 'c.id')
                ->join('documents as d', 'c.id', '=', 'd.activity_id')
                ->join('submissions as e', 'd.id', '=', 'e.document_id')
                ->join('semesters as f', 'e.semester_id', '=', 'f.id')
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
                    'f.sem_label'
                )
                ->get()
                ->groupBy('activity_id');

            /* LOAD STUDENT ACTIVITY DATA */
            $student_activity = StudentActivity::where('student_id', auth()->user()->id)->get();

            /* LOAD DOCUMENT DATA WITH DIFFERENT CONDITION */
            $documentQueryTwo = DB::table('documents as a')
                ->join('activities as act', 'a.activity_id', '=', 'act.id')
                ->join('submissions as b', 'a.id', '=', 'b.document_id')
                ->where('b.student_id', auth()->user()->id)
                ->where('b.submission_status', '!=', 5)
                ->select(
                    'a.activity_id',
                    'a.isRequired',
                    'b.submission_status'
                )
                ->get();

            /* LOAD SUBMISSION REVIEW DATA */
            $submissionReview = DB::table('submission_reviews as a')
                ->join('staff as b', 'a.staff_id', '=', 'b.id')
                ->select('a.id as review_id', 'a.*', 'b.staff_name')
                ->get();

            /* LOAD ACTIVITY CORRECTIONS DATA */
            $activityCorrections = DB::table('activity_corrections')
                ->where('student_id', auth()->user()->id)
                ->get();

            /* LOAD EVALUATION REPORTS DATA */
            $evaluationReport = Evaluation::where('student_id', auth()->user()->id)
                ->where('evaluation_isFinal', 1)
                ->get();

            /* PROCESS ALL DATA */
            foreach ($programmeActivity as $activity) {
                $activitySubmissions = $document->get($activity->activity_id);

                /* CHECK FOR REPEATABLE ACTIVITY */
                if ($activity->is_repeatable == 1) {

                    /* CHECK STUDENT ACTIVITY WITH CURRENT SEMESTER */
                    $studentAct = $student_activity
                        ->where('activity_id', $activity->activity_id)
                        ->where('semester_id', $currsemester->id)
                        ->first();
                } else {

                    /* CHECK STUDENT ACTIVITY WITHOUT CURRENT SEMESTER */
                    $studentAct = $student_activity
                        ->firstWhere('activity_id', $activity->activity_id);
                }

                /* GET TOTAL REQUIRED DOCUMENT */
                $requiredDocument = $documentQueryTwo
                    ->where('activity_id', $activity->activity_id)
                    ->where('isRequired', 1)->count();

                /* GET TOTAL OPTIONAL DOCUMENT */
                $optionalDocument = $documentQueryTwo
                    ->where('activity_id', $activity->activity_id)
                    ->where('isRequired', 0)->count();

                /* GET TOTAL SUBMITTED REQUIRED DOCUMENT */
                $submittedRequiredDocument = $documentQueryTwo
                    ->where('activity_id', $activity->activity_id)
                    ->where('isRequired', 1)
                    ->where('submission_status', 3)->count();

                /* GET TOTAL SUBMITTED OPTIONAL DOCUMENT */
                $submittedOptionalDocument = $documentQueryTwo
                    ->where('activity_id', $activity->activity_id)
                    ->where('isRequired', 0)
                    ->where('submission_status', 3)->count();

                /* FETCH ACTIVITY CORRECTION DATA */
                $activityCorrection = $activityCorrections->firstWhere('activity_id', $activity->activity_id);

                if ($activityCorrection) {
                    /* MAP ACTIVITY CORRECTION STATUS */
                    $correctionStatusMap = [
                        1 => 8,
                        2 => 14,
                        3 => 15,
                        4 => 16,
                        5 => 17,
                        6 => 18,
                        7 => 19,
                        8 => 20,
                    ];

                    /* SET INIT STATUS WITH MAPPED VALUE */
                    $activity->init_status = $correctionStatusMap[$activityCorrection->ac_status] ?? 10;

                    /* SET DATA */
                    $activity->ac_semester_id = $activityCorrection->semester_id;

                    /* HANDLE CORRECTION DOCUMENT PATH */
                    $correctionSemester = Semester::find($activityCorrection->semester_id);
                    $semesterlabel = trim(str_replace('/', '', $correctionSemester->sem_label));

                    if ($activityCorrection->ac_final_submission) {
                        $activity->confirmed_corrected_document = $semesterlabel . '/' . $activityCorrection->ac_final_submission;
                    }
                } elseif ($studentAct) {
                    /* SET STUDENT ACTIVITY DATA WITH RESPECTED ATTRIBUTES */
                    $activity->init_status = $studentAct->sa_status;
                    $activity->confirmed_document = $studentAct->sa_final_submission;
                    $activity->sa_semester_id = $studentAct->semester_id;
                } else {
                    /* SHOW DEFAULT INIT STATUS */
                    if ($activitySubmissions) {
                        $lockedSubmission = $activitySubmissions->firstWhere('submission_status', 2);
                        $activity->init_status = $lockedSubmission ? 11 : 10;
                    } else {
                        $activity->init_status = 11;
                    }
                }

                /* ASSIGN DOCUMENT COUNT IN OBJECT */
                $activity->required_document = $requiredDocument;
                $activity->optional_document = $optionalDocument;
                $activity->submitted_required_document = $submittedRequiredDocument;
                $activity->submitted_optional_document = $submittedOptionalDocument;
                $activity->student_activity_id = $studentAct->id ?? null;
            }

            /* FILTER SUBMISSION DATA THAT HAVE LOCKED[2] AND ARCHIVE[5] STATUS */
            $filtered_documents = $document->map(function ($activityGroup) {
                return $activityGroup->filter(function ($submission) {
                    return !in_array($submission->submission_status, [2, 5]);
                });
            });

            /* RETURN VIEW */
            return view('student.programme.programme-index', [
                'title' => 'Programme Overview',
                'acts' => $programmeActivity,
                'docs' => $filtered_documents,
                'sa' => $student_activity,
                'submissionReview' => $submissionReview,
                'evaluationReport' => $evaluationReport,
            ]);
        } catch (Exception $e) {
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

    /* Confirm Student Submission [Student] - Function | Email : Yes */
    public function confirmStudentSubmission(Request $req, $actID)
    {
        try {
            /* INITIALIZE VARIABLES */
            $actID = decrypt($actID);

            /* LOAD STUDENT DATA */
            $student = auth()->user();

            if (!$student) {
                return back()->with('error', 'Unauthorized access : Student record is not found.');
            }

            /* LOAD SEMESTER DATA */
            $currentSemester = Semester::where('sem_status', 1)->first();

            if (!$currentSemester) {
                return back()->with('error', 'Semester not found. Submission could not be confirmed. Please contact administrator for further assistance.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $actID)->first()->act_name;

            if (!$activity) {
                return back()->with('error', 'Activity not found. Submission could not be confirmed. Please contact administrator for further assistance.');
            }

            /* ENCRYPT ACTIVITY ID */
            $activityID = encrypt($actID);

            /* SET SIGNATURE DATA */
            $signatureData = $req->input('signatureData');

            /* 
            * MERGE AND PROCESS SIGNATURE
            * Signature Role : 1 [STUDENT]
            * status : 1 [PENDING]
            * type : 1 [ACTIVITY FORM]
            */
            $this->mergeStudentSubmission($activityID, $student, $currentSemester, $signatureData, 1, null, 1, 1, null, null, null);

            /* SEND EMAIL CONFIRMATION TO SUPERVISOR */
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
                $this->sendSubmissionNotification((object)$data, 2, $activity, 2, null);
            }

            return back()->with('success', 'Submission for ' . $activity . ' has been confirmed successfully. An email has been sent to the supervisor. If there are any issues, please contact the administrator.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error confirming submission: ' . $e->getMessage());
        }
    }

    /* Correction Student Confirmation [Student] - Function | Email : Yes With Works  */
    public function confirmStudentCorrection(Request $req, $actID)
    {
        try {

            /* INITIALIZE VARIABLES */
            $actID = decrypt($actID);

            /* LOAD STUDENT DATA */
            $student = auth()->user();

            if (!$student) {
                return back()->with('error', 'Unauthorized access : Student record is not found.');
            }

            /* LOAD SEMESTER DATA */
            $currentSemester = Semester::where('sem_status', 1)->first();

            if (!$currentSemester) {
                return back()->with('error', 'Semester not found. Submission could not be confirmed. Please contact administrator for further assistance.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $actID)->first()->act_name;

            if (!$activity) {
                return back()->with('error', 'Activity not found. Submission could not be confirmed. Please contact administrator for further assistance.');
            }

            /* ENCRYPT ACTIVITY ID */
            $activityID = encrypt($actID);

            /* SET SIGNATURE DATA */
            $signatureData = $req->input('signatureData');

            /* 
            * MERGE AND PROCESS SIGNATURE
            * Signature Role : 1 [STUDENT]
            * status : 2 [PENDING]
            * type : 2 [CORRECTION FORM]
            */
            $this->mergeStudentSubmission($activityID, $student, $currentSemester, $signatureData, 1, null, 2, 2, null, null, null);

            /* SEND EMAIL CONFIRMATION TO SUPERVISOR */
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

                // $this->sendSubmissionNotification((object)$data, 2, $activity, 2, null);
            }

            return back()->with('success', 'Correction for ' . $activity . ' has been confirmed successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error confirming submission: ' . $e->getMessage());
        }
    }

    /* Merge and Handle Activity Form Document [Student] - Function */
    public function mergeStudentSubmission($actID, $student, $semester, $signatureData, $role, $userName, $status, $type, $evaluatorIndex = null, $studentActObject = null, $activityCorrectionObject = null)
    {
        try {

            /* 
             * HANDLE TYPE
             * 1 : Activity Form
             * 2 : Correction Form
             */
            $aftarget = 0;

            if ($type == 1) {
                $aftarget = 1;
            } else if ($type == 2) {
                $aftarget = 2;
            } else {
                return back()->with('error', 'Invalid operation. Could not merge submission. Please contact administrator for further assistance.');
            }

            /* HANDLE UNAUTHORIZED ACCESS */
            if (!$student) {
                return back()->with('error', 'Unauthorized access : Student record is not found.');
            }

            /* LOAD ACTIVITY DATA */
            $actID = decrypt($actID);
            $activity = Activity::where('id', $actID)->first()->act_name;

            if (!$activity) {
                return back()->with('error', 'Activity not found. Could not merge submission. Please contact administrator for further assistance.');
            }

            /* LOAD ACTIVITY FORM DATA */
            $form = ActivityForm::where([
                ['activity_id', $actID],
                ['af_status', 1],
                ['af_target', $aftarget],
            ])->first();

            if (!$form) {
                return back()->with('error', 'Activity form not found. Could not merge submission. Please contact administrator for further assistance.');
            }

            /* LOAD PROCEDURE DATA */
            $procedure = Procedure::where([
                'activity_id' => $actID,
                'programme_id' => $student->programme_id
            ])->first();

            if (!$procedure) {
                return back()->with('error', 'Procedure not found. Could not merge submission. Please contact administrator for further assistance.');
            }

            /* SEMESTER LABEL CONVERSION */
            $rawLabel = $semester->sem_label;
            $semesterlabel = str_replace('/', '', $rawLabel);
            $semesterlabel = trim($semesterlabel);

            /* SET DOCUMENT NAME */
            if ($procedure->is_repeatable == 1) {
                if ($type == 1) {
                    $documentName = $semesterlabel . '-' . $student->student_matricno . '_' . str_replace(' ', '_', $activity) . '.pdf';
                } elseif ($type == 2) {
                    $documentName = $semesterlabel . '-Correction_' . $student->student_matricno . '_' . str_replace(' ', '_', $activity) . '.pdf';
                }
            } else {
                if ($type == 1) {
                    $documentName = $student->student_matricno . '_' . str_replace(' ', '_', $activity) . '.pdf';
                } elseif ($type == 2) {
                    $documentName = 'Correction_' . $student->student_matricno . '_' . str_replace(' ', '_', $activity) . '.pdf';
                }
            }

            //---------------------------------------------------------------------------//
            //------------------- SAVE SIGNATURE TO STUDENT_ACTIVITY --------------------//
            //---------------------------------------------------------------------------//

            /* 
            * STORE SIGNATURE
            * role : Signature Role
            * status : Document Status
            * type : Document Type
            */

            $this->storeSignature($actID, $student, $semester, $form, $signatureData, $documentName, $role, $userName, $status, $type, $evaluatorIndex, null, $studentActObject, $activityCorrectionObject);

            //---------------------------------------------------------------------------//
            //--------------------------GENERATE ACTIVITY FORM CODE----------------------//
            //---------------------------------------------------------------------------//

            /* LOAD ACTIVITY DIRECTORY */
            $progcode = strtoupper($student->programmes->prog_code);

            if ($procedure->is_repeatable == 1) {
                $basePath = storage_path("app/public/{$student->student_directory}/{$progcode}/{$activity}/{$semesterlabel}");
                $relativePath = "{$student->student_directory}/{$progcode}/{$activity}/{$semesterlabel}/";
            } else {
                $basePath = storage_path("app/public/{$student->student_directory}/{$progcode}/{$activity}");
                $relativePath = "{$student->student_directory}/{$progcode}/{$activity}/";
            }

            if (!File::exists($basePath)) {
                return back()->with('error', 'Activity folder not found. Could not merge submission. Please contact administrator for further assistance.');
            }

            /* CREATE NEW DIRECTORY */
            if ($type == 1) {
                $finalDocPath = $basePath . '/Final Document';
            } elseif ($type == 2) {
                if ($procedure->is_repeatable == 1) {
                    $finalDocPath = $basePath . '/Correction';
                } else {
                    $finalDocPath = $basePath . '/Correction/' . $semesterlabel;
                }
            }

            if (!File::exists($finalDocPath)) {
                File::makeDirectory($finalDocPath, 0755, true);
            }

            /* GENERATE ACTIVITY FORM FUNCTION */
            $this->generateActivityForm($actID, $student, $semester, $form, $relativePath, $type);

            //---------------------------------------------------------------------------//
            //--------------------------MERGE PDF DOCUMENTS CODE-------------------------//
            //---------------------------------------------------------------------------//

            /* LOAD PDF FILES */
            $pdfFiles = File::files($basePath);
            $pdfFiles = array_filter($pdfFiles, function ($file) {
                return strtolower($file->getExtension()) === 'pdf';
            });

            if (empty($pdfFiles)) {
                return back()->with('error', 'No PDF documents found in the activity folder.' .  $basePath);
            }

            /* LOAD FPDI LIBRARY FOR MERGING */
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

            /* SAVE MERGED FILE */
            $mergedPath =  $finalDocPath . '/' . $documentName;
            $pdf->Output($mergedPath, 'F');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error occurred during handling activity form: ' . $e->getMessage());
        }
    }

    /* Store Activity Form Signature [Student] - Function */
    public function storeSignature($actID, $student, $semester, $form, $signatureData, $documentName, $signatureRole, $userData, $status, $type, $evaluatorIndex = null, $evaluation = null, $studentActObject = null, $activityCorrectionObject = null)
    {
        try {
            if ($signatureData) {

                if ($type == 1) {
                    /* ACTIVITY FORM SIGNATURE */

                    /* LOAD SIGNATURE FIELD DATA */
                    $signatureField = FormField::where([
                        ['af_id', $form->id],
                        ['ff_category', 6],
                        ['ff_signature_role', $signatureRole]
                    ])->first();

                    /* INITIALIZE STUDENT ACTIVITY */

                    if ($studentActObject) {
                        $studentActivity = $studentActObject;
                    } else {
                        $studentActivity = StudentActivity::firstOrNew([
                            'activity_id' => $actID,
                            'student_id' => $student->id,
                            'semester_id' => $semester->id
                        ]);
                    }

                    /* STORE SIGNATURE LOGIC */
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

                        if ($signatureRole == 1) {
                            $mergedSignatureData = $newSignatureData;
                        } else {
                            $mergedSignatureData = array_merge($existingSignatureData, $newSignatureData);
                        }

                        /* MERGE AND STORE SIGNATURE DATA */
                        $studentActivity->sa_signature_data = json_encode($mergedSignatureData);
                        $studentActivity->sa_final_submission = $documentName;
                        $studentActivity->sa_status = $status;
                        $studentActivity->save();
                    }
                } elseif ($type == 2) {
                    /* CORRECTION FORM SIGNATURE */

                    /* LOAD SIGNATURE FIELD DATA */
                    $signatureFields = FormField::where('af_id', $form->id)
                        ->where('ff_category', 6)
                        ->where('ff_signature_role', $signatureRole)
                        ->orderBy('ff_order')
                        ->get();

                    /* INITIALIZE ACTIVITY CORRECTION */
                    if ($activityCorrectionObject) {
                        $correction = $activityCorrectionObject;
                    } else {
                        $correction = ActivityCorrection::firstOrNew([
                            'activity_id' => $actID,
                            'student_id' => $student->id,
                        ]);
                    }

                    /* STORE SIGNATURE LOGIC */
                    $existing = $correction->ac_signature_data
                        ? json_decode($correction->ac_signature_data, true)
                        : [];

                    if ($signatureRole === 8 && is_int($evaluatorIndex)) {
                        /* GET EXAMINER */
                        $signatureField = $signatureFields->get($evaluatorIndex);
                    } else {
                        /* GET SV/COSV/COMMITTEE/DEPUTY DEAN/DEAN */
                        $signatureField = null;
                        foreach ($signatureFields as $f) {
                            if (empty($existing[$f->ff_signature_key])) {
                                $signatureField = $f;
                                break;
                            }
                        }
                    }

                    /* CHECK IF ALL REQUIRED SIGNATURES ARE COMPLETED */
                    if (! $signatureField) {
                        return back()->with(
                            'error',
                            'All required signatures for your role are already completed.'
                        );
                    }

                    $sigKey  = $signatureField->ff_signature_key;
                    $dateKey = $signatureField->ff_signature_date_key;

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

                    /* MERGE AND STORE SIGNATURE DATA */
                    $merged = array_merge($existing, $block);
                    $correction->ac_signature_data   = json_encode($merged);
                    $correction->ac_final_submission  = $documentName;
                    $correction->ac_status            = $status;
                    $correction->semester_id          = $semester->id;
                    $correction->save();
                } elseif ($type == 3) {
                    /* EVALUATION REPORT SIGNATURE */

                    /* LOAD SIGNATURE FIELD DATA */
                    $signatureField = FormField::where([
                        ['af_id', $form->id],
                        ['ff_category', 6],
                        ['ff_signature_role', $signatureRole]
                    ])->first();

                    /* STORE SIGNATURE LOGIC */
                    $existingSignatureData = [];
                    if ($evaluation->evaluation_signature_data) {
                        $existingSignatureData = json_decode($evaluation->evaluation_signature_data, true);
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


                        if ($signatureRole == 1) {
                            $mergedSignatureData = $newSignatureData;
                        } else {
                            $mergedSignatureData = array_merge($existingSignatureData, $newSignatureData);
                        }

                        /* MERGE AND STORE SIGNATURE DATA */
                        $evaluation->evaluation_signature_data = json_encode($mergedSignatureData);
                        $evaluation->evaluation_document = $documentName;
                        $evaluation->save();
                    }
                }
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error storing signature: ' . $e->getMessage());
        }
    }

    /* Generate Activity Form Document [Student] - Function */
    public function generateActivityForm($actID, $student, $semester, $form, $finalDocRelativePath, $type)
    {
        try {

            /* 
             * TYPE OF FORM
             * 1 : ACTIVIRY FORM
             * 2 : CORRECTION FORM
             */

            /* LOAD ACTIVITY DATA */
            $act = Activity::where('id', $actID)->first();

            if (!$act) {
                return back()->with('error', 'Activity not found. Document could not be generated. Please contact administrator for further assistance.');
            }

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

            /* SIGNATURE & DOCUMENT NAME LOGIC */
            if ($type == 1) {

                /* LOAD SIGNATURE DATA */
                $signatureRecord = StudentActivity::where([
                    ['activity_id', $actID],
                    ['student_id', $student->id],
                    ['semester_id', $semester->id],
                ])->select('sa_signature_data')->first();

                $signatureData = $signatureRecord ? json_decode($signatureRecord->sa_signature_data) : null;

                /* SET DOCUMENT NAME */
                $fileName = 'Activity_Form_' . $student->student_matricno . '_' . '.pdf';
            } elseif ($type == 2) {

                /* LOAD SIGNATURE DATA */
                $signatureRecord = ActivityCorrection::where([
                    ['activity_id', $actID],
                    ['student_id', $student->id],
                    ['semester_id', $semester->id],
                ])->select('ac_signature_data')->first();

                $signatureData = $signatureRecord ? json_decode($signatureRecord->ac_signature_data) : null;

                /* SET DOCUMENT NAME */
                $fileName = 'Activity_Correction_Form_' . $student->student_matricno . '_' . '.pdf';
            }

            /* DATA MAPPING LOGIC */
            $userData = [];
            $fhc = new FormHandlerController();
            $userData = $fhc->joinMap($formfields, $student, $act);

            /* RETURN PDF VIEW */
            $pdf = Pdf::loadView('student.programme.form-template.activity-document', [
                'title' => $fileName,
                'act' => $act,
                'form_title' => $form->af_title,
                'formfields' => $formfields,
                'userData' => $userData,
                'faculty' => $faculty,
                'signatures' => $signatures,
                'signatureData' => $signatureData
            ]);

            /* SAVING DOCUMENT */
            $relativePath = $finalDocRelativePath . '/' . $fileName;
            Storage::disk('public')->put($relativePath, $pdf->output());

            /* RETURN PDF STREAM */
            return $pdf->stream($fileName . '.pdf');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error generating form: ' . $e->getMessage());
        }
    }

    /* View Final Document [Student] - Route */
    public function viewFinalDocument($actID, $semesterID, $filename, $opt)
    {
        /* GLOBAL VARIABLE */
        $basePath = null;
        $finalPath = null;

        /* DECRYPT PROCESS */
        $actID = decrypt($actID);
        $semesterID = decrypt($semesterID);
        $filename = Crypt::decrypt($filename);

        try {

            /* LOAD STUDENT DATA */
            $student = auth()->user();

            if (!$student) {
                return back()->with('error', 'Unauthorized access : Student record is not found.');
            }

            /* LOAD SEMESTER DATA */
            $currentSemester = Semester::where('id', $semesterID)->first();

            if (!$currentSemester) {
                return back()->with('error', 'Semester not found. Could not view submission. Please contact administrator for further assistance.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $actID)->first()->act_name;

            if (!$activity) {
                return back()->with('error', 'Activity not found. Could not view submission. Please contact administrator for further assistance.');
            }

            /* LOAD PROCEDURE DATA */
            $procedure = Procedure::where([
                'activity_id' => $actID,
                'programme_id' => $student->programme_id
            ])->first();

            if (!$procedure) {
                return back()->with('error', 'Procedure not found. Could not view submission. Please contact administrator for further assistance.');
            }

            /* HANDLE NECCESSARY ATTRIBUTES */
            $progcode = strtoupper($student->programmes->prog_code);

            $rawLabel = $currentSemester->sem_label;
            $semesterlabel = str_replace('/', '', $rawLabel);
            $semesterlabel = trim($semesterlabel);

            /* IDENTIFY BASE PATH */
            if ($procedure->is_repeatable == 1) {
                $basePath = storage_path("app/public/{$student->student_directory}/{$progcode}/{$activity}/{$semesterlabel}");
            } else {
                $basePath = storage_path("app/public/{$student->student_directory}/{$progcode}/{$activity}");
            }

            /* SET FINAL PATH BASED ON OPTION */
            if ($opt == 1) {
                $finalPath = $basePath . '/Final Document/' . $filename;
            } else if ($opt == 2) {
                if ($procedure->is_repeatable != 1) {
                    $finalPath = $basePath . '/Evaluation/' . $semesterlabel . '/' . $filename;
                } else {
                    $finalPath = $basePath . '/Evaluation/' . $filename;
                }
            } else if ($opt == 3) {
                $finalPath = $basePath . '/Correction/' . $filename;
            }

            /* HANDLE FILE NOT FOUND */
            if (!file_exists($finalPath)) {
                abort(404, 'File not found. Please try again.');
            }

            /* RETURN VIEW */
            return response()->file($finalPath, [
                'Content-Type' => 'application/pdf'
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* Journal Publication Management [Student] - Route */
    public function journalPublicationManagement(Request $req)
    {
        try {

            $data = DB::table('journal_publications')
                ->select('id as journal_id', 'journal_name', 'journal_scopus_isi', 'created_at')
                ->where('student_id', auth()->user()->id)
                ->get();

            if ($req->ajax()) {

                $table = DataTables::of($data)->addIndexColumn();

                $table->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="user-checkbox form-check-input" value="' . $row->journal_id . '">';
                });

                $table->addColumn('journal_scopus_isi', function ($row) {
                    if ($row->journal_scopus_isi == 1) {
                        return '<i class="fas fa-check text-success"></i>';
                    } else {
                        return '<i class="fas fa-minus text-danger"></i>';
                    }
                });

                $table->addColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d M Y g:i A') ?? '-';
                });

                $table->addColumn('action', function ($row) {
                    return
                        '
                            <a href="javascript: void(0)" class="avtar avtar-xs btn-light-primary editJournalBtn" 
                                data-id=' . $row->journal_id . ' data-name="' . $row->journal_name . '" data-scopus=' . $row->journal_scopus_isi . '>
                                <i class="ti ti-edit f-20"></i>
                            </a>

                             <a href="javascript: void(0)" class="avtar avtar-xs  btn-light-danger deleteJournalBtn" data-id=' . $row->journal_id . '>
                                    <i class="ti ti-trash f-20"></i>
                            </a>
                        ';
                });

                $table->rawColumns(['checkbox', 'journal_scopus_isi', 'created_at', 'action']);

                return $table->make(true);
            }
            return view('student.submission.journal-publication-management', [
                'title' => 'Student Journal Publication',
                'journals' => $data
            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
            return abort(500);
        }
    }

    /* Get Journal Publication [Student] - Function */
    public function getJournalPublication()
    {
        try {
            $id = auth()->user()->id;
            $journal = JournalPublication::where('student_id', $id)->get();

            return response()->json([
                'success' => true,
                'data' => $journal
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => 'Error fetching journal publication: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Add Journal Publication [Student] - Function */
    public function addJournalPublication(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'journal_name' => 'required|string',
            'journal_scopus_isi' => 'required|integer|in:0,1',
        ], [], [
            'journal_name' => 'journal name',
            'journal_scopus_isi' => 'journal scopus/isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $journal = JournalPublication::create([
                'journal_name' => $validated['journal_name'],
                'journal_scopus_isi' => $validated['journal_scopus_isi'],
                'student_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => true,
                'data' => $journal,
                'message' => 'Journal publication added successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => 'Error adding journal publication: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Update Journal Publication [Student] - Function */
    public function updateJournalPublication(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'journal_name' => 'required|string',
            'journal_scopus_isi' => 'required|integer|in:0,1',
        ], [], [
            'journal_name' => 'journal name',
            'journal_scopus_isi' => 'journal scopus/isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $journal = JournalPublication::where('id', $req->journal_id)
                ->where('student_id', auth()->user()->id)
                ->firstOrFail();

            $journal->update([
                'journal_name' => $validated['journal_name'],
                'journal_scopus_isi' => $validated['journal_scopus_isi'],
            ]);

            return response()->json([
                'success' => true,
                'data' => $journal,
                'message' => 'Journal publication updated successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => 'Error updating journal publication: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Delete Journal Publication [Student] - Function */
    public function deleteJournalPublication(Request $req)
    {
        try {
            JournalPublication::where('id', $req->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Journal publication deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => 'Error deleting journal publication: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Get Student Activity Eligibility [Staff] - DSS Function */
    public function getStudentSubmissionEligibility($matricno, $activityid)
    {
        try {
            /** LOAD STUDENT (ACTIVE ONLY) **/
            $student = DB::table('students as s')
                ->where('s.student_matricno', $matricno)
                ->where('s.student_status', 1)
                ->select('s.id', 's.programme_id', 's.student_semcount')
                ->first();

            if (!$student) {
                return 0;
            }

            /** LOAD PROCEDURE + ACTIVITY **/
            $procedure = DB::table('procedures as p')
                ->join('activities as a', 'a.id', '=', 'p.activity_id')
                ->where('p.programme_id', $student->programme_id)
                ->where('p.activity_id', $activityid)
                ->select(
                    'p.is_repeatable',
                    'p.timeline_sem',
                    'p.init_status',
                    'p.act_seq',
                    'p.activity_id',
                    'a.act_name'
                )
                ->first();

            if (!$procedure) {
                return 0;
            }

            /** TIMELINE GATE: STUDENT MUST REACH REQUIRED SEMESTER COUNT **/
            if ((int)$student->student_semcount < (int)$procedure->timeline_sem) {
                return 0;
            }

            /** STRATEGY FLAGS **/
            $isRepeatable = ((int)$procedure->is_repeatable === 1);

            // Prefer DB flag if exists; otherwise fall back to a safe heuristic for "always-open"
            $hasIsAlwaysOpen = Schema::hasColumn('procedures', 'is_always_open');
            $isAlwaysOpen = false;
            if ($hasIsAlwaysOpen) {
                $isAlwaysOpen = (int) DB::table('procedures')
                    ->where('programme_id', $student->programme_id)
                    ->where('activity_id', $activityid)
                    ->value('is_always_open') === 1;
            } else {
                // Heuristic: repeatable + init_status == 1 => treat as always-open (e.g., Supervisor Meeting)
                $isAlwaysOpen = $isRepeatable && ((int)$procedure->init_status === 1);
            }

            /** SCOPING FLAG: semester checks apply to repeatable AND always-open **/
            $scopeBySemester = ($isRepeatable || $isAlwaysOpen);

            /** RESOLVE LATEST/CURRENT SEMESTER ID (for semester-scoped path) **/
            $latestSemId = DB::table('student_semesters')
                ->where('student_id', $student->id)
                ->max('semester_id');

            if (!$latestSemId) {
                $latestSemId = DB::table('semesters')->where('sem_status', 1)->value('id');
            }

            /** DEPENDENCY GATE (skip if ALWAYS-OPEN) **/
            if (!$isAlwaysOpen) {
                // All prior activities in the sequence (same programme), excluding those marked always-open if the column exists
                $predecessors = DB::table('procedures as pr')
                    ->where('pr.programme_id', $student->programme_id)
                    ->where('pr.act_seq', '<', $procedure->act_seq)
                    ->when($hasIsAlwaysOpen, function ($q) {
                        $q->where(function ($qq) {
                            $qq->whereNull('pr.is_always_open')
                                ->orWhere('pr.is_always_open', 0);
                        });
                    })
                    ->pluck('pr.activity_id');

                if ($predecessors->isNotEmpty()) {
                    // If ANY predecessor is not completed (sa_status != 3 anywhere), block
                    $hasIncompletePrev = DB::table('activities as ap')
                        ->whereIn('ap.id', $predecessors)
                        ->whereNotExists(function ($q) use ($student) {
                            $q->select(DB::raw(1))
                                ->from('student_activities as sap')
                                ->whereColumn('sap.activity_id', 'ap.id')
                                ->where('sap.student_id', $student->id)
                                ->where('sap.sa_status', 3);
                        })
                        ->exists();

                    if ($hasIncompletePrev) {
                        return 7; /* BlockedByDependency */
                    }
                }
            }

            /** DOCUMENT IDs FOR THIS ACTIVITY (to scope submissions) **/
            $documentIds = DB::table('documents')
                ->where('activity_id', $activityid)
                ->pluck('id');

            /** COMPLETED (priority) **/
            $hasCompleted = DB::table('student_activities')
                ->where('student_id', $student->id)
                ->where('activity_id', $activityid)
                ->where('sa_status', 3)
                ->when($scopeBySemester, function ($q) use ($latestSemId) {
                    $q->where('semester_id', $latestSemId);
                })
                ->exists();

            if ($hasCompleted) {
                return 5;
            }

            /** ARCHIVED SUBMISSION **/
            $hasArchived = DB::table('submissions')
                ->whereIn('document_id', $documentIds)
                ->where('student_id', $student->id)
                ->where('submission_status', 5)
                ->when($scopeBySemester, function ($q) use ($latestSemId) {
                    $q->where('semester_id', $latestSemId);
                })
                ->exists();

            if ($hasArchived) {
                return 6;
            }

            /** IN PROGRESS (any student_activity record for this activity) **/
            $hasInProgress = DB::table('student_activities')
                ->where('student_id', $student->id)
                ->where('activity_id', $activityid)
                ->when($scopeBySemester, function ($q) use ($latestSemId) {
                    $q->where('semester_id', $latestSemId);
                })
                ->exists();

            if ($hasInProgress) {
                return 4;
            }

            /** PENDING SUBMISSION (no student_activity yet) **/
            $hasPending = DB::table('submissions')
                ->whereIn('document_id', $documentIds)
                ->where('student_id', $student->id)
                ->whereIn('submission_status', [1, 4])
                ->when($scopeBySemester, function ($q) use ($latestSemId) {
                    $q->where('semester_id', $latestSemId);
                })
                ->exists();

            if ($hasPending) {
                return 2;
            }

            /** ELIGIBLE **/
            return 1;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /* Assign Submission to All Student [Staff] - Function */
    public function assignSubmission()
    {
        try {
            $decision = 0;

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
                ->select(
                    'e.student_matricno',
                    'a.timeline_week',
                    'a.init_status',
                    'a.is_repeatable',
                    'a.is_haveEva',
                    'a.evaluation_mode',
                    'e.id as student_id',
                    'c.id as document_id',
                    'b.id as activity_id'
                )
                ->get();

            /** ASSIGNING SUBMISSION **/
            DB::beginTransaction();

            foreach ($data as $sub) {
                /** PREPARE BASE DUE DATE + STATUS ACCORDING TO INIT_STATUS **/
                $days = $sub->timeline_week * 7;
                $submissionDate = Carbon::parse($currsemester->sem_startdate)->addDays($days);

                if ($sub->init_status == 2) {
                    $baseStatus = 2;
                } elseif ($sub->init_status == 1) {
                    $baseStatus = $submissionDate->lt(Carbon::today()) ? 4 : 1;
                } else {
                    $baseStatus = $sub->init_status;
                }

                if ($sub->is_repeatable == 0) {
                    /* HANDLE NON-REPEATABLE ACTIVITY SUBMISSION */

                    $exists = Submission::where('student_id', $sub->student_id)
                        ->where('document_id', $sub->document_id)
                        ->exists();

                    if (!$exists) {
                        /** CREATE NEW SUBMISSION (NON-REPEATABLE) **/
                        Submission::create([
                            'submission_document' => '-',
                            'submission_duedate'  => $submissionDate,
                            'submission_status'   => $baseStatus,
                            'student_id'          => $sub->student_id,
                            'document_id'         => $sub->document_id,
                            'semester_id'         => $currsemester->id
                        ]);
                    }
                } else {

                    /* HANDLE REPEATABLE ACTIVITY SUBMISSION */

                    /** FETCH ELIGIBILITY **/
                    $decision = $this->getStudentSubmissionEligibility($sub->student_matricno, $sub->activity_id);

                    /** LOAD CURRENT-SEM SUBMISSION (IF ANY) **/
                    $currentSemSubmission = Submission::where('student_id', $sub->student_id)
                        ->where('document_id', $sub->document_id)
                        ->where('semester_id', $currsemester->id)
                        ->first();

                    /** ARCHIVE PENDING LEFTOVERS FROM PREVIOUS SEMESTERS (REPEATABLE ONLY) **/
                    Submission::where('student_id', $sub->student_id)
                        ->where('document_id', $sub->document_id)
                        ->where('semester_id', '!=', $currsemester->id)
                        ->whereIn('submission_status', [1, 3, 4])
                        ->update(['submission_status' => 5]);

                    if ($decision == 1) {

                        if (!$currentSemSubmission) {
                            /** CREATE NEW SUBMISSION (REPEATABLE, CURRENT SEM) **/
                            $currentSemSubmission = Submission::create([
                                'submission_document' => '-',
                                'submission_duedate'  => $submissionDate,
                                'submission_status'   => $baseStatus,
                                'student_id'          => $sub->student_id,
                                'document_id'         => $sub->document_id,
                                'semester_id'         => $currsemester->id
                            ]);
                        } else {
                            /** REFRESH DUE DATE + STATUS (RE-OPEN IF NEEDED) **/
                            $currentSemSubmission->update([
                                'submission_duedate'  => $submissionDate,
                                'submission_status'   => $baseStatus
                            ]);
                        }

                        /** IF ACTIVITY HAS EVALUATION, PREPARE EVALUATION/NOMINATION **/
                        if ($sub->is_haveEva == 1) {

                            /** LOAD PREVIOUS EVALUATOR (CONFIRMED ONES) **/
                            $previousEvaluator = DB::table('evaluators as a')
                                ->join('nominations as b', 'a.nom_id', '=', 'b.id')
                                ->where('b.student_id', $sub->student_id)
                                ->where('b.activity_id', $sub->activity_id)
                                ->where('a.eva_status', 3)
                                ->get();

                            if ($previousEvaluator->count() > 0) {
                                /** CREATE EVALUATION RECORDS FROM PREVIOUS EVALUATORS **/
                                foreach ($previousEvaluator as $eva) {
                                    if ($sub->evaluation_mode == 1 || ($sub->evaluation_mode == 2 && $eva->eva_role == 1)) {
                                        /** CREATE EVALUATION **/
                                        $evaluation = new Evaluation();
                                        $evaluation->student_id        = $sub->student_id;
                                        $evaluation->staff_id          = $eva->staff_id;
                                        $evaluation->activity_id       = $sub->activity_id;
                                        $evaluation->semester_id       = $currsemester->id;
                                        $evaluation->evaluation_status = 1;
                                        $evaluation->save();
                                    }
                                }
                            } else {
                                /** CREATE OR UPDATE NOMINATION **/
                                Nomination::firstOrCreate(
                                    [
                                        'student_id'  => $sub->student_id,
                                        'activity_id' => $sub->activity_id,
                                        'semester_id' => $currsemester->id
                                    ],
                                    [
                                        'nom_status' => 1
                                    ]
                                );
                            }
                        }
                    } else {

                        /** REMOVE CURRENT-SEM SUBMISSION (IF EXISTS) **/
                        if ($currentSemSubmission) {
                            $currentSemSubmission->delete();
                        }

                        /** REMOVE CURRENT-SEM STUDENT ACTIVITY + REVIEWS **/
                        $sa = StudentActivity::where('student_id', $sub->student_id)
                            ->where('activity_id', $sub->activity_id)
                            ->where('semester_id', $currsemester->id)
                            ->first();

                        if ($sa) {
                            SubmissionReview::where('student_activity_id', $sa->id)->delete();
                            $sa->delete();
                        }

                        /** REMOVE CURRENT-SEM EVALUATIONS **/
                        Evaluation::where('student_id', $sub->student_id)
                            ->where('activity_id', $sub->activity_id)
                            ->where('semester_id', $currsemester->id)
                            ->delete();

                        /** REMOVE CURRENT-SEM NOMINATIONS **/
                        Nomination::where('student_id', $sub->student_id)
                            ->where('activity_id', $sub->activity_id)
                            ->where('semester_id', $currsemester->id)
                            ->delete();
                    }
                }
            }

            DB::commit();

            /* RETURN SUCCESS */
            return back()->with('success', 'Submission has been assigned successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Oops! Error assigning students with submission: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
        }
    }

    /* Assign Submission to Individual Student [Staff] - Function */
    public function assignStudentSubmission($matricno)
    {
        try {
            $decision = 0;

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
                ->select(
                    'e.student_matricno',
                    'a.timeline_week',
                    'a.init_status',
                    'a.is_repeatable',
                    'a.is_haveEva',
                    'a.evaluation_mode',
                    'e.id as student_id',
                    'c.id as document_id',
                    'b.id as activity_id'
                )
                ->get();

            /** ASSIGNING SUBMISSION **/
            DB::beginTransaction();

            foreach ($data as $sub) {
                /** PREPARE BASE DUE DATE + STATUS ACCORDING TO INIT_STATUS **/
                $days = $sub->timeline_week * 7;
                $submissionDate = Carbon::parse($currsemester->sem_startdate)->addDays($days);

                if ($sub->init_status == 2) {
                    $baseStatus = 2;
                } elseif ($sub->init_status == 1) {
                    $baseStatus = $submissionDate->lt(Carbon::today()) ? 4 : 1;
                } else {
                    $baseStatus = $sub->init_status;
                }

                if ($sub->is_repeatable == 0) {
                    /* HANDLE NON-REPEATABLE ACTIVITY SUBMISSION */

                    $exists = Submission::where('student_id', $sub->student_id)
                        ->where('document_id', $sub->document_id)
                        ->exists();

                    if (!$exists) {
                        /** CREATE NEW SUBMISSION (NON-REPEATABLE) **/
                        Submission::create([
                            'submission_document' => '-',
                            'submission_duedate'  => $submissionDate,
                            'submission_status'   => $baseStatus,
                            'student_id'          => $sub->student_id,
                            'document_id'         => $sub->document_id,
                            'semester_id'         => $currsemester->id
                        ]);
                    }
                } else {

                    /* HANDLE REPEATABLE ACTIVITY SUBMISSION */

                    /** FETCH ELIGIBILITY **/
                    $decision = $this->getStudentSubmissionEligibility($sub->student_matricno, $sub->activity_id);

                    /** LOAD CURRENT-SEM SUBMISSION (IF ANY) **/
                    $currentSemSubmission = Submission::where('student_id', $sub->student_id)
                        ->where('document_id', $sub->document_id)
                        ->where('semester_id', $currsemester->id)
                        ->first();

                    /** ARCHIVE PENDING LEFTOVERS FROM PREVIOUS SEMESTERS (REPEATABLE ONLY) **/
                    Submission::where('student_id', $sub->student_id)
                        ->where('document_id', $sub->document_id)
                        ->where('semester_id', '!=', $currsemester->id)
                        ->whereIn('submission_status', [1, 3, 4])
                        ->update(['submission_status' => 5]);

                    if ($decision == 1) {

                        if (!$currentSemSubmission) {
                            /** CREATE NEW SUBMISSION (REPEATABLE, CURRENT SEM) **/
                            $currentSemSubmission = Submission::create([
                                'submission_document' => '-',
                                'submission_duedate'  => $submissionDate,
                                'submission_status'   => $baseStatus,
                                'student_id'          => $sub->student_id,
                                'document_id'         => $sub->document_id,
                                'semester_id'         => $currsemester->id
                            ]);
                        } else {
                            /** REFRESH DUE DATE + STATUS (RE-OPEN IF NEEDED) **/
                            $currentSemSubmission->update([
                                'submission_duedate'  => $submissionDate,
                                'submission_status'   => $baseStatus
                            ]);
                        }

                        /** IF ACTIVITY HAS EVALUATION, PREPARE EVALUATION/NOMINATION **/
                        if ($sub->is_haveEva == 1) {

                            /** LOAD PREVIOUS EVALUATOR (CONFIRMED ONES) **/
                            $previousEvaluator = DB::table('evaluators as a')
                                ->join('nominations as b', 'a.nom_id', '=', 'b.id')
                                ->where('b.student_id', $sub->student_id)
                                ->where('b.activity_id', $sub->activity_id)
                                ->where('a.eva_status', 3)
                                ->get();

                            if ($previousEvaluator->count() > 0) {
                                /** CREATE EVALUATION RECORDS FROM PREVIOUS EVALUATORS **/
                                foreach ($previousEvaluator as $eva) {
                                    if ($sub->evaluation_mode == 1 || ($sub->evaluation_mode == 2 && $eva->eva_role == 1)) {
                                        /** CREATE EVALUATION **/
                                        $evaluation = new Evaluation();
                                        $evaluation->student_id        = $sub->student_id;
                                        $evaluation->staff_id          = $eva->staff_id;
                                        $evaluation->activity_id       = $sub->activity_id;
                                        $evaluation->semester_id       = $currsemester->id;
                                        $evaluation->evaluation_status = 1;
                                        $evaluation->save();
                                    }
                                }
                            } else {
                                /** CREATE OR UPDATE NOMINATION **/
                                Nomination::firstOrCreate(
                                    [
                                        'student_id'  => $sub->student_id,
                                        'activity_id' => $sub->activity_id,
                                        'semester_id' => $currsemester->id
                                    ],
                                    [
                                        'nom_status' => 1
                                    ]
                                );
                            }
                        }
                    } else {

                        /** REMOVE CURRENT-SEM SUBMISSION (IF EXISTS) **/
                        if ($currentSemSubmission) {
                            $currentSemSubmission->delete();
                        }

                        /** REMOVE CURRENT-SEM STUDENT ACTIVITY + REVIEWS **/
                        $sa = StudentActivity::where('student_id', $sub->student_id)
                            ->where('activity_id', $sub->activity_id)
                            ->where('semester_id', $currsemester->id)
                            ->first();

                        if ($sa) {
                            SubmissionReview::where('student_activity_id', $sa->id)->delete();
                            $sa->delete();
                        }

                        /** REMOVE CURRENT-SEM EVALUATIONS **/
                        Evaluation::where('student_id', $sub->student_id)
                            ->where('activity_id', $sub->activity_id)
                            ->where('semester_id', $currsemester->id)
                            ->delete();

                        /** REMOVE CURRENT-SEM NOMINATIONS **/
                        Nomination::where('student_id', $sub->student_id)
                            ->where('activity_id', $sub->activity_id)
                            ->where('semester_id', $currsemester->id)
                            ->delete();
                    }
                }
            }

            DB::commit();

            /* RETURN SUCCESS */
            return back()->with('success', 'Submission has been assigned successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Oops! Error assigning students with submission: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
        }
    }

    /* Submission Final Overview [Staff] - Route */
    public function submissionFinalOverview(Request $req)
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
                ->leftJoin('semesters as b', 'b.id', '=', 'ss.semester_id')
                ->join('programmes as c', 'c.id', '=', 'a.programme_id')
                ->join('student_activities as d', 'd.student_id', '=', 'a.id')
                ->join('activities as e', 'e.id', '=', 'd.activity_id')
                ->select(
                    'a.*',
                    'b.sem_label',
                    'c.prog_code',
                    'c.prog_mode',
                    'd.id as sa_id',
                    'd.sa_status',
                    'd.sa_final_submission',
                    'd.created_at',
                    'd.semester_id',
                    'e.id as activity_id',
                    'e.act_name as activity_name'
                )
                ->orderBy('e.act_name');

            if ($req->ajax()) {

                if ($req->has('faculty') && !empty($req->input('faculty'))) {
                    $data->where('fac_id', $req->input('faculty'));
                }
                if ($req->has('programme') && !empty($req->input('programme'))) {
                    $data->where('programme_id', $req->input('programme'));
                }
                if ($req->has('semester') && !empty($req->input('semester'))) {
                    $data->where('d.semester_id', $req->input('semester'));
                }
                if ($req->has('activity') && !empty($req->input('activity'))) {
                    $data->where('e.id', $req->input('activity'));
                }
                if ($req->has('status') && $req->input('status') !== null && $req->input('status') !== '') {
                    $data->where('d.sa_status', $req->input('status'));
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

                $table->addColumn('sa_activity', function ($row) {
                    /* RETURN ACTIVITY NAME */
                    return $row->activity_name;
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

                $table->addColumn('sa_date', function ($row) {
                    /* FORMAT DATE TO 'd M Y g:i A' OR RETURN '-' IF NULL */
                    return Carbon::parse($row->created_at)->format('d M Y g:i A') ?? '-';
                });

                $table->addColumn('sa_semester', function ($row) {
                    /* LOAD SEMESTER DATA */
                    $semester = Semester::where('id', $row->semester_id)->first();

                    /* RETURN SEMESTER LABEL */
                    return $semester->sem_label;
                });

                $table->addColumn('sa_status', function ($row) {

                    /* CHECK STATUS AND RETURN CORRESPONDING BADGE */
                    if ($row->sa_status == 1) {
                        $status = '<span class="badge bg-light-warning">Pending Approval : Supervisor</span>';
                    } elseif ($row->sa_status == 2) {
                        $status = '<span class="badge bg-light-warning">Pending Approval : Committee / Deputy Dean / Dean</span>';
                    } elseif ($row->sa_status == 3) {
                        $status = '<span class="badge bg-success">Approved & Completed</span>';
                    } elseif ($row->sa_status == 4) {
                        $status = '<span class="badge bg-light-danger">Rejected : Supervisor</span>';
                    } elseif ($row->sa_status == 5) {
                        $status = '<span class="badge bg-light-danger">Rejected : Committee / Deputy Dean / Dean</span>';
                    } elseif ($row->sa_status == 7) {
                        $status = '<span class="badge bg-light-warning">Pending : Evaluation</span>';
                    } elseif ($row->sa_status == 8) {
                        $status = '<span class="badge bg-light-warning">Evaluation : Minor/Major Correction</span>';
                    } elseif ($row->sa_status == 9) {
                        $status = '<span class="badge bg-light-danger">Evaluation : Represent/Resubmit</span>';
                    } elseif ($row->sa_status == 12) {
                        $status = '<span class="badge bg-danger">Evaluation : Failed</span>';
                    } elseif ($row->sa_status == 13) {
                        $status = '<span class="badge bg-light-success">Passed & Continue</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">N/A</span>';
                    }

                    /* RETURN STATUS */
                    return $status;
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
                            data-bs-target="#settingModal-' . $row->sa_id . '">
                            Setting 
                        </a>
                    ';

                    /* DELETE - ONLY IF sa_status NOT IN RESTRICTED LIST */
                    $restrictedStatuses = [3, 4, 5, 7, 8, 9, 12, 13];
                    if (!in_array($row->sa_status, $restrictedStatuses)) {
                        $htmlTwo .= '
                            <a href="javascript: void(0)" class="dropdown-item" data-bs-toggle="modal"
                                data-bs-target="#deleteModal-' . $row->sa_id . '">
                                Delete
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

                $table->rawColumns(['student_photo', 'sa_activity', 'sa_final_submission', 'sa_date', 'sa_semester', 'sa_status', 'action']);

                return $table->make(true);
            }

            /* RETURN VIEW */
            return view('staff.submission.submission-final-overview', [
                'title' => 'Final Overview - Submission',
                'current_sem' => Semester::where('sem_status', 1)->first()->sem_label ?? 'N/A',
                'progs' => Programme::all(),
                'facs' => Faculty::all(),
                'sems' => Semester::all(),
                'acts' => Activity::all(),
                'studentActivity' => $data->get()
            ]);
        } catch (Exception $e) {
            dd($e);
            return abort(500, $e->getMessage());
        }
    }

    /* Update Final Submission [Staff] - Function */
    public function updateFinalSubmission(Request $req, $id)
    {
        /* DECRYPT ID */
        $id = decrypt($id);

        $validator = Validator::make($req->all(), [
            'sa_status_up' => 'required|integer|in:1,2,3,4,5,7,8,9,12,13',
        ], [], [
            'sa_status_up' => 'final submission status',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'settingModal-' . $id);
        }

        try {

            $validated = $validator->validated();

            /* LOAD STUDENT ACTIVITY DATA */
            $studentActivity = StudentActivity::where('id', $id)->first();

            if (!$studentActivity) {
                return back()->with('error', 'Error occurred: Student activity not found.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $studentActivity->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Error occurred: Student not found.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $studentActivity->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Error occurred: Activity not found.');
            }

            /* UPDATE STUDENT ACTIVITY DATA */
            StudentActivity::where('id', $id)->update([
                'sa_status' =>  $validated['sa_status_up']
            ]);

            /* RETURN SUCCESS */
            return back()->with('success', $student->student_name . ' - ' . $activity->act_name . ' final submission successfully updated.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating final submission: ' . $e->getMessage());
        }
    }

    /* Delete Final Submission [Staff] - Function | Email : Yes  */
    public function deleteFinalSubmission($id)
    {
        /* DECRYPT ID */
        $id = decrypt($id);
        try {

            /* LOAD STUDENT ACTIVITY DATA */
            $studentActivity = StudentActivity::where('id', $id)->first();

            if (!$studentActivity) {
                return back()->with('error', 'Error occurred: Student activity not found.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $studentActivity->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Error occurred: Student not found.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $studentActivity->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Error occurred: Activity not found.');
            }

            /* LOAD PROCEDURE DATA */
            $procedure = Procedure::where('activity_id', $activity->id)
                ->where('programme_id', $student->programme_id)
                ->first();

            if (!$procedure) {
                return back()->with('error', 'Error occurred: Procedure not found.');
            }

            /* LOAD SEMESTER DATA */
            $semester = Semester::where('id', $studentActivity->semester_id)->first();

            if (!$semester) {
                return back()->with('error', 'Error occurred: Semester not found.');
            }

            $programme = Programme::where('id', $student->programme_id)->first();

            if (!$programme) {
                return back()->with('error', 'Error occurred: Programme not found.');
            }

            /* LOAD FINAL SUBMISSION PATH */
            $basepath = $student->student_directory . '/' . strtoupper($programme->prog_code) . '/' . $activity->act_name;

            if (!Storage::exists($basepath)) {
                return back()->with('error', 'Error occurred: Activity Directory not found.');
            }

            if ($procedure->is_repeatable == 1) {
                $finalSubmissionPath = $basepath . '/' . str_replace('/', '', $semester->sem_label)  . '/Final Document';
            }

            if ($procedure->is_repeatable == 0) {
                $finalSubmissionPath = $basepath . '/Final Document/';
            }

            /* DELETE FINAL SUBMISSION */
            Storage::disk('public')->deleteDirectory($finalSubmissionPath);

            /* LOAD STUDENT ACTIVITY REVIEW DATA */
            $studentActivityReview = SubmissionReview::where('student_activity_id', $studentActivity->id)->first();

            /* DELETE STUDENT ACTIVITY REVIEW */
            if ($studentActivityReview) {
                $studentActivityReview->delete();
            }

            /* DELETE STUDENT ACTIVITY */
            $studentActivity->delete();

            /* SEND EMAIL NOTIFICATION TO STUDENT */
            $this->sendSubmissionNotification($student, 1, $activity->act_name, 5, 0);

            /* RETURN SUCCESS */
            return back()->with('success', $student->student_name . ' - ' . $activity->act_name . ' final submission successfully deleted. An email notification has been sent to the student.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error deleting final submission: ' . $e->getMessage());
        }
    }

    /* Correction Final Overview [Staff] - Route */
    public function correctionFinalOverview(Request $req)
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
                ->leftJoin('semesters as b', 'b.id', '=', 'ss.semester_id')
                ->join('programmes as c', 'c.id', '=', 'a.programme_id')
                ->join('activity_corrections as d', 'd.student_id', '=', 'a.id')
                ->join('activities as e', 'e.id', '=', 'd.activity_id')
                ->select(
                    'a.*',
                    'b.sem_label',
                    'c.prog_code',
                    'c.prog_mode',
                    'd.id as ac_id',
                    'd.ac_status',
                    'd.ac_final_submission',
                    'd.ac_startdate',
                    'd.ac_duedate',
                    'd.semester_id',
                    'e.id as activity_id',
                    'e.act_name as activity_name'
                )
                ->orderBy('e.act_name');

            if ($req->ajax()) {

                if ($req->has('faculty') && !empty($req->input('faculty'))) {
                    $data->where('fac_id', $req->input('faculty'));
                }
                if ($req->has('programme') && !empty($req->input('programme'))) {
                    $data->where('programme_id', $req->input('programme'));
                }
                if ($req->has('semester') && !empty($req->input('semester'))) {
                    $data->where('d.semester_id', $req->input('semester'));
                }
                if ($req->has('activity') && !empty($req->input('activity'))) {
                    $data->where('e.id', $req->input('activity'));
                }
                if ($req->has('status') && $req->input('status') !== null && $req->input('status') !== '') {
                    $data->where('d.ac_status', $req->input('status'));
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

                $table->addColumn('ac_activity', function ($row) {
                    /* RETURN ACTIVITY NAME */
                    return $row->activity_name;
                });

                $table->addColumn('ac_final_submission', function ($row) {

                    /* HANDLE EMPTY FINAL DOCUMENT */
                    if (empty($row->ac_final_submission)) {
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
                        $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/' . $semesterlabel . '/Correction';
                    } else {
                        $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name . '/Correction/' . $semesterlabel;
                    }

                    /* HTML OUTPUT */
                    $final_doc =
                        '
                        <a href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->ac_final_submission)]) . '" 
                            target="_blank" class="link-dark d-flex align-items-center">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                            <span class="fw-semibold">View Document</span>
                        </a>
                    ';

                    /* RETURN HTML */
                    return $final_doc;
                });

                $table->addColumn('ac_date', function ($row) {
                    /* RETURN DATE HTML */
                    return '
                        <div style="line-height:1.2; font-size: 12px;">
                            <div><strong>Correction Start:</strong><br>' . Carbon::parse($row->ac_startdate)->format('d M Y g:i A') . '</div>
                            <div class="mt-1"><strong>Correction Due:</strong><br>' . Carbon::parse($row->ac_duedate)->format('d M Y g:i A') . '</div>
                        </div>
                    ';
                });

                $table->addColumn('ac_semester', function ($row) {
                    /* LOAD SEMESTER DATA */
                    $semester = Semester::where('id', $row->semester_id)->first();

                    /* RETURN SEMESTER LABEL */
                    return $semester->sem_label;
                });

                $table->addColumn('ac_status', function ($row) {

                    /* CORECTION STATUS MAP */
                    $correctionStatusMap = [
                        1 => '<span class="badge bg-light-warning">Correction : Pending Student Action</span>',
                        2 => '<span class="badge bg-light-warning">Correction : Pending Supervisor Approval</span>',
                        3 => '<span class="badge bg-light-warning">Correction : Pending Examiners / Panels Approval</span>',
                        4 => '<span class="badge bg-light-warning">Correction : Pending Committee / <br/>Deputy Dean / Dean Approval</span>',
                        5 => '<span class="badge bg-success">Correction : Approve & Completed</span>',
                        6 => '<span class="badge bg-light-danger">Correction : Rejected by Supervisor</span>',
                        7 => '<span class="badge bg-light-danger">Correction : Rejected by Examiners/Panels</span>',
                        8 => '<span class="badge bg-light-danger">Correction : Rejected by Committee / <br/>Deputy Dean / Dean</span>',
                    ];

                    /* RETURN CORECTION STATUS */
                    return $correctionStatusMap[$row->ac_status] ?? '<span class="badge bg-light-danger">N/A</span>';
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
                            data-bs-target="#settingModal-' . $row->ac_id . '">
                            Setting 
                        </a>
                    ';

                    /* DELETE - ONLY IF ac_status IS 1 */
                    if ($row->ac_status == 1) {
                        $htmlTwo .= '
                            <a href="javascript: void(0)" class="dropdown-item" data-bs-toggle="modal"
                                data-bs-target="#deleteModal-' . $row->ac_id . '">
                                Delete
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

                $table->rawColumns(['student_photo', 'ac_activity', 'ac_final_submission', 'ac_date', 'ac_semester', 'ac_status', 'action']);

                return $table->make(true);
            }

            /* RETURN VIEW */
            return view('staff.submission.correction-final-overview', [
                'title' => 'Final Overview - Correction',
                'current_sem' => Semester::where('sem_status', 1)->first()->sem_label ?? 'N/A',
                'progs' => Programme::all(),
                'facs' => Faculty::all(),
                'sems' => Semester::all(),
                'acts' => Activity::all(),
                'correction' => $data->get()
            ]);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* Update Final Correction [Staff] - Function */
    public function updateFinalCorrection(Request $req, $id)
    {
        /* DECRYPT ID */
        $id = decrypt($id);

        $validator = Validator::make($req->all(), [
            'ac_startdate_up' => 'required|date_format:Y-m-d\TH:i',
            'ac_duedate_up'   => 'required|date_format:Y-m-d\TH:i|after:ac_startdate_up',
            'ac_status_up'    => 'required|integer|in:1,2,3,4,5,6,7,8',
        ], [], [
            'ac_startdate_up' => 'correction start date',
            'ac_duedate_up'   => 'correction due date',
            'ac_status_up'    => 'correction status',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'settingModal-' . $id);
        }

        try {

            $validated = $validator->validated();

            /* LOAD STUDENT ACTIVITY DATA */
            $correction = ActivityCorrection::where('id', $id)->first();

            if (!$correction) {
                return back()->with('error', 'Error occurred: Student correction not found.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $correction->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Error occurred: Student not found.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $correction->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Error occurred: Activity not found.');
            }

            /* INITIATE EVALUATION CONTROLLER */
            $ec = new EvaluationController();

            /* UPDATE STUDENT CORRECTION DATA */
            $update = ActivityCorrection::where('id', $id)->update([
                'ac_startdate' => $validated['ac_startdate_up'],
                'ac_duedate' => $validated['ac_duedate_up'],
                'ac_status' =>  $validated['ac_status_up']
            ]);

            if ($update) {
                $ec->restoreSubmission($student, $activity, $validated['ac_duedate_up']);
            }

            /* RETURN SUCCESS */
            return back()->with('success', $student->student_name . ' - ' . $activity->act_name . ' final correction successfully updated.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error updating final submission: ' . $e->getMessage());
        }
    }

    /* Delete Final Correction [Staff] - Function  */
    public function deleteFinalCorrection($id)
    {
        /* DECRYPT ID */
        $id = decrypt($id);
        try {

            /* LOAD STUDENT ACTIVITY DATA */
            $correction = ActivityCorrection::where('id', $id)->first();

            if (!$correction) {
                return back()->with('error', 'Error occurred: Student correction not found.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $correction->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Error occurred: Student not found.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $correction->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Error occurred: Activity not found.');
            }

            /* DELETE STUDENT ACTIVITY */
            $correction->delete();

            /* RETURN SUCCESS */
            return back()->with('success', $student->student_name . ' - ' . $activity->act_name . ' final correctiom successfully deleted.');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error deleting final correction: ' . $e->getMessage());
        }
    }

    /* Submission Management [Staff] - Route */
    public function submissionManagement(Request $req)
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
                    'd.semester_id',
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
                    $data->where('d.semester_id', $req->input('semester'));
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
                    /* FORMAT DATE TO 'd M Y g:i A' OR RETURN '-' IF NULL */
                    return Carbon::parse($row->submission_duedate)->format('d M Y g:i A') ?? '-';
                });

                $table->addColumn('submission_date', function ($row) {
                    /* RETURN '-' IF NULL, OTHERWISE FORMAT DATE */
                    return $row->submission_date == null ? '-' : Carbon::parse($row->submission_date)->format('d M Y g:i A');
                });

                $table->addColumn('submission_semester', function ($row) {
                    /* LOAD SEMESTER DATA */
                    $semester = Semester::where('id', $row->semester_id)->first();

                    /* RETURN SEMESTER LABEL */
                    return $semester->sem_label;
                });

                $table->addColumn('submission_status', function ($row) {
                    $status = '';

                    /* CHECK STATUS AND RETURN CORRESPONDING BADGE */
                    if ($row->submission_status == 1) {
                        $status = '<span class="badge bg-light-warning">No Attempt</span>';
                    } elseif ($row->submission_status == 2) {
                        $status = '<span class="badge bg-danger">Locked</span>';
                    } elseif ($row->submission_status == 3) {
                        $status = '<span class="badge bg-light-success">Submitted</span>';
                    } elseif ($row->submission_status == 4) {
                        $status = '<span class="badge bg-light-danger">Overdue</span>';
                    } elseif ($row->submission_status == 5) {
                        $status = '<span class="badge bg-secondary">Archive</span>';
                    } else {
                        $status = '<span class="badge bg-light-danger">N/A</span>';
                    }

                    return $status;
                });

                $table->addColumn('action', function ($row) {
                    /* SETUP STUDENT SUBMISSION DIRECTORY PATH */
                    $submission_dir = $row->student_directory . '/' . $row->prog_code . '/' . $row->activity_name;

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

                    /* DETERMINE MENU OPTIONS BASED ON SUBMISSION STATUS */
                    if ($row->submission_document != '-' && $row->submission_status != 5) {
                        /* SHOW SETTING, DOWNLOAD AND ARCHIVE OPTIONS */
                        $htmlTwo = '          
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
                        /* SHOW DOWNLOAD AND UNARCHIVE OPTIONS FOR ARCHIVED WITH DOCUMENT */
                        $htmlTwo = '
                            <a class="dropdown-item" href="' . route('view-material-get', ['filename' => Crypt::encrypt($submission_dir . '/' . $row->submission_document)]) . '" download="' . $row->submission_document . '">Download</a>  
                            <a href="javascript: void(0)" class="dropdown-item" data-bs-toggle="modal"
                                data-bs-target="#unarchiveModal-' . $row->submission_id . '">
                                Unarchive 
                            </a>
                        ';
                    } elseif ($row->submission_status == 5 && $row->submission_document == '-') {
                        /* SHOW ONLY UNARCHIVE OPTION FOR ARCHIVED WITHOUT DOCUMENT */
                        $htmlTwo = '
                            <a href="javascript: void(0)" class="dropdown-item" data-bs-toggle="modal"
                                data-bs-target="#unarchiveModal-' . $row->submission_id . '">
                                Unarchive 
                            </a>
                        ';
                    } else {
                        /* DEFAULT OPTIONS (SETTING AND ARCHIVE) */
                        $htmlTwo = '           
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

                    /* CLOSE DROPDOWN MENU TAGS */
                    $htmlThree = '
                            </div>
                        </div>
                    ';

                    return $htmlOne . $htmlTwo . $htmlThree;
                });

                $table->rawColumns(['checkbox', 'student_photo', 'submission_duedate', 'submission_date', 'submission_semester', 'submission_status', 'action']);

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
            return abort(500);
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

    /* Submission Approval [Staff] - Route */
    public function submissionApproval(Request $req)
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
                    'c.semester_id',
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
                        12 => "<span class='badge bg-danger d-block mb-1'>Evaluation: <br> Failed</span>",
                        13 => "<span class='badge bg-light-success d-block mb-1'>Evaluation: <br> Passed & Continue Activity</span>",
                        default => "N/A",
                    };


                    $signatureData = !empty($row->sa_signature_data)
                        ? json_decode($row->sa_signature_data, true)
                        : [];

                    // Get required signature roles for the activity
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
                    $activityId = $row->activity_id;
                    $studentActivityId = $row->student_activity_id;
                    $userRoleId = auth()->user()->staff_role; // 4=comm, 5=deputy dean, 6=dean (assumption)

                    /* LOAD REQUIRED SIGNATURE ROLES */
                    $formFields = DB::table('activity_forms as a')
                        ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                        ->where('a.activity_id', $activityId)
                        ->where('b.ff_category', 6)
                        ->pluck('b.ff_signature_role')
                        ->toArray();

                    $requiredRoles = collect($formFields)->unique()->values()->toArray();

                    /* CHECK REQUIRED ROLES */
                    $hasCommfield = in_array(4, $requiredRoles);
                    $hasDeputyDeanfield = in_array(5, $requiredRoles);
                    $hasDeanfield = in_array(6, $requiredRoles);

                    /* DECODE SIGNATURE DATA */
                    $signatureData = json_decode($row->sa_signature_data ?? '[]', true);
                    $hasCommSignature = isset($signatureData['comm_signature_date']);
                    $hasDeputyDeanSignature = isset($signatureData['deputy_dean_signature_date']);
                    $hasDeanSignature = isset($signatureData['dean_signature_date']);

                    /* DETERMINE USER SIGNING STATUS */
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

                    /* HANDLE ACTIVE STATUS (SA_STATUS = 1) */
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
                    }

                    /* HANDLE COMPLETED/REJECTED STATUSES (SA_STATUS = 4 OR 5) */
                    if ($row->sa_status == 4 || $row->sa_status == 5 || $row->sa_status == 7 || $row->sa_status == 8 || $row->sa_status == 9 || $row->sa_status == 12 || $row->sa_status == 13) {
                        return '<span class="fst-italic text-muted">No action required</span>';
                    }

                    /* HANDLE SIGNATURE REQUIRED CASES */
                    if ($isRequiredToSign) {
                        if ($alreadySigned) {
                            return '
                                <button type="button" class="btn btn-light btn-sm d-flex justify-content-center align-items-center w-100 mb-2"
                                    onclick="loadReviews(' . $studentActivityId . ')">
                                    <i class="ti ti-eye me-2"></i>
                                    <span class="me-2">Review</span>
                                </button>
                            ';
                        } else {
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
                    }

                    /* DEFAULT CASE - SHOW REVIEW BUTTON */
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

    /* Submission Approval Handler [Staff] - Function | Email : Yes */
    public function studentActivitySubmissionApproval(Request $request, $stuActID, $option)
    {
        /* DECRYPT PROCESS */
        $stuActID = Crypt::decrypt($stuActID);

        try {

            /* LOAD USER DATA */
            $authUser = auth()->user();

            if (!$authUser) {
                return back()->with('error', 'Unauthorized access : Staff record is not found.');
            }

            /* LOAD STUDENT ACTIVITY DATA */
            $studentActivity = StudentActivity::where('id', $stuActID)->first();

            if (!$studentActivity) {
                return back()->with('error', 'Student confirmation record not found. Approval could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $studentActivity->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Student record not found. Approval could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $studentActivity->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Activity record not found. Approval could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD SEMESTER DATA */
            $semester = Semester::where('id', $studentActivity->semester_id)->first();

            if (!$semester) {
                return back()->with('error', 'Semester record not found. Approval could not be processed. Please contact administrator for further assistance.');
            }

            /* GET ACTIVITY FORM ID */
            $afID = ActivityForm::where('activity_id', $studentActivity->activity_id)->where('af_target', 1)->first()?->id;

            if (!$afID) {
                return back()->with('error', 'Activity form not found. Approval could not be processed. Please contact administrator for further assistance.');
            }

            /* ENCRYPT ACTIVITY ID */
            $actID = Crypt::encrypt($studentActivity->activity_id);

            /* CHECK SUPERVISOR ROLE (SV or CoSV) */
            $supervision = Supervision::where('student_id', $student->id)
                ->where('staff_id', $authUser->id)->first();

            /* CHECK PROCEDURE FOR IS_HAVEEVA */
            $procedure = Procedure::where('activity_id', $activity->id)
                ->where('programme_id', $student->programme_id)
                ->first();
            $isHaveEvaluation = $procedure?->is_haveEva == 1;
            $isRepeatable = $procedure?->is_repeatable == 1;

            /* CHECK IF SV IS REQUIRED */
            $hasSvfield = DB::table('activity_forms as a')
                ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                ->where('a.activity_id', $studentActivity->activity_id)
                ->where('b.ff_category', 6)
                ->where('b.ff_signature_role', 2)
                ->where('a.id', $afID)
                ->exists();

            /* CHECK IF CO-SV IS REQUIRED */
            $hasCoSvfield = DB::table('activity_forms as a')
                ->join('form_fields as b', 'a.id', '=', 'b.af_id')
                ->where('a.activity_id', $studentActivity->activity_id)
                ->where('b.ff_category', 6)
                ->where('b.ff_signature_role', 3)
                ->where('a.id', $afID)
                ->exists();

            $hasCoSv = $hasSvfield && $hasCoSvfield;

            if ($option == 1) {
                /* APPROVE LOGIC */

                /* DETERMINE APPROVAL ROLE AND STATUS */
                [$role, $status] = $this->determineApprovalRoleStatus($supervision, null, $authUser->staff_role, 1);

                /* MERGE AND HANDLE FORM */
                $this->mergeStudentSubmission($actID, $student, $semester, $request->input('signatureData'), $role, $authUser, $status, 1, null, $studentActivity, null);

                /* HANDLE REVIEW PROCESS */
                if ($request->filled('comment')) {
                    SubmissionReview::create([
                        'student_activity_id' => $stuActID,
                        'sr_comment' => $request->input('comment'),
                        'sr_date' => now()->toDateString(),
                        'staff_id' => $authUser->id
                    ]);
                }

                /* RELOAD STUDENT ACTIVITY DATA */
                $updatedActivity = StudentActivity::where('id', $studentActivity->id)->first();

                if (!$updatedActivity) {
                    return back()->with('error', 'Student activity record not found. Approval could not be processed. Please contact administrator for further assistance.');
                }

                /* DECODE UPDATED SIGNATURE DATA */
                $updatedSignatureData = json_decode($updatedActivity->sa_signature_data ?? '[]', true);

                /* HANDLE SIGNATURE LOGIC */
                $this->handleSignatureApprovalStatus($student, $updatedActivity, null, $activity, $afID, $role, $hasCoSv, $updatedSignatureData, $isHaveEvaluation, $isRepeatable, 1);

                /* SEND EMAIL NOTIFICATION TO STUDENT */
                $this->sendSubmissionNotification($student, 1, $activity->act_name, 3, $role);

                /* RETURN SUCCESS */
                return back()->with('success', $student->student_name . ' submission for ' . $activity->act_name . ' has been approved. An email notification has been sent to the student.');
            } elseif ($option == 2) {
                /* REJECTION LOGIC */

                /* DETERMINE REJECTION ROLE AND STATUS */
                [$role, $status] = $this->determineRejectionRoleStatus($supervision, null, $authUser->staff_role, 1);

                /* UPDATE STATUS */
                StudentActivity::whereId($stuActID)->update([
                    'sa_status' => $status,
                    'sa_signature_data' => json_encode([]),
                ]);

                /* HANDLE REVIEW PROCESS */
                if ($request->filled('comment')) {
                    SubmissionReview::create([
                        'student_activity_id' => $stuActID,
                        'sr_comment' => $request->input('comment'),
                        'sr_date' => now()->toDateString(),
                        'staff_id' => $authUser->id
                    ]);
                }

                /* SEND EMAIL NOTIFICATION TO STUDENT */
                $this->sendSubmissionNotification($student, 1, $activity->act_name, 4, $role);

                /* RETURN SUCCESS */
                return back()->with('success', $student->student_name . ' submission for ' . $activity->act_name . ' has been rejected. An email notification has been sent to the student.');
            } elseif ($option == 3) {
                /* REVERT LOGIC */

                /* REMOVE NECCESSARY RECORDS */
                SubmissionReview::where('student_activity_id', $stuActID)->delete();
                StudentActivity::whereId($stuActID)->delete();

                /* SEND EMAIL NOTIFICATION TO STUDENT */
                $this->sendSubmissionNotification($student, 1, $activity->act_name, 5, 0);

                /* RETURN SUCCESS */
                return back()->with('success', $student->student_name . ' submission for ' . $activity->act_name . ' has been reverted. An email notification has been sent to the student.');
            }

            return back()->with('error', 'Oops! Something went wrong. Cannot process your request. Please try again. If the problem persists, please contact the system administrator.');
        } catch (Exception $e) {
            return back()->with('error', 'Error occurred: ' . $e->getMessage());
        }
    }

    /* Determine Approval Role and Status [Staff] - Function */
    public function determineApprovalRoleStatus($supervision, $evaluator, $staffRole, $option)
    {
        if ($option === 1) {
            /* SUBMISSION APPROVAL */

            /* COMMITTEE/ DEPUTY DEAN / DEAN */
            if (! $supervision) {
                return match ($staffRole) {
                    1 => [4, 3], // Committee
                    3 => [5, 3], // Deputy Dean
                    4 => [6, 3], // Dean
                    default => [0, 1],
                };
            }

            /* SUPERVISOR(s) */
            return match ($supervision->supervision_role) {
                1 => [2, 1], // SV 
                2 => [3, 1], // CoSV
                default => [0, 1],
            };
        }

        if ($option === 2) {
            /* CORRECTION APPROVAL */

            /* SUPERVISOR(s) */
            if ($supervision) {
                return match ($supervision->supervision_role) {
                    1 => [2, 3], // SV 
                    2 => [3, 3], // CoSV
                    default => [0, 2],
                };
            }

            /* EXAMINER(s) */
            if ($evaluator) {
                return [8, 4];   // Examiner
            }

            /* COMMITTEE/ DEPUTY DEAN / DEAN */
            return match ($staffRole) {
                1 => [4, 5],   // Committee 
                3 => [5, 5],   // Deputy Dean 
                4 => [6, 5],   // Dean 
                default => [0, 4],
            };
        }

        if ($option === 3) {
            /* EVALUATION REPORT APPROVAL */

            /* COMMITTEE/ DEPUTY DEAN / DEAN */
            if (! $supervision) {
                return match ($staffRole) {
                    1 => [4, 8], // Committee
                    3 => [5, 8], // Deputy Dean
                    4 => [6, 8], // Dean
                    default => [0, 1],
                };
            }

            /* SUPERVISOR(s) */
            return match ($supervision->supervision_role) {
                1 => [2, 8], // SV 
                2 => [3, 8], // CoSV
                default => [0, 1],
            };
        }

        /* FALLBACK */
        return [0, 2];
    }

    /* Determine Rejection Role and Status [Staff] - Function */
    public function determineRejectionRoleStatus($supervision, $evaluator, $staffRole, $option)
    {
        if ($option == 1) {
            /* ACTIVITY SUBMISSION REJECTION */

            /* COMMITTEE/ DEPUTY DEAN / DEAN */
            if (!$supervision) {
                return match ($staffRole) {
                    1 => [4, 5], // Committee
                    3 => [5, 5], // Deputy Dean
                    4 => [6, 5], // Dean
                    default => [0, 1],
                };
            }

            /* SUPERVISOR(s) */
            return match ($supervision->supervision_role) {
                1 => [2, 4], // SV
                2 => [3, 4], // CoSV
                default => [0, 1],
            };
        }

        if ($option == 2) {
            /* ACTIVITY CORRECTION REJECTION */

            /* SUPERVISOR(s) */
            if ($supervision) {
                return match ($supervision->supervision_role) {
                    1 => [2, 6], // SV 
                    2 => [3, 6], // CoSV
                    default => [0, 6],
                };
            }

            /* EXAMINER(s) */
            if ($evaluator) {
                return [8, 7];   // Examiner
            }

            /* COMMITTEE/ DEPUTY DEAN / DEAN */
            return match ($staffRole) {
                1 => [4, 8],   // Committee 
                3 => [5, 8],   // Deputy Dean 
                4 => [6, 8],   // Dean 
                default => [0, 8],
            };
        }

        if ($option == 3) {
            /* EVALUATION REPORT REJECTION */

            /* SUPERVISOR(s) */
            if ($supervision) {
                return match ($supervision->supervision_role) {
                    1 => [2, 11], // SV 
                    2 => [3, 11], // CoSV
                    default => [0, 11],
                };
            }

            /* COMMITTEE/ DEPUTY DEAN / DEAN */
            return match ($staffRole) {
                1 => [4, 12],   // Committee 
                3 => [5, 12],   // Deputy Dean 
                4 => [6, 12],   // Dean 
                default => [0, 12],
            };
        }
    }

    /* Handle Signature And Status [Staff] - Function | Email : Yes With Works */
    private function handleSignatureApprovalStatus($student, $updatedActivity, $updatedCorrection, $activity, $afID, $role, $hasCoSv, $updatedSignatureData, $isHaveEvaluation, $isRepeatable, $type)
    {
        /* HANDLE TARGET */
        $target = $type === 1 ? 1 : 2;

        /* HANDLE FORM ROLES */
        $formRoles = DB::table('activity_forms as a')
            ->join('form_fields as b', 'a.id', '=', 'b.af_id')
            ->where('a.id', $afID)
            ->where('a.af_target', $target)
            ->where('b.ff_category', 6)
            ->pluck('b.ff_signature_role')
            ->unique()
            ->toArray();

        if (in_array($role, [2, 3])) {
            /* SUPERVISOR / CO-SUPERVISOR LOGIC */

            $hasHigherRoles   = collect($formRoles)->intersect([4, 5, 6, 8])->isNotEmpty();
            $hasSvSignature   = isset($updatedSignatureData['sv_signature']);
            $hasCoSvSignature = isset($updatedSignatureData['cosv_signature']);
            $allSigned        = $hasCoSv
                ? ($hasSvSignature && $hasCoSvSignature)
                : $hasSvSignature;

            if ($type === 1) {
                /* ACTIVITY FORM */
                if ($allSigned) {
                    if (! $hasHigherRoles) {
                        // BEFORE: $finalStatus = $isHaveEvaluation ? 7 : 3;
                        $finalStatus = $isHaveEvaluation ? 7 : ($isRepeatable ? 13 : 3);
                    } else {
                        $finalStatus = 2;
                    }
                } else {
                    $finalStatus = 1;
                }

                /* UPDATE STATUS */
                $updatedActivity->update(['sa_status' => $finalStatus]);

                /* FINALIZE PROCESS WITH EMAIL NOTIFICATION TO STUDENT */
                if ($finalStatus === 3 || $finalStatus === 13) {
                    $this->finalizeSubmission($student, $updatedActivity);
                    $this->sendSubmissionNotification($student, 1, $activity->act_name, 6, $role);
                }
            } else {
                /* CORRECTION FORM (unchanged) */
                if ($allSigned) {
                    $finalStatus = $hasHigherRoles ? 3 : 5;
                } else {
                    $finalStatus = 2;
                }
                $updatedCorrection->update(['ac_status' => $finalStatus]);
                if ($finalStatus === 5) {
                    $this->finalizeCorrection($student, $updatedCorrection);
                }
            }
            return;
        }

        if ($role === 8 && $type === 2) {
            /* EXAMINER / PANEL LOGIC - ONLY CORRECTION (unchanged) */
            $hasHigherRoles = collect($formRoles)->intersect([4, 5, 6])->isNotEmpty();
            $examKeys = DB::table('form_fields')
                ->where('af_id', $afID)
                ->where('ff_category', 6)
                ->where('ff_signature_role', 8)
                ->pluck('ff_signature_key')
                ->toArray();

            $allSigned = collect($examKeys)->every(
                fn($key) =>
                isset($updatedSignatureData[$key]) && ! empty($updatedSignatureData[$key])
            );

            $newStatus = ! $allSigned ? 3 : ($hasHigherRoles ? 4 : 5);
            $updatedCorrection->update(['ac_status' => $newStatus]);
            if ($newStatus === 5) {
                $this->finalizeCorrection($student, $updatedCorrection);
            }
            return;
        }

        if (in_array($role, [4, 5, 6])) {
            /* COMMITTEE / DEPUTY-DEAN / DEAN LOGIC */

            $roleSignatures = [
                4 => in_array(4, $formRoles) ? isset($updatedSignatureData['comm_signature_date']) : true,
                5 => in_array(5, $formRoles) ? isset($updatedSignatureData['deputy_dean_signature_date']) : true,
                6 => in_array(6, $formRoles) ? isset($updatedSignatureData['dean_signature_date']) : true,
            ];

            $allSigned = collect($roleSignatures)
                ->only($formRoles)
                ->every(fn($signed) => $signed);

            if ($type === 1) {
                /* ACTIVITY FORM */
                // BEFORE: $finalStatus = $allSigned ? ($isHaveEvaluation ? 7 : 3) : 2;
                $finalStatus = $allSigned
                    ? ($isHaveEvaluation ? 7 : ($isRepeatable ? 13 : 3))
                    : 2;

                $updatedActivity->update(['sa_status' => $finalStatus]);

                if ($finalStatus === 3 || $finalStatus === 13) {
                    $this->finalizeSubmission($student, $updatedActivity);
                    $this->sendSubmissionNotification($student, 1, $activity->act_name, 6, $role);
                }
            } else {
                /* CORRECTION FORM (unchanged) */
                $finalStatus = $allSigned ? 5 : 4;
                $updatedCorrection->update(['ac_status' => $finalStatus]);
                if ($finalStatus === 5) {
                    $this->finalizeCorrection($student, $updatedCorrection);
                }
            }
        }
    }

    /* Archive Submission [Staff] [Committee/DD/DEAN] - Function */
    public function finalizeSubmission($student, $activity)
    {
        DB::table('submissions as a')
            ->join('documents as b', 'a.document_id', '=', 'b.id')
            ->join('activities as c', 'b.activity_id', '=', 'c.id')
            ->where('a.student_id', $student->id)
            ->where('c.id', $activity->activity_id)
            ->where('a.semester_id', $activity->semester_id)
            ->update(['a.submission_status' => 5]);
    }

    /* Download Final Document [Staff] [Committee/DD/DEAN] - Function */
    public function downloadMultipleFinalDocument(Request $req)
    {
        try {
            /* FETCHING IDs FROM REQUEST */
            $submissionIds = json_decode($req->query('ids'), true);
            $option = $req->query('option');

            /* HANDLE UNSELECTED STUDENTS */
            if (!$submissionIds || count($submissionIds) === 0) {
                return back()->with('error', 'No students selected.');
            }

            /* ESTABISHING ZIP FILE */
            $zipFile = storage_path('app/public/ePGS_SELECTED_STUDENT_FINAL_DOCUMENT.zip');

            if (File::exists($zipFile)) {
                File::delete($zipFile);
            }

            /* ZIP FILE LOGIC USING ZIPARCHIVE */
            $zip = new ZipArchive;
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return back()->with('error', 'Failed to create ZIP file.');
            }

            if ($option == 1) {
                /* ZIP ACTIVITY FINAL SUBMISSION DOCUMENT */

                foreach ($submissionIds as $id) {

                    /* FETCHING DATA FROM DATABASE */
                    $submission = DB::table('students as a')
                        ->join('programmes as c', 'c.id', '=', 'a.programme_id')
                        ->join('student_activities as g', 'g.student_id', '=', 'a.id')
                        ->join('activities as f', 'f.id', '=', 'g.activity_id')
                        ->join('semesters as e', 'e.id', '=', 'g.semester_id')
                        ->select(
                            'a.*',
                            'c.prog_code',
                            'c.prog_mode',
                            'f.id as activity_id',
                            'f.act_name as activity_name',
                            'g.id as student_activity_id',
                            'g.sa_final_submission',
                            'e.sem_label'
                        )
                        ->where('g.id', $id)
                        ->first();

                    /* LOAD PROCEDURE DATA */
                    $procedure = Procedure::where('activity_id', $submission->activity_id)
                        ->where('programme_id', $submission->programme_id)
                        ->first();

                    /* REPEATABLE PROCESS */
                    if ($procedure && $procedure->is_repeatable == 1) {

                        /* SET SEMESTER LABEL FORMAT */
                        $rawLabel = $submission->sem_label;
                        $semesterlabel = str_replace('/', '', $rawLabel);
                        $semesterlabel = trim($semesterlabel);

                        /* STUDENT SUBMISSION DIRECTORY */
                        $submission_dir = $submission->student_directory . '/' . $submission->prog_code . '/' . $submission->activity_name . '/' . $semesterlabel . '/Final Document';
                    } else {
                        /* STUDENT SUBMISSION DIRECTORY */
                        $submission_dir = $submission->student_directory . '/' . $submission->prog_code . '/' . $submission->activity_name . '/Final Document';
                    }

                    /* HANDLE EMPTY FOLDER OR FILE */
                    if (!$submission || empty($submission->sa_final_submission)) {
                        continue;
                    }

                    /* LOAD FOLDER PATH */
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
                /* ZIP CORECTION FINAL SUBMISSION DOCUMENT */

                foreach ($submissionIds as $id) {

                    /* FETCHING DATA FROM DATABASE */
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


                    /* SET SEMESTER LABEL FORMAT */
                    $currsemester = Semester::find($submission->semester_id);
                    $rawLabel = $currsemester->sem_label;
                    $semesterlabel = str_replace('/', '', $rawLabel);
                    $semesterlabel = trim($semesterlabel);

                    /* STUDENT SUBMISSION DIRECTORY */
                    $submission_dir = $submission->student_directory . '/' . $submission->prog_code . '/' . $submission->activity_name . '/Correction/' . $semesterlabel;

                    /* HANDLE EMPTY FOLDER OR FILE */
                    if (!$submission || empty($submission->ac_final_submission)) {
                        continue;
                    }

                    /* LOAD FOLDER PATH */
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

            /* ZIP FILE DOWNLOAD */
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

    /* Correction Approval [Staff] [Committee/DD/DEAN] - Route */
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

    /* Correction Approval - [Staff] [Examiners/Panels] - Route */
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

    /* Student Correction Approval - Function [Staff] | Email : Yes With Works */
    public function studentActivityCorrectionApproval(Request $request, $actCorrID, $option)
    {
        /* DECRYPT PROCESS */
        $actCorrID = Crypt::decrypt($actCorrID);

        try {

            /* LOAD USER DATA */
            $authUser = auth()->user();

            if (!$authUser) {
                return back()->with('error', 'Unauthorized access : Staff record is not found.');
            }

            /* LOAD ACTIVITY CORRECTION DATA */
            $activityCorrection = ActivityCorrection::where('id', $actCorrID)->first();

            if (!$activityCorrection) {
                return back()->with('error', 'Activity correction record is not found. Approval could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD STUDENT DATA */
            $student = Student::where('id', $activityCorrection->student_id)->first();

            if (!$student) {
                return back()->with('error', 'Student record not found. Approval could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD ACTIVITY DATA */
            $activity = Activity::where('id', $activityCorrection->activity_id)->first();

            if (!$activity) {
                return back()->with('error', 'Activity record not found. Approval could not be processed. Please contact administrator for further assistance.');
            }

            /* LOAD SEMESTER DATA */
            $semester = Semester::where('id', $activityCorrection->semester_id)->first();

            if (!$semester) {
                return back()->with('error', 'Semester record not found. Approval could not be processed. Please contact administrator for further assistance.');
            }

            /* GET ACTIVITY FORM ID */
            $afID = ActivityForm::where('activity_id', $activityCorrection->activity_id)->where('af_target', 2)->first()?->id;

            if (!$afID) {
                return back()->with('error', 'Activity form not found. Approval could not be processed. Please contact administrator for further assistance.');
            }

            /* ENCRYPT ACTIVITY ID */
            $actID = Crypt::encrypt($activityCorrection->activity_id);

            /* CHECK ALL EXAMINER */
            $allExaminers = DB::table('nominations as a')
                ->join('evaluators as b', 'a.id', '=', 'b.nom_id')
                ->where('a.activity_id', $activityCorrection->activity_id)
                ->where('a.student_id', $student->id)
                ->where('b.eva_status', 3)
                ->where('b.eva_role', 1)
                ->orderBy('b.updated_at')
                ->pluck('b.staff_id')
                ->toArray();

            /* GET EXAMINER EVALUATORS */
            $evaluatorIndex = array_search($authUser->id, $allExaminers, true);
            if ($evaluatorIndex === false) {
                $evaluatorIndex = null;
            }

            /* GET CURRENT USER - EXAMINER */
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

            if ($option == 1) {
                /* APPROVE LOGIC */

                /* DETERMINE APPROVAL ROLE AND STATUS */
                [$role, $status] = $this->determineApprovalRoleStatus($supervision, $evaluator, $authUser->staff_role, 2);

                /* MERGE AND HANDLE FORM */
                $this->mergeStudentSubmission($actID, $student, $semester, $request->input('signatureData'), $role, $authUser, $status, 2, $evaluatorIndex, null, $activityCorrection);

                /* RELOAD STUDENT ACTIVITY DATA */
                $updatedCorrection = ActivityCorrection::where('id', $activityCorrection->id)->first();

                if (!$updatedCorrection) {
                    return back()->with('error', 'Student activity record not found. Approval could not be processed. Please contact administrator for further assistance.');
                }

                /* DECODE UPDATED SIGNATURE DATA */
                $updatedSignatureData = json_decode($updatedCorrection->ac_signature_data ?? '[]', true);

                /* HANDLE SIGNATURE LOGIC */
                $this->handleSignatureApprovalStatus($student, null, $updatedCorrection, $activity, $afID, $role, $hasCoSv, $updatedSignatureData, null, null, 2);

                /* SEND EMAIL NOTIFICATION TO STUDENT [NOT DONE] */
                // $this->sendSubmissionNotification($student, 1, $activity->act_name, 3, $role);

                /* RETURN SUCCESS */
                return back()->with('success',  $student->student_name . ' correction for ' . $activity->act_name . ' has been approved. An email notification has been sent to the student.');
            } elseif ($option == 2) {
                /* REJECTION LOGIC */

                /* DETERMINE REJECTION ROLE AND STATUS */
                [$role, $status] = $this->determineRejectionRoleStatus($supervision, $evaluator, $authUser->staff_role, 2);

                /* UPDATE STATUS */
                ActivityCorrection::whereId($actCorrID)
                    ->update([
                        'ac_status'         => $status,
                        'ac_signature_data' => json_encode([]),
                    ]);

                /* SEND EMAIL NOTIFICATION TO STUDENT [NOT DONE] */
                // $this->sendSubmissionNotification($student, 1, $activity->act_name, 4, $role);

                /* RETURN SUCCESS */
                return back()->with('success', $student->student_name . ' correction for ' . $activity->act_name . ' has been rejected. An email notification has been sent to the student.');
            } elseif ($option == 3) {
                /* REVERT LOGIC */

                /* UPDATE STATUS */
                ActivityCorrection::whereId($actCorrID)
                    ->update([
                        'ac_status'         => 1,
                        'ac_signature_data' => json_encode([]),
                    ]);

                /* SEND EMAIL NOTIFICATION TO STUDENT [NOT DONE] */
                // $this->sendSubmissionNotification($student, 1, $activity->act_name, 5, 0);

                /* RETURN SUCCESS */
                return back()->with('success', $student->student_name . ' correction for ' . $activity->act_name . ' has been reverted. An email notification has been sent to the student.');
            }

            return back()->with('error', 'Oops! Something went wrong. Cannot process your request. Please try again. If the problem persists, please contact the system administrator.');
        } catch (Exception $e) {
            return back()->with('error', 'Error occurred: ' . $e->getMessage());
        }
    }

    /* Finalize Correction - Function [Staff]  */
    public function finalizeCorrection($student, $correction)
    {
        DB::table('submissions as a')
            ->join('documents as b', 'a.document_id', '=', 'b.id')
            ->join('activities as c', 'b.activity_id', '=', 'c.id')
            ->where('a.student_id', $student->id)
            ->where('c.id', $correction->activity_id)
            ->update(['a.submission_status' => 5]);

        $studentactivity = StudentActivity::where('student_id', $student->id)
            ->where('activity_id', $correction->activity_id)
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
                    'p.is_repeatable',
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
                        ->where('p.init_status', '=', 2)
                        ->where('p.is_repeatable', '=', 0);
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
                ->where('b.is_repeatable', 0)
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
}
