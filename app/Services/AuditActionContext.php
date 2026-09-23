<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Builds a human-readable, pre-mutation snapshot for audit evidence. */
class AuditActionContext
{
    public function resolve(Request $request): array
    {
        $route = $request->route()?->getName() ?? $request->path();
        $parameters = $request->route()?->parameters() ?? [];

        $subject = match ($route) {
            'delete-staff-get' => $this->staff($this->id($parameters['id'] ?? null)),
            'delete-student-get' => $this->student($this->id($parameters['id'] ?? null)),
            'delete-supervision-get' => $this->student($this->id($parameters['id'] ?? null), 'supervision assignment'),
            'delete-student-semester-get' => $this->studentSemester(
                $this->id($parameters['studentID'] ?? null),
                $this->id($parameters['semID'] ?? null)
            ),
            'delete-faculty-get' => $this->simple('faculties', $this->id($parameters['id'] ?? null), 'faculty', ['fac_code', 'fac_name', 'fac_status']),
            'delete-department-get' => $this->simple('departments', $this->id($parameters['id'] ?? null), 'department', ['dep_code', 'dep_name', 'dep_status']),
            'delete-programme-get' => $this->simple('programmes', $this->id($parameters['id'] ?? null), 'programme', ['prog_code', 'prog_name', 'prog_mode', 'prog_status']),
            'delete-semester-get' => $this->simple('semesters', $this->id($parameters['id'] ?? null), 'semester', ['sem_label', 'sem_status']),
            'delete-activity-get' => $this->simple('activities', $this->id($parameters['id'] ?? null), 'activity', ['act_name']),
            'delete-document-get' => $this->simple('documents', $this->id($parameters['id'] ?? null), 'document', ['doc_name', 'activity_id']),
            'delete-procedure-get' => $this->procedure(
                $this->id($parameters['actID'] ?? null),
                $this->id($parameters['progID'] ?? null)
            ),
            'delete-form-activity-get' => $this->activityForm($this->id($parameters['afID'] ?? null)),
            'delete-final-submission-get' => $this->workflowRecord('student_activities', $this->id($parameters['id'] ?? null), 'final submission'),
            'delete-final-nomination-get' => $this->workflowRecord('nominations', $this->id($parameters['id'] ?? null), 'final nomination'),
            'delete-final-evaluation-get' => $this->workflowRecord('evaluations', $this->id($parameters['id'] ?? null), 'final evaluation'),
            'delete-final-correction-get' => $this->workflowRecord('activity_corrections', $this->id($parameters['id'] ?? null), 'final correction'),
            'delete-journal-publication-post' => $this->journalPublication($this->id($request->input('id'))),
            'delete-review-post' => $this->submissionReview($this->id($request->input('review_id'))),
            'delete-form-field-post' => $this->simple('form_fields', $this->id($request->input('ff_id')), 'form field', ['ff_label', 'ff_category', 'af_id']),
            'archive-submission-get' => $this->submission($this->id($parameters['id'] ?? null)),
            'archive-multiple-submission-post' => $this->bulkSubject('submission', $request->input('selectedIds', [])),
            default => [],
        };

        $operation = $this->operation($route, $parameters, $request);

        return array_filter([
            'subject_type' => $subject['type'] ?? null,
            'subject_id' => $subject['id'] ?? null,
            'subject_label' => $subject['label'] ?? null,
            'description' => $this->description($operation, $subject['label'] ?? null),
            'metadata' => array_filter([
                'operation' => $operation,
                'subject_snapshot' => $subject['snapshot'] ?? null,
                'resolved_route_parameters' => $this->resolvedParameters($parameters),
            ], fn ($value) => $value !== null && $value !== []),
        ], fn ($value) => $value !== null && $value !== []);
    }

    private function operation(string $route, array $parameters, Request $request): string
    {
        if (str_contains($route, 'delete')) {
            return (int) ($parameters['opt'] ?? 1) === 2 ? 'inactivate' : 'delete';
        }

        return match (true) {
            str_contains($route, 'archive') => (int) ($parameters['opt'] ?? $request->input('option', 1)) === 2 ? 'unarchive' : 'archive',
            str_contains($route, 'remove') => 'remove',
            str_contains($route, 'approve'), str_contains($route, 'approval') => 'approve',
            str_contains($route, 'assign'), str_contains($route, 'enroll') => 'assign',
            str_contains($route, 'add'), str_contains($route, 'register') => 'create',
            str_contains($route, 'update'), str_contains($route, 'change') => 'update',
            default => Str::headline($route),
        };
    }

