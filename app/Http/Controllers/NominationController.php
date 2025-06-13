<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\Activity;
use App\Models\FormField;
use App\Models\ActivityForm;
use Illuminate\Http\Request;
use App\Models\StudentActivity;
use Illuminate\Support\Facades\DB;
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

            $html = view('staff.sop.template.activity-document-dynamic', [
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
            $student = Student::where('id', $studentId)->first();

            if (!$student) {
                return back()->with('error', 'Oops! Student not found');
            }

            dd($student, $req->all());
        } catch (Exception $e) {
            return back()->with('error', 'Oops! Error submitting nomination: ' . $e->getMessage());
        }
    }
}
