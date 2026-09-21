<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Converts document-level submission rows into one truthful activity-level state.
 * Optional documents are alternatives when an activity has no required documents.
 */
class SubmissionActivityProgress
{
    private const CONFIRMED_ACTIVITY_STATUSES = [1, 2, 3, 7, 8, 12, 13];

    public function forScope(array $filters, ?int $supervisorId = null): Collection
    {
        $query = DB::table('submissions as sub')
            ->join('documents as d', 'd.id', '=', 'sub.document_id')
            ->join('activities as a', 'a.id', '=', 'd.activity_id')
            ->join('students as s', 's.id', '=', 'sub.student_id')
            ->join('programmes as p', 'p.id', '=', 's.programme_id')
            ->join('procedures as pr', function ($join) {
                $join->on('pr.activity_id', '=', 'd.activity_id')
                    ->on('pr.programme_id', '=', 's.programme_id');
            })
            ->where('sub.semester_id', $filters['semester_id'])
            ->whereIn('sub.submission_status', [1, 2, 3, 4]);

        if ($supervisorId !== null) {
            $query->join('supervisions as sv', function ($join) use ($supervisorId) {
                $join->on('sv.student_id', '=', 's.id')->where('sv.staff_id', $supervisorId);
            });
        }
        if (! empty($filters['programme_id'])) {
            $query->where('s.programme_id', $filters['programme_id']);
        }
        if (! empty($filters['mode'])) {
            $query->where('p.prog_mode', $filters['mode']);
        }
        if (! empty($filters['activity_id'])) {
            $query->where('d.activity_id', $filters['activity_id']);
        }

        $documents = $query
            ->get([
                'sub.id as submission_id',
                'sub.student_id',
                'sub.semester_id',
                'sub.submission_status',
                'sub.submission_duedate',
                'd.activity_id',
                'd.isRequired',
                'a.act_name',
                's.student_name',
                's.student_matricno',
                'p.prog_code',
                'pr.is_repeatable',
            ])
            ->unique('submission_id');

        if ($documents->isEmpty()) {
            return collect();
        }

        $studentIds = $documents->pluck('student_id')->unique();
        $activityIds = $documents->pluck('activity_id')->unique();
        $confirmed = DB::table('student_activities')
            ->whereIn('student_id', $studentIds)
            ->whereIn('activity_id', $activityIds)
            ->whereIn('sa_status', self::CONFIRMED_ACTIVITY_STATUSES)
            ->get(['student_id', 'activity_id', 'semester_id', 'sa_status'])
            ->groupBy(fn ($row) => $row->student_id.':'.$row->activity_id);

        return $documents
            ->groupBy(fn ($row) => $row->student_id.':'.$row->activity_id.':'.$row->semester_id)
            ->map(function (Collection $rows) use ($confirmed) {
                $first = $rows->first();
                $activityRecords = $confirmed->get($first->student_id.':'.$first->activity_id, collect());
                $isConfirmed = (int) $first->is_repeatable === 1
                    ? $activityRecords->contains(fn ($row) => (int) $row->semester_id === (int) $first->semester_id)
                    : $activityRecords->isNotEmpty();

                $required = $rows->where('isRequired', 1);
                $optional = $rows->where('isRequired', 0);
                $activeOptional = $optional->filter(fn ($row) => in_array((int) $row->submission_status, [1, 3, 4], true));
                $hasActiveSubmission = $rows->contains(fn ($row) => in_array((int) $row->submission_status, [1, 3, 4], true));
                $readyToConfirm = $required->isNotEmpty()
                    ? $required->every(fn ($row) => (int) $row->submission_status === 3)
                    : $activeOptional->contains(fn ($row) => (int) $row->submission_status === 3);
                $overdue = $required->isNotEmpty()
                    ? $required->contains(fn ($row) => (int) $row->submission_status === 4)
                    : ! $readyToConfirm
                        && $activeOptional->contains(fn ($row) => (int) $row->submission_status === 4)
                        && ! $activeOptional->contains(fn ($row) => (int) $row->submission_status === 1);
                $obligations = $required->isNotEmpty() ? $required : $optional;
                $openObligations = $obligations->where('submission_status', 1)->whereNotNull('submission_duedate');
                $nextDue = $readyToConfirm ? null : ($required->isNotEmpty()
                    ? $openObligations->sortBy('submission_duedate')->first()
                    : $openObligations->sortByDesc('submission_duedate')->first());

                return [
                    'student_id' => $first->student_id,
                    'semester_id' => $first->semester_id,
                    'activity_id' => $first->activity_id,
                    'activity_name' => $first->act_name,
                    'student_name' => $first->student_name,
                    'matric_no' => $first->student_matricno,
                    'programme' => $first->prog_code,
                    'is_confirmed' => $isConfirmed,
                    'has_active_submission' => $hasActiveSubmission,
                    'ready_to_confirm' => ! $isConfirmed && $readyToConfirm,
                    'documents_pending' => ! $isConfirmed && $hasActiveSubmission && ! $readyToConfirm,
                    'overdue' => ! $isConfirmed && $overdue,
                    'overdue_at' => $overdue
                        ? optional($obligations->where('submission_status', 4)->sortBy('submission_duedate')->first())->submission_duedate
                        : null,
                    'next_due_at' => ! $isConfirmed && $nextDue ? $nextDue->submission_duedate : null,
                ];
            })
            ->filter(fn ($activity) => $activity['has_active_submission'] || $activity['is_confirmed'])
            ->values();
    }
}
