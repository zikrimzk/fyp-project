<?php

namespace App\Services;

use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommitteeDashboardMetrics
{
    private const SIGNATURE_KEYS = [
        4 => 'comm_signature',
        5 => 'deputy_dean_signature',
        6 => 'dean_signature',
    ];

    private array $filters = [];

    private int $signatureRole;

    private Collection $signatureFields;

    private Collection $submissionActivities;

    public function __construct(private SubmissionActivityProgress $submissionProgress)
    {
    }

    public function forStaff(Staff $staff, array $filters): array
    {
        $this->filters = $filters;
        $this->signatureRole = [1 => 4, 3 => 5, 4 => 6][(int) $staff->staff_role] ?? 4;
        $this->signatureFields = DB::table('activity_forms as af')
            ->join('form_fields as ff', 'ff.af_id', '=', 'af.id')
            ->where('ff.ff_category', 6)
            ->get(['af.activity_id', 'af.af_target', 'ff.ff_signature_role'])
            ->groupBy(fn ($field) => $field->activity_id.':'.$field->af_target);
        $this->submissionActivities = $this->submissionProgress->forScope($filters);

        $semester = DB::table('semesters')->find($filters['semester_id']);
        $actions = $this->actions();
        $overdue = $this->overdueRecords();
        $stalled = $this->stalledRecords();
        $unassigned = $this->unassignedStudents();
        $attention = $this->attentionItems($overdue, $stalled, $unassigned);
        $activityCounts = $this->activityCounts();
        $dueSoon = $this->upcomingDeadlines();

        $riskStudentIds = collect($overdue)
            ->merge($stalled)
            ->merge($unassigned)
            ->pluck('student_id')
            ->filter()
            ->unique();

        return [
            'semester' => $semester,
            'filters' => $filters,
            'options' => $this->filterOptions(),
            'actions' => $actions,
            'kpis' => [
                'enrolled_students' => $this->enrolledStudentCount(),
                'completed_activities' => $activityCounts['completed'],
                'in_progress_activities' => $activityCounts['in_progress'],
                'at_risk_students' => $riskStudentIds->count(),
                'completion_rate' => $activityCounts['total'] > 0
                    ? round(($activityCounts['completed'] / $activityCounts['total']) * 100, 1)
                    : 0,
                'due_soon' => count($dueSoon),
            ],
            'operational' => [
                'pending_actions' => collect($actions)->sum('count'),
                'overdue' => count($overdue),
                'unassigned' => count($unassigned),
                'stalled' => count($stalled),
            ],
            'pipeline' => $this->pipeline(),
            'attention' => array_slice($attention, 0, 10),
            'attention_total' => count($attention),
            'deadlines' => array_slice($dueSoon, 0, 8),
            'definitions' => [
                'completion_rate' => 'Completed student activities divided by all recorded student activities for the selected filters.',
                'at_risk' => 'Students with an overdue item, no supervisor, or an item waiting more than 7 days.',
                'stalled' => 'Pending items with no update for at least 7 days.',
            ],
        ];
    }

    private function filterOptions(): array
    {
        return [
            'semesters' => DB::table('semesters')->orderByDesc('sem_startdate')->get(['id', 'sem_label', 'sem_status']),
            'programmes' => DB::table('programmes')->orderBy('prog_code')->get(['id', 'prog_code', 'prog_name', 'prog_mode']),
            'modes' => DB::table('programmes')->whereNotNull('prog_mode')->distinct()->orderBy('prog_mode')->pluck('prog_mode'),
            'activities' => DB::table('activities')->orderBy('act_name')->get(['id', 'act_name']),
        ];
    }

    private function enrolledStudentCount(): int
    {
        return $this->studentScope(DB::table('student_semesters as ss')
            ->join('students as s', 's.id', '=', 'ss.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 's')
            ->where('ss.semester_id', $this->filters['semester_id'])
            ->where('ss.ss_status', 1)
            ->where('s.student_status', 1)
            ->distinct()
            ->count('s.id');
    }

    private function activityCounts(): array
    {
        $rows = $this->activityScope(DB::table('student_activities as sa')
            ->join('students as s', 's.id', '=', 'sa.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 'sa', 's')
            ->pluck('sa.sa_status');

        return [
            'total' => $rows->count(),
            'completed' => $rows->filter(fn ($status) => (int) $status === 3)->count(),
            'in_progress' => $rows->filter(fn ($status) => in_array((int) $status, [1, 2, 7, 8, 9, 13], true))->count(),
        ];
    }

    private function actions(): array
    {
        $submissionRows = $this->activityScope(DB::table('student_activities as sa')
            ->join('students as s', 's.id', '=', 'sa.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 'sa', 's')
            ->where('sa.sa_status', 2)
            ->get(['sa.id', 'sa.activity_id', 'sa.sa_signature_data']);

        $nominationStatuses = $this->signatureRole === 4 ? [2, 5] : [3];
        $nominationRows = $this->activityScope(DB::table('nominations as n')
            ->join('students as s', 's.id', '=', 'n.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 'n', 's')
            ->whereIn('n.nom_status', $nominationStatuses)
            ->get(['n.id', 'n.activity_id', 'n.nom_signature_data']);

        $evaluationRows = $this->activityScope(DB::table('evaluations as e')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 'e', 's')
            ->where('e.evaluation_status', 10)
            ->get(['e.id', 'e.activity_id', 'e.evaluation_signature_data']);

        $correctionRows = $this->activityScope(DB::table('activity_corrections as ac')
            ->join('students as s', 's.id', '=', 'ac.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 'ac', 's')
            ->where('ac.ac_status', 4)
            ->get(['ac.id', 'ac.activity_id', 'ac.ac_signature_data']);

        $activity = $this->selectedActivity();

        return [
            [
                'key' => 'submissions',
                'label' => 'Submission approvals',
                'count' => $submissionRows->filter(fn ($row) => $this->unsigned($row->activity_id, 1, $row->sa_signature_data, true))->count(),
                'icon' => 'fas fa-file-signature',
                'url' => route('submission-approval'),
            ],
            [
                'key' => 'nominations',
                'label' => 'Nomination approvals',
                'count' => $nominationRows->filter(fn ($row) => $this->signatureRole === 4 || $this->unsigned($row->activity_id, 3, $row->nom_signature_data))->count(),
                'icon' => 'fas fa-user-check',
                'url' => $activity ? route('nomination-approval', Str::slug($activity->act_name)) : '#committee-attention',
            ],
            [
                'key' => 'evaluations',
                'label' => 'Evaluation approvals',
                'count' => $evaluationRows->filter(fn ($row) => $this->unsigned($row->activity_id, 5, $row->evaluation_signature_data))->count(),
                'icon' => 'fas fa-clipboard-check',
                'url' => $activity ? route('evaluation-approval', Str::slug($activity->act_name)) : '#committee-attention',
            ],
            [
                'key' => 'corrections',
                'label' => 'Correction approvals',
                'count' => $correctionRows->filter(fn ($row) => $this->unsigned($row->activity_id, 2, $row->ac_signature_data, true))->count(),
                'icon' => 'fas fa-edit',
                'url' => route('correction-approval'),
            ],
        ];
    }

    private function overdueRecords(): array
    {
        $now = now();
        $items = [];

        foreach ($this->submissionActivities->where('overdue', true) as $activity) {
            $items[] = $this->submissionRiskItem(
                $activity,
                'Activity submission overdue',
                $activity['overdue_at'],
                'danger',
                route('submission-management')
            );
        }

        $corrections = $this->activityScope(DB::table('activity_corrections as ac')
            ->join('activities as a', 'a.id', '=', 'ac.activity_id')
            ->join('students as s', 's.id', '=', 'ac.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 'ac', 's')
            ->whereIn('ac.ac_status', [1, 2, 3, 4])
            ->whereNotNull('ac.ac_duedate')
            ->where('ac.ac_duedate', '<', $now)
            ->orderBy('ac.ac_duedate')
            ->get(['ac.id', 'ac.student_id', 's.student_name', 's.student_matricno', 'p.prog_code', 'a.act_name', 'ac.ac_duedate']);

        foreach ($corrections as $row) {
            $items[] = $this->riskItem($row, 'Correction overdue', $row->ac_duedate, 'danger', route('correction-final-overview'));
        }

        return $items;
    }

    private function stalledRecords(): array
    {
        $cutoff = now()->subDays(7);
        $items = [];

        $activities = $this->activityScope(DB::table('student_activities as sa')
            ->join('activities as a', 'a.id', '=', 'sa.activity_id')
            ->join('students as s', 's.id', '=', 'sa.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 'sa', 's')
            ->whereIn('sa.sa_status', [1, 2, 7, 8, 9, 13])
            ->where('sa.updated_at', '<=', $cutoff)
            ->orderBy('sa.updated_at')
            ->get(['sa.id', 'sa.student_id', 'sa.sa_status', 'sa.updated_at', 's.student_name', 's.student_matricno', 'p.prog_code', 'a.act_name']);

        $labels = [1 => 'Waiting for supervisor approval', 2 => 'Waiting for administrator approval', 7 => 'Evaluation not completed', 8 => 'Correction pending', 9 => 'Resubmission pending', 13 => 'Continuation pending'];
        foreach ($activities as $row) {
            $url = (int) $row->sa_status === 2 ? route('submission-approval') : route('submission-final-overview');
            $items[] = $this->riskItem($row, $labels[(int) $row->sa_status] ?? 'Waiting over 7 days', $row->updated_at, 'warning', $url, true);
        }

        $nominations = $this->activityScope(DB::table('nominations as n')
            ->join('activities as a', 'a.id', '=', 'n.activity_id')
            ->join('students as s', 's.id', '=', 'n.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 'n', 's')
            ->whereIn('n.nom_status', [1, 2, 3, 5])
            ->where('n.updated_at', '<=', $cutoff)
            ->orderBy('n.updated_at')
            ->get(['n.id', 'n.student_id', 'n.updated_at', 's.student_name', 's.student_matricno', 'p.prog_code', 'a.act_name']);

        foreach ($nominations as $row) {
            $items[] = $this->riskItem(
                $row,
                'Nomination waiting over 7 days',
                $row->updated_at,
                'warning',
                route('nomination-final-overview', Str::slug($row->act_name)),
                true
            );
        }

        $evaluations = $this->activityScope(DB::table('evaluations as e')
            ->join('activities as a', 'a.id', '=', 'e.activity_id')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 'e', 's')
            ->whereIn('e.evaluation_status', [1, 7, 9, 10])
            ->where('e.updated_at', '<=', $cutoff)
            ->orderBy('e.updated_at')
            ->get(['e.id', 'e.student_id', 'e.updated_at', 's.student_name', 's.student_matricno', 'p.prog_code', 'a.act_name']);

        foreach ($evaluations as $row) {
            $items[] = $this->riskItem(
                $row,
                'Evaluation waiting over 7 days',
                $row->updated_at,
                'warning',
                route('evaluation-final-overview', Str::slug($row->act_name)),
                true
            );
        }

        $corrections = $this->activityScope(DB::table('activity_corrections as ac')
            ->join('activities as a', 'a.id', '=', 'ac.activity_id')
            ->join('students as s', 's.id', '=', 'ac.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 'ac', 's')
            ->whereIn('ac.ac_status', [1, 2, 3, 4])
            ->where('ac.updated_at', '<=', $cutoff)
            ->orderBy('ac.updated_at')
            ->get(['ac.id', 'ac.student_id', 'ac.updated_at', 's.student_name', 's.student_matricno', 'p.prog_code', 'a.act_name']);

        foreach ($corrections as $row) {
            $items[] = $this->riskItem(
                $row,
                'Correction waiting over 7 days',
                $row->updated_at,
                'warning',
                route('correction-final-overview'),
                true
            );
        }

        return $items;
    }

    private function unassignedStudents(): array
    {
        $rows = $this->studentScope(DB::table('student_semesters as ss')
            ->join('students as s', 's.id', '=', 'ss.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->leftJoin('supervisions as sv', 'sv.student_id', '=', 's.id'), 's')
            ->where('ss.semester_id', $this->filters['semester_id'])
            ->where('ss.ss_status', 1)
            ->where('s.student_status', 1)
            ->groupBy('s.id', 's.student_name', 's.student_matricno', 'p.prog_code', 'ss.created_at')
            ->havingRaw('COUNT(sv.staff_id) = 0')
            ->get(['s.id as student_id', 's.student_name', 's.student_matricno', 'p.prog_code', 'ss.created_at']);

        return $rows->map(fn ($row) => [
            'student_id' => $row->student_id,
            'student_name' => $row->student_name,
            'matric_no' => $row->student_matricno,
            'programme' => $row->prog_code,
            'activity' => 'Supervision',
            'issue' => 'No supervisor assigned',
            'waiting_days' => Carbon::parse($row->created_at)->diffInDays(now()),
            'severity' => 'danger',
            'url' => route('supervision-arrangement'),
        ])->all();
    }

    private function attentionItems(array $overdue, array $stalled, array $unassigned): array
    {
        $priority = ['danger' => 2, 'warning' => 1, 'info' => 0];

        return collect($overdue)
            ->merge($unassigned)
            ->merge($stalled)
            ->unique(fn ($item) => $item['student_id'].'|'.$item['activity'].'|'.$item['issue'])
            ->sortByDesc(fn ($item) => (($priority[$item['severity']] ?? 0) * 10000) + $item['waiting_days'])
            ->values()
            ->all();
    }

    private function upcomingDeadlines(): array
    {
        $end = now()->addDays(14)->endOfDay();
        $items = [];

        foreach ($this->submissionActivities
            ->filter(fn ($activity) => $activity['next_due_at']
                && Carbon::parse($activity['next_due_at'])->betweenIncluded(now(), $end)) as $activity) {
            $items[] = [
                'student_id' => $activity['student_id'],
                'student_name' => $activity['student_name'],
                'matric_no' => $activity['matric_no'],
                'activity' => $activity['activity_name'],
                'type' => 'Submission',
                'due_at' => $activity['next_due_at'],
                'days' => now()->diffInDays(Carbon::parse($activity['next_due_at']), false),
                'url' => route('submission-management'),
            ];
        }

        $corrections = $this->activityScope(DB::table('activity_corrections as ac')
            ->join('activities as a', 'a.id', '=', 'ac.activity_id')
            ->join('students as s', 's.id', '=', 'ac.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 'ac', 's')
            ->whereIn('ac.ac_status', [1, 2, 3, 4])
            ->whereBetween('ac.ac_duedate', [now(), $end])
            ->orderBy('ac.ac_duedate')
            ->get(['ac.student_id', 's.student_name', 's.student_matricno', 'a.act_name', 'ac.ac_duedate']);

        foreach ($corrections as $row) {
            $items[] = [
                'student_name' => $row->student_name,
                'matric_no' => $row->student_matricno,
                'activity' => $row->act_name,
                'type' => 'Correction',
                'due_at' => $row->ac_duedate,
                'days' => now()->diffInDays(Carbon::parse($row->ac_duedate), false),
                'url' => route('correction-final-overview'),
            ];
        }

        return collect($items)->sortBy('due_at')->values()->all();
    }

    private function pipeline(): array
    {
        $activityStatuses = $this->activityScope(DB::table('student_activities as sa')
            ->join('students as s', 's.id', '=', 'sa.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 'sa', 's')
            ->pluck('sa.sa_status');

        $correctionCount = $this->activityScope(DB::table('activity_corrections as ac')
            ->join('students as s', 's.id', '=', 'ac.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id'), 'ac', 's')
            ->whereIn('ac.ac_status', [1, 2, 3, 4])
            ->count('ac.id');

        return [
            ['label' => 'Waiting for document', 'count' => $this->submissionActivities->where('documents_pending', true)->count(), 'icon' => 'fas fa-upload'],
            ['label' => 'Ready for student confirmation', 'count' => $this->submissionActivities->where('ready_to_confirm', true)->count(), 'icon' => 'fas fa-file-signature'],
            ['label' => 'Supervisor approval', 'count' => $activityStatuses->filter(fn ($s) => (int) $s === 1)->count(), 'icon' => 'fas fa-user-check'],
            ['label' => 'Administrator approval', 'count' => $activityStatuses->filter(fn ($s) => (int) $s === 2)->count(), 'icon' => 'fas fa-file-signature'],
            ['label' => 'Evaluation', 'count' => $activityStatuses->filter(fn ($s) => (int) $s === 7)->count(), 'icon' => 'fas fa-clipboard-list'],
            ['label' => 'Correction', 'count' => $correctionCount, 'icon' => 'fas fa-pen'],
            ['label' => 'Completed', 'count' => $activityStatuses->filter(fn ($s) => (int) $s === 3)->count(), 'icon' => 'fas fa-check-circle'],
        ];
    }

    private function selectedActivity(): ?object
    {
        if (empty($this->filters['activity_id'])) {
            return null;
        }

        return DB::table('activities')->find($this->filters['activity_id']);
    }

    private function unsigned(int $activityId, int $target, ?string $json, bool $date = false): bool
    {
        $required = $this->signatureFields
            ->get($activityId.':'.$target, collect())
            ->contains(fn ($field) => (int) $field->ff_signature_role === $this->signatureRole);

        if (! $required) {
            return false;
        }

        $signatures = json_decode($json ?? '{}', true) ?: [];
        $key = self::SIGNATURE_KEYS[$this->signatureRole] ?? '';

        return empty($signatures[$key.($date ? '_date' : '')]);
    }

    private function riskItem(object $row, string $issue, $date, string $severity, string $url, bool $fromUpdate = false): array
    {
        $date = Carbon::parse($date);

        return [
            'student_id' => $row->student_id,
            'student_name' => $row->student_name,
            'matric_no' => $row->student_matricno,
            'programme' => $row->prog_code,
            'activity' => $row->act_name,
            'issue' => $issue,
            'waiting_days' => $fromUpdate ? $date->diffInDays(now()) : max(1, $date->diffInDays(now())),
            'severity' => $severity,
            'url' => $url,
        ];
    }

    private function submissionRiskItem(array $activity, string $issue, $date, string $severity, string $url): array
    {
        $date = Carbon::parse($date);

        return [
            'student_id' => $activity['student_id'],
            'student_name' => $activity['student_name'],
            'matric_no' => $activity['matric_no'],
            'programme' => $activity['programme'],
            'activity' => $activity['activity_name'],
            'issue' => $issue,
            'waiting_days' => max(1, $date->diffInDays(now())),
            'severity' => $severity,
            'url' => $url,
        ];
    }

    private function activityScope(Builder $query, string $recordAlias, string $studentAlias): Builder
    {
        $this->studentScope($query, $studentAlias);
        $query->where($recordAlias.'.semester_id', $this->filters['semester_id']);

        if (! empty($this->filters['activity_id'])) {
            $query->where($recordAlias.'.activity_id', $this->filters['activity_id']);
        }

        return $query;
    }

    private function studentScope(Builder $query, string $alias): Builder
    {
        if (! empty($this->filters['programme_id'])) {
            $query->where($alias.'.programme_id', $this->filters['programme_id']);
        }

        if (! empty($this->filters['mode'])) {
            $query->where('p.prog_mode', $this->filters['mode']);
        }

        return $query;
    }
}