    private function description(string $operation, ?string $label): string
    {
        $verb = match ($operation) {
            'delete' => 'Delete',
            'inactivate' => 'Inactivate',
            'archive' => 'Archive',
            'unarchive' => 'Unarchive',
            'remove' => 'Remove',
            'approve' => 'Approve',
            'assign' => 'Assign',
            'create' => 'Create',
            'update' => 'Update',
            default => Str::headline($operation),
        };

        return $label ? $verb . ' ' . $label . '.' : $verb . ' operation.';
    }

    private function staff(?int $id): array
    {
        $row = $id ? DB::table('staff')->where('id', $id)->first(['id', 'staff_id', 'staff_name', 'staff_role', 'staff_status', 'department_id']) : null;
        if (!$row) return [];

        return ['type' => 'staff account', 'id' => $row->id, 'label' => 'Staff account: ' . $row->staff_name . ' [' . $row->staff_id . ']', 'snapshot' => (array) $row];
    }

    private function student(?int $id, string $type = 'student account'): array
    {
        $row = $id ? DB::table('students')->where('id', $id)->first(['id', 'student_matricno', 'student_name', 'student_status', 'programme_id']) : null;
        if (!$row) return [];

        $prefix = $type === 'student account' ? 'Student account' : Str::headline($type) . ' for';

        return ['type' => $type, 'id' => $row->id, 'label' => $prefix . ': ' . $row->student_name . ' [' . $row->student_matricno . ']', 'snapshot' => (array) $row];
    }

    private function studentSemester(?int $studentId, ?int $semesterId): array
    {
        $student = $this->student($studentId, 'semester enrollment');
        $semester = $semesterId ? DB::table('semesters')->where('id', $semesterId)->first(['id', 'sem_label']) : null;
        if (!$student) return [];

        $student['id'] = $studentId . ':' . $semesterId;
        $student['label'] .= $semester ? ' — ' . $semester->sem_label : '';
        $student['snapshot']['semester_id'] = $semesterId;
        $student['snapshot']['semester_label'] = $semester->sem_label ?? null;
        return $student;
    }

    private function simple(string $table, ?int $id, string $type, array $columns): array
    {
        $row = $id ? DB::table($table)->where('id', $id)->first(array_merge(['id'], $columns)) : null;
        if (!$row) return [];

        $snapshot = (array) $row;
        $label = collect($columns)->map(fn ($column) => $snapshot[$column] ?? null)
            ->filter(fn ($value) => is_scalar($value) && $value !== '')
            ->take(2)->implode(' — ');

        return ['type' => $type, 'id' => $row->id, 'label' => Str::headline($type) . ($label ? ': ' . $label : ' #' . $row->id), 'snapshot' => $snapshot];
    }

    private function procedure(?int $activityId, ?int $programmeId): array
    {
        $row = ($activityId && $programmeId) ? DB::table('procedures as p')
            ->join('activities as a', 'a.id', '=', 'p.activity_id')
            ->join('programmes as g', 'g.id', '=', 'p.programme_id')
            ->where('p.activity_id', $activityId)->where('p.programme_id', $programmeId)
            ->first(['p.activity_id', 'p.programme_id', 'a.act_name', 'g.prog_code', 'g.prog_mode']) : null;
        if (!$row) return [];

        return [
            'type' => 'procedure', 'id' => $activityId . ':' . $programmeId,
            'label' => 'Procedure: ' . $row->act_name . ' — ' . $row->prog_code . ' (' . $row->prog_mode . ')',
            'snapshot' => (array) $row,
        ];
    }

    private function activityForm(?int $id): array
    {
        $row = $id ? DB::table('activity_forms as af')->join('activities as a', 'a.id', '=', 'af.activity_id')
            ->where('af.id', $id)->first(['af.id', 'af.activity_id', 'af.af_target', 'a.act_name']) : null;
        if (!$row) return [];

        return ['type' => 'activity form', 'id' => $row->id, 'label' => 'Activity form: ' . $row->act_name, 'snapshot' => (array) $row];
    }

