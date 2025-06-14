<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\Activity;
use App\Models\Evaluator;
use App\Models\FormField;
use App\Models\Nomination;
use App\Models\ActivityForm;
use Illuminate\Http\Request;
use App\Models\StudentActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class NominationController extends Controller
{

    public function viewNominationForm(Request $req)
    {
        try {
            $actID = $req->input('actid');
            $student = Student::whereId($req->input('studentid'))->first();
            $form = ActivityForm::whereId($req->input('afid'))->first();
            $mode = $req->input('mode');

            $act = Activity::where('id', $actID)->first();

            if (!$act) {
                return back()->with('error', 'Activity not found.');
            }

            $formfields = FormField::where('af_id', $form->id)
                ->orderBy('ff_order')
                ->get();

            $faculty = Faculty::where('fac_status', 3)->first();
            $signatures = $formfields->where('ff_category', 6);

            $nominationRecord = Nomination::where([
                ['activity_id', $actID],
                ['student_id', $student->id]
            ])->first();

            $signatureData = $nominationRecord ? json_decode($nominationRecord->nom_signature_data) : null;

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

            // $pdf = Pdf::loadView('student.programme.form-template.activity-document', [
            //     'title' => $act->act_name . " Document",
            //     'act' => $act,
            //     'form_title' => $form->af_title,
            //     'formfields' => $formfields,
            //     'userData' => $userData,
            //     'faculty' => $faculty,
            //     'signatures' => $signatures,
            //     'signatureData' => $signatureData
            // ]);

            // $fileName = 'Activity_Form_' . $student->student_matricno . '_' . '.pdf';
            // $relativePath = $finalDocRelativePath . '/' . $fileName;

            // Storage::disk('public')->put($relativePath, $pdf->output());

            // return $pdf->stream($fileName . '.pdf');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error generating activity form: ' . $e->getMessage());
        }
    }

    public function mysupervisionSubmitNomination(Request $req, $studentId)
    {
        try {
            $studentId = decrypt($studentId);

            /* GET STUDENT DATA */
            $student = Student::where('id', $studentId)->first();

            if (!$student) {
                return back()->with('error', 'Oops! Student not found');
            }

            /* GET FORM FIELD DATA */
            $actID = $req->input('activity_id');
            $form = ActivityForm::where('activity_id', $actID)->where('af_target', 3)->first();

            if (!$form) {
                return back()->with('error', 'Oops! Form not found');
            }

            /* GET NOMINATION DATA */
            $nomination = Nomination::where('student_id', $studentId)->where('activity_id', $actID)->first();

            if (!$nomination) {
                return back()->with('error', 'Oops! Nomination not found');
            }

            /* PROCESS EVALUATOR DATA */
            $evaluatorFields = $this->getEvaluatorFields($form);
            $this->processEvaluators($req, $evaluatorFields, $nomination);

            /* PROCESS SIGNATURE DATA */
            $signatureData = $req->input('signatureData', []);
            if (!empty($signatureData)) {
                $this->storeNominationSignature($student, $form, $signatureData, $nomination, 2, auth()->user());
            }

            /* UPDATE NOMINATION DATA */
            $nomination->nom_status = 2;
            $nomination->save();

            return redirect()->route('my-supervision-nomination', Crypt::encrypt($actID))->with('success', 'Nomination submitted successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error submitting nomination: ' . $e->getMessage());
        }
    }

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

    protected function processEvaluators($req, $evaluatorFields, $nomination)
    {
        /* DELETE EXISTING NOMINATION [IF ANY] */
        Evaluator::where('nom_id', $nomination->id)->delete();

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

    public function storeNominationSignature($student, $form, $signatureData, $nomination, $signatureRole, $userData)
    {
        try {
            // Check if signature data exists and is an array
            if ($signatureData && is_array($signatureData)) {
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

                    // Extract the actual signature string from the input data
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

                        // Merge and save
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

    protected function determineEvaluatorRole($fieldLabel)
    {
        $fieldLabel = strtolower($fieldLabel);

        if (str_contains($fieldLabel, 'examiner')) {
            return 1; // Examiner
        } elseif (str_contains($fieldLabel, 'panel')) {
            return 2; // Panel member
        } elseif (str_contains($fieldLabel, 'chair')) {
            return 3; // Chairman
        }

        return 1;
    }

    protected function findStaffByName($name)
    {
        // Handle array input by taking the first name
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

    public function mysupervisionSubmitNominatddion(Request $req, $studentId)
    {
        try {
            $studentId = decrypt($studentId);
            $student = Student::where('id', $studentId)->first();

            if (!$student) {
                return back()->with('error', 'Oops! Student not found');
            }

            // Get activity and form information
            $actID = $req->input('activity_id');
            // $afid = $req->input('afid');
            // $activity = Activity::findOrFail($actID);
            $form = ActivityForm::where('activity_id', $actID)->where('af_target', 3)->first();

            // Create or update nomination record
            $nomination = Nomination::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'activity_id' => $actID
                ],
                [
                    'nom_status' => 2, // Nominated => SV
                    'nom_date' => now(),
                ]
            );

            // Process signature data
            $signatureData = $req->input('signatureData', []);
            if (!empty($signatureData)) {
                $this->storeNominationSignatures($nomination, $signatureData, $form);
            }

            // Process evaluator nominations
            $evaluatorFields = $this->getEvaluatorFields($form);
            $this->processEvaluators($req, $evaluatorFields, $nomination);

            // Generate and store PDF document
            // $documentPath = $this->generateNominationDocument($student, $activity, $form, $req);
            // $nomination->update(['nom_document' => $documentPath]);

            return back()->with('success', 'Nomination submitted successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error submitting nomination: ' . $e->getMessage());
        }
    }
}
