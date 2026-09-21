<?php

namespace App\Services;

use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupervisorDashboardMetrics
{
    private const SIGNATURE_KEYS = [
        2 => 'sv_signature',
        3 => 'cosv_signature',
    ];

    private Staff $staff;

    private array $filters = [];

    private Collection $signatureFields;

    private Collection $submissionActivities;

    public function __construct(private SubmissionActivityProgress $submissionProgress)
    {
    }

    public function forStaff(Staff $staff, array $filters): array
    {
        $this->staff = $staff;
        $this->filters = $filters;
        $this->signatureFields = DB::table('activity_forms as af')
            ->join('form_fields as ff', 'ff.af_id', '=', 'af.id')
            ->where('ff.ff_category', 6)
            ->orderBy('ff.ff_order')
            ->get(['af.activity_id', 'af.af_target', 'ff.ff_signature_role'])
            ->groupBy(fn ($field) => $field->activity_id.':'.$field->af_target);
        $this->submissionActivities = $this->submissionProgress->forScope($filters, $staff->id);

        $semester = DB::table('semesters')->find($filters['semester_id']);
        $actions = $this->actionRecords();
        $attention = $this->attentionRecords();
        $deadlines = $this->upcomingDeadlines();
        $studentRows = $this->studentRows($actions, $attention, $deadlines);
        $activityCounts = $this->activityCounts();

        return [
            'semester' => $semester,
            'filters' => $filters,
            'options' => $this->filterOptions(),
            'actions' => $this->actionCards($actions),
            'kpis' => [
                'supervised_students' => count($studentRows),
                'completed_activities' => $activityCounts['completed'],
                'in_progress_activities' => $activityCounts['in_progress'],
                'at_risk_students' => collect($attention)->pluck('student_id')->unique()->count(),
                'completion_rate' => $activityCounts['total'] > 0
                    ? round(($activityCounts['completed'] / $activityCounts['total']) * 100, 1)
                    : 0,
                'due_soon' => count($deadlines),
            ],
            'attention' => array_slice($attention, 0, 10),
            'attention_total' => count($attention),
            'deadlines' => array_slice($deadlines, 0, 8),
            'students' => array_slice($studentRows, 0, 10),
            'students_total' => count($studentRows),
            'definitions' => [
                'completion_rate' => 'Completed student activities divided by all recorded activities for your supervised students under the selected filters.',
                'at_risk' => 'Supervised students with an overdue item or an item waiting more than 7 days.',
            ],
        ];
    }

    private function filterOptions(): array
    {
        $studentIds = DB::table('supervisions')->where('staff_id', $this->staff->id)->pluck('student_id');

        return [
            'semesters' => DB::table('semesters')->orderByDesc('sem_startdate')->get(['id', 'sem_label', 'sem_status']),
            'programmes' => DB::table('programmes as p')
                ->join('students as s', 's.programme_id', '=', 'p.id')
                ->whereIn('s.id', $studentIds)
                ->distinct()
                ->orderBy('p.prog_code')
                ->get(['p.id', 'p.prog_code', 'p.prog_name', 'p.prog_mode']),
            'modes' => DB::table('programmes as p')
                ->join('students as s', 's.programme_id', '=', 'p.id')
                ->whereIn('s.id', $studentIds)
                ->whereNotNull('p.prog_mode')
                ->distinct()
                ->orderBy('p.prog_mode')
                ->pluck('p.prog_mode'),
            'activities' => DB::table('activities')->orderBy('act_name')->get(['id', 'act_name']),
        ];
    }

    private function actionRecords(): array
    {
        $records = [
            'submissions' => [],
            'nominations' => [],
            'evaluations' => [],
            'corrections' => [],
        ];

        $submissionRows = $this->supervisedActivityScope(DB::table('student_activities as sa')
            ->join('students as s', 's.id', '=', 'sa.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }), 'sa')
            ->where('sa.sa_status', 1)
            ->get(['sa.id', 'sa.student_id', 'sa.activity_id', 'sa.sa_signature_data', 'sv.supervision_role']);

        foreach ($submissionRows as $row) {
            $role = (int) $row->supervision_role + 1;
            if ($this->unsigned($row->activity_id, 1, $role, $row->sa_signature_data)) {
                $records['submissions'][(string) $row->id] = $row;
            }
        }

        $nominationRows = $this->supervisedActivityScope(DB::table('nominations as n')
            ->join('students as s', 's.id', '=', 'n.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }), 'n')
            ->where('n.nom_status', 1)
            ->where('sv.supervision_role', 1)
            ->get(['n.id', 'n.student_id', 'n.activity_id']);

        foreach ($nominationRows as $row) {
            $records['nominations'][(string) $row->id] = $row;
        }

        $activityRows = $this->supervisedActivityScope(DB::table('student_activities as sa')
            ->join('students as s', 's.id', '=', 'sa.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }), 'sa')
            ->where('s.student_status', 1)
            ->whereNotIn('sa.sa_status', [1, 2, 3, 4, 5, 13])
            ->get(['sa.id', 'sa.student_id', 'sa.activity_id', 'sa.semester_id', 'sv.supervision_role']);

        $evaluationRows = $this->supervisedActivityScope(DB::table('evaluations as e')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }), 'e')
            ->get(['e.id', 'e.student_id', 'e.activity_id', 'e.semester_id', 'e.evaluation_status', 'e.evaluation_signature_data']);
        $evaluationsByActivity = $evaluationRows->groupBy(fn ($row) => $row->student_id.':'.$row->activity_id.':'.$row->semester_id);

        foreach ($activityRows as $activity) {
            $role = (int) $activity->supervision_role + 1;
            $evaluations = $evaluationsByActivity->get($activity->student_id.':'.$activity->activity_id.':'.$activity->semester_id, collect());
            $pending = $evaluations->contains(fn ($evaluation) => (int) $evaluation->evaluation_status === 9
                && $this->unsigned($evaluation->activity_id, 5, $role, $evaluation->evaluation_signature_data));
            $allConfirmed = $evaluations->isNotEmpty()
                && $evaluations->every(fn ($evaluation) => (int) $evaluation->evaluation_status === 8);
            $hasHigherApproval = $this->fields($activity->activity_id, 5)
                ->contains(fn ($field) => in_array((int) $field->ff_signature_role, [4, 5, 6], true));
            $supervisorFinalization = $allConfirmed
                && ! $hasHigherApproval
                && $this->requiresRole($activity->activity_id, 5, $role);

            if ($pending || $supervisorFinalization) {
                $records['evaluations'][(string) $activity->id] = $activity;
            }
        }

        $correctionRows = $this->supervisedActivityScope(DB::table('activity_corrections as ac')
            ->join('students as s', 's.id', '=', 'ac.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }), 'ac')
            ->where('ac.ac_status', 2)
            ->get(['ac.id', 'ac.student_id', 'ac.activity_id', 'ac.ac_signature_data', 'sv.supervision_role']);

        foreach ($correctionRows as $row) {
            $role = (int) $row->supervision_role + 1;
            if ($this->unsigned($row->activity_id, 2, $role, $row->ac_signature_data)) {
                $records['corrections'][(string) $row->id] = $row;
            }
        }

        return $records;
    }

    private function actionCards(array $records): array
    {
        return [
            [
                'key' => 'submissions',
                'label' => 'Submission approvals',
                'count' => count($records['submissions']),
                'icon' => 'fas fa-file-signature',
                'url' => route('my-supervision-submission-approval'),
            ],
            [
                'key' => 'nominations',
                'label' => 'Nominations to prepare',
                'count' => count($records['nominations']),
                'icon' => 'fas fa-user-plus',
                'url' => $this->activityRoute($records['nominations'], 'my-supervision-nomination'),
            ],
            [
                'key' => 'evaluations',
                'label' => 'Evaluation approvals',
                'count' => count($records['evaluations']),
                'icon' => 'fas fa-clipboard-check',
                'url' => $this->activityRoute($records['evaluations'], 'my-supervision-evaluation-approval'),
            ],
            [
                'key' => 'corrections',
                'label' => 'Correction approvals',
                'count' => count($records['corrections']),
                'icon' => 'fas fa-edit',
                'url' => route('my-supervision-correction-approval'),
            ],
        ];
    }

    private function activityCounts(): array
    {
        $activityRows = $this->supervisedActivityScope(DB::table('student_activities as sa')
            ->join('students as s', 's.id', '=', 'sa.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }), 'sa')
            ->get(['sa.id', 'sa.sa_status'])
            ->unique('id');

        return [
            'total' => $activityRows->count(),
            'completed' => $activityRows->filter(fn ($row) => (int) $row->sa_status === 3)->count(),
            'in_progress' => $activityRows->filter(fn ($row) => in_array((int) $row->sa_status, [1, 2, 7, 8, 9, 13], true))->count(),
        ];
    }

    private function attentionRecords(): array
    {
        $now = now();
        $cutoff = now()->subDays(7);
        $items = [];

        foreach ($this->submissionActivities->where('overdue', true) as $activity) {
            $items[] = $this->submissionAttentionItem(
                $activity,
                'Activity submission overdue',
                $activity['overdue_at'],
                'danger',
                route('my-supervision-submission-management')
            );
        }

        $pendingActivities = $this->supervisedActivityScope(DB::table('student_activities as sa')
            ->join('activities as a', 'a.id', '=', 'sa.activity_id')
            ->join('students as s', 's.id', '=', 'sa.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }), 'sa')
            ->whereIn('sa.sa_status', [1, 2, 7, 8, 9, 13])
            ->where('sa.updated_at', '<=', $cutoff)
            ->get(['sa.student_id', 'sa.sa_status', 'sa.updated_at', 's.student_name', 's.student_matricno', 'p.prog_code', 'a.act_name']);

        $activityLabels = [
            1 => 'Supervisor approval pending',
            2 => 'Administrator approval pending',
            7 => 'Evaluation pending',
            8 => 'Correction pending',
            9 => 'Resubmission pending',
            13 => 'Continuation pending',
        ];
        foreach ($pendingActivities as $row) {
            $items[] = $this->attentionItem(
                $row,
                $activityLabels[(int) $row->sa_status] ?? 'Activity pending',
                $row->updated_at,
                'warning',
                (int) $row->sa_status === 1 ? route('my-supervision-submission-approval') : route('my-supervision-student-list')
            );
        }

        $pendingNominations = $this->supervisedActivityScope(DB::table('nominations as n')
            ->join('activities as a', 'a.id', '=', 'n.activity_id')
            ->join('students as s', 's.id', '=', 'n.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }), 'n')
            ->whereIn('n.nom_status', [1, 2, 3, 5])
            ->where('n.updated_at', '<=', $cutoff)
            ->get(['n.student_id', 'n.updated_at', 's.student_name', 's.student_matricno', 'p.prog_code', 'a.act_name']);

        foreach ($pendingNominations as $row) {
            $items[] = $this->attentionItem(
                $row,
                'Nomination waiting over 7 days',
                $row->updated_at,
                'warning',
                route('my-supervision-nomination', Str::slug($row->act_name))
            );
        }

        $pendingEvaluations = $this->supervisedActivityScope(DB::table('evaluations as e')
            ->join('activities as a', 'a.id', '=', 'e.activity_id')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }), 'e')
            ->whereIn('e.evaluation_status', [1, 7, 9, 10])
            ->where('e.updated_at', '<=', $cutoff)
            ->get(['e.student_id', 'e.updated_at', 's.student_name', 's.student_matricno', 'p.prog_code', 'a.act_name']);

        foreach ($pendingEvaluations as $row) {
            $items[] = $this->attentionItem(
                $row,
                'Evaluation waiting over 7 days',
                $row->updated_at,
                'warning',
                route('my-supervision-evaluation-approval', Str::slug($row->act_name))
            );
        }

        $overdueCorrections = $this->supervisedActivityScope(DB::table('activity_corrections as ac')
            ->join('activities as a', 'a.id', '=', 'ac.activity_id')
            ->join('students as s', 's.id', '=', 'ac.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }), 'ac')
            ->whereIn('ac.ac_status', [1, 2, 3, 4])
            ->whereNotNull('ac.ac_duedate')
            ->where('ac.ac_duedate', '<', $now)
            ->get(['ac.student_id', 's.student_name', 's.student_matricno', 'p.prog_code', 'a.act_name', 'ac.ac_duedate']);

        foreach ($overdueCorrections as $row) {
            $items[] = $this->attentionItem($row, 'Correction overdue', $row->ac_duedate, 'danger', route('my-supervision-correction-approval'));
        }

        $stalledCorrections = $this->supervisedActivityScope(DB::table('activity_corrections as ac')
            ->join('activities as a', 'a.id', '=', 'ac.activity_id')
            ->join('students as s', 's.id', '=', 'ac.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }), 'ac')
            ->whereIn('ac.ac_status', [1, 2, 3, 4])
            ->where('ac.updated_at', '<=', $cutoff)
            ->get(['ac.student_id', 'ac.updated_at', 's.student_name', 's.student_matricno', 'p.prog_code', 'a.act_name']);

        foreach ($stalledCorrections as $row) {
            $items[] = $this->attentionItem($row, 'Correction waiting over 7 days', $row->updated_at, 'warning', route('my-supervision-correction-approval'));
        }

        return collect($items)
            ->unique(fn ($item) => $item['student_id'].'|'.$item['activity'].'|'.$item['issue'])
            ->sortByDesc(fn ($item) => (($item['severity'] === 'danger' ? 2 : 1) * 10000) + $item['waiting_days'])
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
                'url' => route('my-supervision-submission-management'),
            ];
        }

        $corrections = $this->supervisedActivityScope(DB::table('activity_corrections as ac')
            ->join('activities as a', 'a.id', '=', 'ac.activity_id')
            ->join('students as s', 's.id', '=', 'ac.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }), 'ac')
            ->whereIn('ac.ac_status', [1, 2, 3, 4])
            ->whereBetween('ac.ac_duedate', [now(), $end])
            ->get(['ac.student_id', 's.student_name', 's.student_matricno', 'a.act_name', 'ac.ac_duedate']);

        foreach ($corrections as $row) {
            $items[] = $this->deadlineItem($row, 'Correction', $row->ac_duedate, route('my-supervision-correction-approval'));
        }

        return collect($items)->sortBy('due_at')->values()->all();
    }

    private function studentRows(array $actions, array $attention, array $deadlines): array
    {
        $students = $this->studentScope(DB::table('student_semesters as ss')
            ->join('students as s', 's.id', '=', 'ss.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }))
            ->where('ss.semester_id', $this->filters['semester_id'])
            ->get(['s.id', 's.student_name', 's.student_matricno', 'p.prog_code', 'p.prog_mode', 'ss.ss_status', 'sv.supervision_role'])
            ->unique('id');

        $activities = $this->supervisedActivityScope(DB::table('student_activities as sa')
            ->join('students as s', 's.id', '=', 'sa.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('supervisions as sv', function ($join) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $this->staff->id);
            }), 'sa')
            ->get(['sa.id', 'sa.student_id', 'sa.sa_status'])
            ->unique('id')
            ->groupBy('student_id');

        $actionStudents = collect($actions)->flatten(1)->groupBy('student_id');
        $attentionStudents = collect($attention)->groupBy('student_id');
        $deadlineStudents = collect($deadlines)->groupBy('student_id');
        $enrollmentLabels = [1 => 'Active', 2 => 'Inactive', 3 => 'Barred', 4 => 'Completed'];

        return $students->map(function ($student) use ($activities, $actionStudents, $attentionStudents, $deadlineStudents, $enrollmentLabels) {
            $studentActivities = $activities->get($student->id, collect());
            $nextDeadline = $deadlineStudents->get($student->id, collect())->sortBy('due_at')->first();
            $risks = $attentionStudents->get($student->id, collect());

            return [
                'student_id' => $student->id,
                'student_name' => $student->student_name,
                'matric_no' => $student->student_matricno,
                'programme' => $student->prog_code.' · '.$student->prog_mode,
                'supervision_role' => (int) $student->supervision_role === 1 ? 'Main supervisor' : 'Co-supervisor',
                'enrollment_status' => $enrollmentLabels[(int) $student->ss_status] ?? 'Unknown',
                'completed' => $studentActivities->where('sa_status', 3)->count(),
                'in_progress' => $studentActivities->filter(fn ($row) => in_array((int) $row->sa_status, [1, 2, 7, 8, 9, 13], true))->count(),
                'pending_actions' => $actionStudents->get($student->id, collect())->count(),
                'risk_count' => $risks->count(),
                'next_deadline' => $nextDeadline,
                'url' => route('my-supervision-student-list'),
            ];
        })->sortBy([
            fn ($a, $b) => $b['risk_count'] <=> $a['risk_count'],
            fn ($a, $b) => $b['pending_actions'] <=> $a['pending_actions'],
            fn ($a, $b) => strcasecmp($a['student_name'], $b['student_name']),
        ])->values()->all();
    }

    private function activityRoute(array $records, string $route): string
    {
        $activityId = $this->filters['activity_id'] ?: optional(collect($records)->first())->activity_id;
        $activityName = $activityId ? DB::table('activities')->where('id', $activityId)->value('act_name') : null;

        return $activityName ? route($route, Str::slug($activityName)) : '#supervisor-attention';
    }

    private function fields(int $activityId, int $target): Collection
    {
        return $this->signatureFields->get($activityId.':'.$target, collect());
    }

    private function requiresRole(int $activityId, int $target, int $role): bool
    {
        return $this->fields($activityId, $target)
            ->contains(fn ($field) => (int) $field->ff_signature_role === $role);
    }

    private function unsigned(int $activityId, int $target, int $role, ?string $json): bool
    {
        if (! $this->requiresRole($activityId, $target, $role)) {
            return false;
        }

        $signatures = json_decode($json ?? '{}', true) ?: [];
        $key = self::SIGNATURE_KEYS[$role] ?? '';

        return empty($signatures[$key]);
    }

    private function attentionItem(object $row, string $issue, $date, string $severity, string $url): array
    {
        return [
            'student_id' => $row->student_id,
            'student_name' => $row->student_name,
            'matric_no' => $row->student_matricno,
            'programme' => $row->prog_code,
            'activity' => $row->act_name,
            'issue' => $issue,
            'waiting_days' => max(1, Carbon::parse($date)->diffInDays(now())),
            'severity' => $severity,
            'url' => $url,
        ];
    }

    private function submissionAttentionItem(array $activity, string $issue, $date, string $severity, string $url): array
    {
        return [
            'student_id' => $activity['student_id'],
            'student_name' => $activity['student_name'],
            'matric_no' => $activity['matric_no'],
            'programme' => $activity['programme'],
            'activity' => $activity['activity_name'],
            'issue' => $issue,
            'waiting_days' => max(1, Carbon::parse($date)->diffInDays(now())),
            'severity' => $severity,
            'url' => $url,
        ];
    }

    private function deadlineItem(object $row, string $type, $date, string $url): array
    {
        return [
            'student_id' => $row->student_id,
            'student_name' => $row->student_name,
            'matric_no' => $row->student_matricno,
            'activity' => $row->act_name,
            'type' => $type,
            'due_at' => $date,
            'days' => now()->diffInDays(Carbon::parse($date), false),
            'url' => $url,
        ];
    }

    private function supervisedActivityScope(Builder $query, string $recordAlias): Builder
    {
        $this->studentScope($query);
        $query->where($recordAlias.'.semester_id', $this->filters['semester_id']);

        if (! empty($this->filters['activity_id'])) {
            $query->where($recordAlias.'.activity_id', $this->filters['activity_id']);
        }

        return $query;
    }

    private function studentScope(Builder $query): Builder
    {
        if (! empty($this->filters['programme_id'])) {
            $query->where('s.programme_id', $this->filters['programme_id']);
        }

        if (! empty($this->filters['mode'])) {
            $query->where('p.prog_mode', $this->filters['mode']);
        }

        return $query;
    }
}