    private function workflowRecord(string $table, ?int $id, string $type): array
    {
        $row = $id ? DB::table($table . ' as w')
            ->join('students as s', 's.id', '=', 'w.student_id')
            ->join('activities as a', 'a.id', '=', 'w.activity_id')
            ->where('w.id', $id)
            ->first(['w.id', 'w.student_id', 'w.activity_id', 's.student_name', 's.student_matricno', 'a.act_name']) : null;
        if (!$row) return [];

        return [
            'type' => $type, 'id' => $row->id,
            'label' => Str::headline($type) . ': ' . $row->student_name . ' [' . $row->student_matricno . '] — ' . $row->act_name,
            'snapshot' => (array) $row,
        ];
    }

    private function submission(?int $id): array
    {
        $row = $id ? DB::table('submissions as x')
            ->join('students as s', 's.id', '=', 'x.student_id')
            ->join('documents as d', 'd.id', '=', 'x.document_id')
            ->join('activities as a', 'a.id', '=', 'd.activity_id')
            ->where('x.id', $id)
            ->first(['x.id', 'x.student_id', 'x.document_id', 'x.submission_status', 's.student_name', 's.student_matricno', 'd.doc_name', 'a.act_name']) : null;
        if (!$row) return [];

        return [
            'type' => 'submission', 'id' => $row->id,
            'label' => 'Submission: ' . $row->student_name . ' [' . $row->student_matricno . '] — ' . $row->act_name . ' / ' . $row->doc_name,
            'snapshot' => (array) $row,
        ];
    }

    private function journalPublication(?int $id): array
    {
        $row = $id ? DB::table('journal_publications as j')->join('students as s', 's.id', '=', 'j.student_id')
            ->where('j.id', $id)->first(['j.id', 'j.journal_name', 'j.journal_scopus_isi', 'j.student_id', 's.student_name', 's.student_matricno']) : null;
        if (!$row) return [];

        return [
            'type' => 'journal publication', 'id' => $row->id,
            'label' => 'Journal publication: ' . $row->journal_name . ' — ' . $row->student_name . ' [' . $row->student_matricno . ']',
            'snapshot' => (array) $row,
        ];
    }

    private function submissionReview(?int $id): array
    {
        $row = $id ? DB::table('submission_reviews as r')
            ->join('student_activities as sa', 'sa.id', '=', 'r.student_activity_id')
            ->join('students as s', 's.id', '=', 'sa.student_id')
            ->join('activities as a', 'a.id', '=', 'sa.activity_id')
            ->where('r.id', $id)
            ->first(['r.id', 'r.student_activity_id', 'r.staff_id', 'r.sr_date', 's.student_name', 's.student_matricno', 'a.act_name']) : null;
        if (!$row) return [];

        return [
            'type' => 'submission review', 'id' => $row->id,
            'label' => 'Submission review: ' . $row->student_name . ' [' . $row->student_matricno . '] — ' . $row->act_name,
            'snapshot' => (array) $row,
        ];
    }

    private function bulkSubject(string $type, mixed $ids): array
    {
        $ids = is_array($ids) ? array_values(array_filter(array_map('intval', $ids))) : [];
        if ($ids === []) return [];

        return [
            'type' => 'bulk ' . $type . ' operation',
            'id' => implode(',', array_slice($ids, 0, 20)),
            'label' => count($ids) . ' selected ' . Str::plural($type, count($ids)),
            'snapshot' => ['record_count' => count($ids), 'record_ids' => array_slice($ids, 0, 200)],
        ];
    }

    private function id(mixed $value): ?int
    {
        if (is_numeric($value)) return (int) $value;
        if (!is_string($value) || $value === '') return null;

        try {
            $decrypted = Crypt::decrypt($value);
            return is_numeric($decrypted) ? (int) $decrypted : null;
        } catch (DecryptException) {
            return null;
        }
    }

    private function resolvedParameters(array $parameters): array
    {
        $resolved = [];
        foreach ($parameters as $key => $value) {
            $resolved[$key] = str_contains(strtolower((string) $key), 'id') ? ($this->id($value) ?? '[unresolved]') : $value;
        }
        return $resolved;
    }
}
