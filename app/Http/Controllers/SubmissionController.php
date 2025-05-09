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
use App\Models\Programme;
use App\Models\Submission;
use Illuminate\Support\Str;
use App\Models\ActivityForm;
use Illuminate\Http\Request;
use App\Models\StudentActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use setasign\Fpdi\PdfParser\StreamReader;


class SubmissionController extends Controller
{
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

            // dd($documentQueryTwo);

            foreach ($programmeActivity as $activity) {
                $activitySubmissions = $document->get($activity->activity_id);
                $studentAct = $student_activity->firstWhere('activity_id', $activity->activity_id);
                $requiredDocument = $documentQueryTwo->where('activity_id', $activity->activity_id)->where('isRequired', 1)->count();
                $optionalDocument = $documentQueryTwo->where('activity_id', $activity->activity_id)->where('isRequired', 0)->count();
                $submittedRequiredDocument = $documentQueryTwo->where('activity_id', $activity->activity_id)->where('isRequired', 1)->where('submission_status', 3)->count();
                $submittedOptionalDocument = $documentQueryTwo->where('activity_id', $activity->activity_id)->where('isRequired', 0)->where('submission_status', 3)->count();


                if ($studentAct) {
                    // Change status based on SA status
                    $activity->init_status = $studentAct->sa_status;
                    $activity->confirmed_document = $studentAct->sa_final_submission;
                } else {
                    // No confirmation yet
                    $lockedSubmission = $activitySubmissions?->firstWhere('submission_status', 2);
                    $activity->init_status = $lockedSubmission ? 11 : 10;
                }

                $activity->required_document = $requiredDocument;
                $activity->optional_document = $optionalDocument;
                $activity->submitted_required_document = $submittedRequiredDocument;
                $activity->submitted_optional_document = $submittedOptionalDocument;
            }

            // Filter out submissions with 'submission_status' of 2 or 5
            $filtered_documents = $document->map(function ($activityGroup) {
                return $activityGroup->filter(function ($submission) {
                    return !in_array($submission->submission_status, [2, 5]);
                });
            });

            return view('student.programme.programme-index', [
                'title' => 'Programme Overview',
                'acts' => $programmeActivity,
                'docs' => $filtered_documents,
                'sa' => $student_activity

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

            $activity_name = $activity->act_name ?? 'UNKNOWN';
            $document_name = $document->doc_name ?? 'UNKNOWN';

            // 1 - CUSTOMIZE FILENAME
            $activity_clean = preg_replace('/[\/\s]+/', '', $activity_name);
            $document_clean = preg_replace('/[\/\s]+/', '', $document_name);

            $filename = "{$student->student_matricno}_{$document_clean}_{$activity_clean}." . $originalFile->getClientOriginalExtension();

            // 2 - SAVE SUBMISSION FILE INTO DIRECTORY
            $safe_path = "{$student->student_directory}/{$programme_name}/{$activity_name}";
            $originalFile->storeAs($safe_path, $filename);

            // 3 - SAVE SUBMISSION DATA INTO DATABASE
            Submission::where('id', $validated['submission_id'])->update([
                'submission_document' => $filename,
                'submission_date' => now()->toDateTimeString(),
                'submission_status' => 3,
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

    public function confirmStudentSubmission($actID)
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

            $signatureData = request()->input('signatureData');

            // 1 - Signature Role [Student]
            // 1 - Document Status [Pending]
            $this->storeSignature($actID, $student, $form, $signatureData, $documentName, 1, 1);

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


            return back()->with('success', 'Submission has been confirmed successfully.');
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

    public function storeSignature($actID, $student, $form, $signatureData, $documentName, $signatureRole, $status)
    {
        try {
            if ($signatureData) {
                // Get the signature field for this role & activity
                $signatureField = FormField::where([
                    ['af_id', $form->id],
                    ['ff_category', 6],
                    ['ff_signature_role', $signatureRole]
                ])->first();

                if ($signatureField) {
                    $signatureKey = $signatureField->ff_signature_key;
                    $dateKey = $signatureField->ff_signature_date_key;

                    $newSignatureData = [
                        $signatureKey => $signatureData,
                        $dateKey => now()->format('d M Y')
                    ];

                    // Retrieve or create StudentActivity
                    $studentActivity = StudentActivity::where([
                        ['activity_id', $actID],
                        ['student_id', $student->id]
                    ])->first();

                    // Decode existing signature JSON if exists
                    $existingSignatureData = [];
                    if ($studentActivity && $studentActivity->sa_signature_data) {
                        $existingSignatureData = json_decode($studentActivity->sa_signature_data, true);
                    }

                    // Merge new data into existing data
                    $mergedSignatureData = array_merge($existingSignatureData, $newSignatureData);

                    // Save or create StudentActivity record
                    if (!$studentActivity) {
                        StudentActivity::create([
                            'activity_id' => $actID,
                            'student_id' => $student->id,
                            'sa_final_submission' => $documentName,
                            'sa_signature_data' => json_encode($mergedSignatureData)
                        ]);
                    } else {
                        $studentActivity->sa_status = $status;
                        $studentActivity->sa_signature_data = json_encode($mergedSignatureData);
                        $studentActivity->save();
                    }
                }
            }
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error storing signature: ' . $e->getMessage());
        }
    }

    public function viewFinalDocument($actID, $filename)
    {
        $actID = decrypt($actID);
        $filename = Crypt::decrypt($filename);

        try {
            $student = auth()->user();
            $activity = Activity::where('id', $actID)->first()->act_name;
            $progcode = strtoupper($student->programmes->prog_code);
            $basePath = storage_path("app/public/{$student->student_directory}/{$progcode}/{$activity}/Final Document/{$filename}");

            if (!file_exists($basePath)) {
                abort(404, 'File not found.');
            }

            return response()->file($basePath);
        } catch (Exception $e) {
            return abort(500, $e->getMessage());
        }
    }

    /* Submission Management [Staff] [Committee] */
    public function submissionManagement(Request $req)
    {
        try {
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
                    'd.submission_document',
                    'e.id as document_id',
                    'e.doc_name as document_name',
                    'f.id as activity_id',
                    'f.act_name as activity_name'
                )
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

    /* Submission Management [Staff] [Supervisor/Co Supervisor] */
    public function mySupervisionSubmissionManagement(Request $req)
    {
        try {
            $data = DB::table('students as a')
                ->join('semesters as b', 'b.id', '=', 'a.semester_id')
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
            return view('staff.my-supervision.submission-management', [
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

    /* Submission Management [Staff] [Supervisor/Co Supervisor] */
    public function mySupervisionSubmissionApproval(Request $req)
    {
        try {
            $data = DB::table('students as a')
                ->join('semesters as b', 'b.id', '=', 'a.semester_id')
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
            return view('staff.my-supervision.submission-approval', [
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
}
