<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FormHandlerController extends Controller
{
    public function joinMap($formfields, $student, $act)
    {
        $userData = [];

        $specialMappings = [
            'prog_mode' => [
                'FT' => 'Full-Time',
                'PT' => 'Part-Time',
            ],
            'journal_scopus_isi' => [
                1 => '/',
                0 => '-',
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
            'evaluators' => [
                'nominations' => [
                    'alias' => 'b',
                    'table' => 'nominations',
                    'on' => ['a.nom_id', '=', 'b.id'],
                ],
                'staff' => [
                    'alias' => 'c',
                    'table' => 'staff',
                    'on' => ['a.staff_id', '=', 'c.id'],
                ],
            ],
            'journal_publications' => [
                'students' => [
                    'alias' => 'b',
                    'table' => 'students',
                    'on' => ['a.student_id', '=', 'b.id'],
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

            // SPECIAL TABLE MAPPING
            if ($baseTable == 'evaluators') {
                if ($key == 'evaluator_name')
                    $key = 'staff_name';
                elseif ($key == 'evaluator_email')
                    $key = 'staff_email';
                elseif ($key == 'evaluator_phoneno')
                    $key = 'staff_phoneno';

                if ($extraCondition == 3)
                    $extraCondition = 1;
                elseif ($extraCondition == 4)
                    $extraCondition = 2;
            }

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

            if ($baseTable === 'evaluators') {
                // 1) join nominations
                if (! in_array('b', $joinedAliases)) {
                    $j = $joinMap['evaluators']['nominations'];
                    $query->join(
                        "{$j['table']} as {$j['alias']}",
                        ...$j['on']
                    );
                    $joinedAliases[] = 'b';
                }
                // 2) join staff
                if (! in_array('c', $joinedAliases)) {
                    $j = $joinMap['evaluators']['staff'];
                    $query->join(
                        "{$j['table']} as {$j['alias']}",
                        ...$j['on']
                    );
                    $joinedAliases[] = 'c';
                }
                // 3) filter on student, activity, status
                $query
                    ->where('b.student_id',  $student->id)
                    ->where('b.activity_id', $act->id)
                    ->where('a.eva_status',   3);
            }

            if ($baseTable === 'journal_publications') {
                if (! in_array('b', $joinedAliases)) {
                    $j = $joinMap['journal_publications']['students'];
                    $query->join(
                        "{$j['table']} as {$j['alias']}",
                        ...$j['on']
                    );
                    $joinedAliases[] = 'b';
                }
                $query->where('a.student_id', $student->id);
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

        return $userData;
    }

    public function generateReport($title, $data, $type, $orientation, $filename, $option, $reportModule)
    {
        try {

            /* REPORT MODULE
             * 1 : SUBMISSION
             * 2 : FINAL SUBMISSION
             * 3 : FINAL NOMINATION
             * 4 : FINAL EVALUATION
             * 5 : FINAL CORRECTION
            */

            /* LOAD FACULTY DATA */
            $faculty = Faculty::where('fac_status', 3)->first();

            if (!$faculty) {
                return back()->with('error', 'Faculty not found. Failed to generate report. Please contact administrator for further assistance.');
            }

            /* INITIALIZE PDF */
            $pdf = PDF::loadView('staff.report.report-template', [
                'title' => $title,
                'groupedData' => $data,
                'faculty' => $faculty,
                'report_type' => $type,
                'report_module' => $reportModule
            ])->setPaper('a4', $orientation);

            /* RETURN PDF 
             * 1 : Download
             * 2 : View
            */
            if ($option == 1) {
                return $pdf->download($filename);
            } elseif ($option == 2) {
                return $pdf->stream($filename);
            }

            return abort(404, 'Failed to generate report. Please contact administrator for further assistance.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to generate report. Please contact administrator for further assistance. ' . $e->getMessage());
        }
    }
}
