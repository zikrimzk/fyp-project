<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Support\Facades\DB;

/** Read-only, request-local counts. A count represents a distinct actionable record. */
class StaffWorkCounts
{
    private array $work = [];

    private $fields;

    private const SIGNATURES = [2 => 'sv_signature', 3 => 'cosv_signature', 4 => 'comm_signature', 5 => 'deputy_dean_signature', 6 => 'dean_signature'];

    private function fields(int $activity, int $target)
    {
        return $this->fields->get($activity.':'.$target, collect());
    }

    private function unsigned($record, int $target, int $role, $json, bool $date = false): bool
    {
        $required = $this->fields((int) $record->activity_id, $target)->contains(fn ($f) => (int) $f->ff_signature_role === $role);
        $signatures = json_decode($json ?? '{}', true) ?: [];
        $key = self::SIGNATURES[$role] ?? '';

        return $required && empty($signatures[$key.($date ? '_date' : '')]);
    }

    private function add(string $route, $id, ?string $activity = null): void
    {
        $parameters = $activity === null ? [] : [strtolower(str_replace(' ', '-', $activity))];
        $url = route($route, $parameters);
        $this->work[$url][(string) $id] = true;
    }

    public function forStaff(Staff $staff): array
    {
        $this->work = [];
        $this->fields = DB::table('activity_forms as af')->join('form_fields as ff', 'ff.af_id', '=', 'af.id')
            ->where('ff.ff_category', 6)->orderBy('ff.ff_order')
            ->get(['af.activity_id', 'af.af_target', 'ff.ff_signature_role', 'ff.ff_signature_key'])
            ->groupBy(fn ($f) => $f->activity_id.':'.$f->af_target);
        $supervisions = DB::table('supervisions')->where('staff_id', $staff->id)->get()->groupBy('student_id');
        $higherRole = [1 => 4, 3 => 5, 4 => 6][(int) $staff->staff_role] ?? null;
        $names = DB::table('activities')->pluck('act_name', 'id');
        $active = DB::table('students')->where('student_status', 1)->pluck('id')->flip();
        $activities = DB::table('student_activities')->get(['id', 'student_id', 'activity_id', 'semester_id', 'sa_status', 'sa_signature_data']);
        $evaluations = DB::table('evaluations')->get(['id', 'student_id', 'staff_id', 'activity_id', 'semester_id', 'evaluation_status', 'evaluation_isFinal', 'evaluation_signature_data']);
        $evaluationsByActivity = $evaluations->groupBy(fn ($r) => $r->student_id.':'.$r->activity_id.':'.$r->semester_id);

        foreach ($activities as $row) {
            $roles = $supervisions->get($row->student_id, collect());
            foreach ($roles as $supervision) {
                if ((int) $row->sa_status === 1 && $this->unsigned($row, 1, (int) $supervision->supervision_role + 1, $row->sa_signature_data)) {
                    $this->add('my-supervision-submission-approval', $row->id);
                }
            }
            // Status 2 is the higher-up approval stage; status 1 is still with supervisors.
            if ($higherRole && $roles->isEmpty() && (int) $row->sa_status === 2 && $this->unsigned($row, 1, $higherRole, $row->sa_signature_data, true)) {
                $this->add('submission-approval', $row->id);
            }
            if (! $active->has($row->student_id) || in_array((int) $row->sa_status, [1, 2, 3, 4, 5, 13], true)) {
                continue;
            }
            $evaluationRows = $evaluationsByActivity->get($row->student_id.':'.$row->activity_id.':'.$row->semester_id, collect());
            $allDone = $evaluationRows->isNotEmpty() && $evaluationRows->every(fn ($e) => (int) $e->evaluation_status === 8);
            $hasHigherApproval = $this->fields((int) $row->activity_id, 5)->contains(fn ($f) => in_array((int) $f->ff_signature_role, [4, 5, 6], true));
            foreach ($roles as $supervision) {
                $role = (int) $supervision->supervision_role + 1;
                $pending = $evaluationRows->contains(fn ($e) => (int) $e->evaluation_status === 9 && $this->unsigned($e, 5, $role, $e->evaluation_signature_data));
                if ($pending || ($allDone && ! $hasHigherApproval && $this->unsigned($row, 5, $role, '{}'))) {
                    $this->add('my-supervision-evaluation-approval', $row->id, $names[$row->activity_id]);
                }
            }
            if ($higherRole && $roles->isEmpty()) {
                $pending = $evaluationRows->contains(fn ($e) => (int) $e->evaluation_status === 10 && $this->unsigned($e, 5, $higherRole, $e->evaluation_signature_data));
                if ($pending || ($allDone && $this->unsigned($row, 5, $higherRole, '{}'))) {
                    $this->add('evaluation-approval', $row->id, $names[$row->activity_id]);
                }
            }
        }

        $nominations = DB::table('nominations')->get(['id', 'student_id', 'activity_id', 'nom_status', 'nom_signature_data']);
        foreach ($nominations as $row) {
            if (! $active->has($row->student_id)) {
                continue;
            }
            if ((int) $row->nom_status === 1 && $supervisions->get($row->student_id, collect())->contains(fn ($s) => (int) $s->supervision_role === 1)) {
                $this->add('my-supervision-nomination', $row->id, $names[$row->activity_id]);
            }
            if ($higherRole === 4 && in_array((int) $row->nom_status, [2, 5], true)) {
                $this->add('nomination-approval', $row->id, $names[$row->activity_id]);
            } elseif (in_array($higherRole, [5, 6], true) && (int) $row->nom_status === 3 && $this->unsigned($row, 3, $higherRole, $row->nom_signature_data)) {
                $this->add('nomination-approval', $row->id, $names[$row->activity_id]);
            }
        }

        $evaluators = DB::table('evaluators as e')->join('nominations as n', 'n.id', '=', 'e.nom_id')
            ->where('e.eva_status', 3)->orderBy('e.updated_at')->orderBy('e.id')
            ->get(['n.student_id', 'n.activity_id', 'e.staff_id', 'e.eva_role']);
        $assigned = $evaluators->where('staff_id', $staff->id)->groupBy(fn ($e) => $e->student_id.':'.$e->activity_id);
        $currentSemester = DB::table('semesters')->where('sem_status', 1)->value('id');
        $blocked = $activities->whereIn('sa_status', [1, 2, 4, 5])->groupBy(fn ($r) => $r->student_id.':'.$r->activity_id);
        $ready = $activities->whereNotIn('sa_status', [1, 2, 4, 5])
            ->groupBy(fn ($r) => $r->student_id.':'.$r->activity_id.':'.$r->semester_id);
        foreach ($evaluations->where('staff_id', $staff->id) as $row) {
            if (! $active->has($row->student_id) || (int) $row->evaluation_isFinal === 1 || in_array((int) $row->evaluation_status, [8, 9, 10], true)) {
                continue;
            }
            if ((int) $row->semester_id !== (int) $currentSemester || $blocked->has($row->student_id.':'.$row->activity_id)) {
                continue;
            }
            if (! $ready->has($row->student_id.':'.$row->activity_id.':'.$row->semester_id)) {
                continue;
            }
            foreach ($assigned->get($row->student_id.':'.$row->activity_id, collect()) as $assignment) {
                $this->add((int) $assignment->eva_role === 2 ? 'chairman-evaluation' : 'examiner-panel-evaluation', $row->id, $names[$row->activity_id]);
            }
        }

        foreach (DB::table('activity_corrections')->whereIn('ac_status', [2, 3, 4])->get() as $row) {
            $roles = $supervisions->get($row->student_id, collect());
            foreach ($roles as $supervision) {
                if ((int) $row->ac_status === 2 && $this->unsigned($row, 2, (int) $supervision->supervision_role + 1, $row->ac_signature_data)) {
                    $this->add('my-supervision-correction-approval', $row->id);
                }
            }
            if ($higherRole && $roles->isEmpty() && (int) $row->ac_status === 4 && $this->unsigned($row, 2, $higherRole, $row->ac_signature_data, true)) {
                $this->add('correction-approval', $row->id);
            }
            if ((int) $row->ac_status === 3) {
                $examiners = $evaluators->where('student_id', $row->student_id)->where('activity_id', $row->activity_id)->where('eva_role', 1)->pluck('staff_id')->values();
                $index = $examiners->search(fn ($id) => (int) $id === (int) $staff->id);
                $keys = $this->fields((int) $row->activity_id, 2)->where('ff_signature_role', 8)->pluck('ff_signature_key')->values();
                $signatures = json_decode($row->ac_signature_data ?? '{}', true) ?: [];
                if ($index !== false && isset($keys[$index]) && empty($signatures[$keys[$index]])) {
                    $this->add('examiner-panel-correction-approval', $row->id);
                }
            }
        }

        return array_map('count', $this->work);
    }
}
