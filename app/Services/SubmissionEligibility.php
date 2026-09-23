<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Evaluation;
use App\Models\Nomination;
use App\Models\Procedure;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\StudentSemester;
use App\Models\Submission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmissionEligibility
{
    private ?array $snapshot = null;

    // Read-only overview snapshot avoids issuing eligibility queries for every table row.
    public function preloadOverview(?Semester $semester = null): void
    {
        $this->snapshot = [
            'enrollments' => StudentSemester::get(['student_id', 'semester_id', 'ss_status'])->groupBy('student_id'),
            'activities' => StudentActivity::get(['student_id', 'activity_id', 'semester_id', 'sa_status'])->groupBy('student_id'),
            'submissions' => Submission::join('documents', 'documents.id', '=', 'submissions.document_id')
                ->select('submissions.student_id', 'submissions.semester_id', 'submissions.submission_status', 'documents.activity_id')
                ->get()->groupBy('student_id'),
            'procedures' => Procedure::all()->groupBy('programme_id'),
        ];
        if ($semester && (int) $semester->sem_status !== 1) {
            $semesterIds = Semester::where('sem_startdate', '<=', $semester->sem_startdate)->pluck('id');
            $this->snapshot['historical'] = true;
            foreach (['enrollments', 'activities', 'submissions'] as $key) {
                $this->snapshot[$key] = $this->snapshot[$key]->map(fn ($rows) => $rows->whereIn('semester_id', $semesterIds));
            }
        }
    }

    public function overviewSemesterCount(Student $student, Semester $semester): int
    {
        if ((int) $semester->sem_status === 1) return (int) $student->student_semcount;
        return $this->snapshot['enrollments']->get($student->id, collect())->whereIn('ss_status', [1, 4])->count();
    }

    public function enrolled(Student $student, Semester $semester): bool
    {
        if ($this->snapshot !== null) {
            if ($this->snapshot['historical'] ?? false) {
                return $this->snapshot['enrollments']->get($student->id, collect())
                    ->where('semester_id', $semester->id)->whereIn('ss_status', [1, 4])->isNotEmpty();
            }
            return (int) $student->student_status === 1 && $this->snapshot['enrollments']->get($student->id, collect())
                ->where('semester_id', $semester->id)->where('ss_status', 1)->isNotEmpty();
        }

        return (int) $student->student_status === 1 && StudentSemester::where('student_id', $student->id)
            ->where('semester_id', $semester->id)->where('ss_status', 1)->exists();
    }

    public function submissions(Student $student, $procedure, Semester $semester)
    {
        return Submission::where('student_id', $student->id)
            ->whereIn('document_id', Document::where('activity_id', $procedure->activity_id)->select('id'))
            ->when($procedure->is_repeatable, fn ($q) => $q->where('semester_id', $semester->id));
    }

    public function activities(Student $student, $procedure, Semester $semester)
    {
        return StudentActivity::where('student_id', $student->id)->where('activity_id', $procedure->activity_id)
            ->when($procedure->is_repeatable, fn ($q) => $q->where('semester_id', $semester->id));
    }

    // Status values used by the eligibility overview, separate from submission statuses.
    public function status(Student $student, $procedure, Semester $semester): int
    {
        if ($this->snapshot !== null) {
            $activities = $this->snapshot['activities']->get($student->id, collect())->where('activity_id', $procedure->activity_id);
            $submissions = $this->snapshot['submissions']->get($student->id, collect())->where('activity_id', $procedure->activity_id);
            if ($procedure->is_repeatable) {
                $activities = $activities->where('semester_id', $semester->id);
                $submissions = $submissions->where('semester_id', $semester->id);
            }
            if ($activities->where('sa_status', 3)->isNotEmpty()) {
                return 5;
            }
            if ($submissions->where('submission_status', 5)->isNotEmpty()) {
                return 6;
            }
            if (! $this->enrolled($student, $semester)) {
                return 8;
            }
            if ($activities->isNotEmpty()) {
                return 4;
            }
            if ($submissions->whereIn('submission_status', [1, 3, 4])->isNotEmpty()) {
                return 2;
            }

            return $this->requirements($student, $procedure);
        }
        $activities = $this->activities($student, $procedure, $semester);
        if ((clone $activities)->where('sa_status', 3)->exists()) {
            return 5;
        }
        if ($this->submissions($student, $procedure, $semester)->where('submission_status', 5)->exists()) {
            return 6;
        }
        if (! $this->enrolled($student, $semester)) {
            return 8;
        }
        if ($activities->exists()) {
            return 4;
        }
        if ($this->submissions($student, $procedure, $semester)->whereIn('submission_status', [1, 3, 4])->exists()) {
            return 2;
        }

        return $this->requirements($student, $procedure);
    }

    public function requirements(Student $student, $procedure): int
    {
        if ((int) $student->student_semcount < (int) $procedure->timeline_sem) {
            return 7;
        }
        // Open activities depend only on reaching their configured semester timeline.
        if ((int) $procedure->init_status === 1) {
            return 1;
        }
        if ($this->snapshot !== null) {
            $previous = $this->snapshot['procedures']->get($student->programme_id, collect())
                ->where('act_seq', '<', $procedure->act_seq)->pluck('activity_id');
            $completed = $this->snapshot['activities']->get($student->id, collect())->where('sa_status', 3)->pluck('activity_id');

            return $previous->diff($completed)->isEmpty() ? 1 : 3;
        }
        $previous = Procedure::where('programme_id', $student->programme_id)
            ->where('act_seq', '<', $procedure->act_seq)->pluck('activity_id');
        $completed = StudentActivity::where('student_id', $student->id)->where('sa_status', 3)
            ->whereIn('activity_id', $previous)->distinct()->pluck('activity_id');

        return $previous->diff($completed)->isEmpty() ? 1 : 3;
    }

    public function sync(string $matricno): void
    {
        $this->snapshot = null;
        DB::transaction(function () use ($matricno) {
            $student = Student::where('student_matricno', $matricno)->lockForUpdate()->firstOrFail();
            $semester = Semester::where('sem_status', 1)->firstOrFail();
            foreach (Procedure::where('programme_id', $student->programme_id)->orderBy('act_seq')->get() as $procedure) {
                if ($procedure->is_repeatable) {
                    // Close obsolete drafts without altering confirmed activity history.
                    Submission::where('student_id', $student->id)
                        ->whereIn('document_id', Document::where('activity_id', $procedure->activity_id)->select('id'))
                        ->where('semester_id', '<>', $semester->id)->whereIn('submission_status', [1, 3, 4])
                        ->whereNotExists(function ($query) use ($procedure) {
                            $query->selectRaw('1')->from('student_activities')
                                ->whereColumn('student_activities.student_id', 'submissions.student_id')
                                ->whereColumn('student_activities.semester_id', 'submissions.semester_id')
                                ->where('student_activities.activity_id', $procedure->activity_id);
                        })->update(['submission_status' => 5]);
                }
                if (! $this->enrolled($student, $semester)) {
                    $this->resetEvaluationWorkflow($student, $procedure, $semester);
                    $this->lock($student, $procedure, $semester);

                    continue;
                }
                $status = $this->status($student, $procedure, $semester);
                if ($status === 4) {
                    $this->ensureFreshNomination($student, $procedure, $semester);

                    continue;
                }
                if (in_array($status, [5, 6], true)) {
                    continue;
                }
                if ($this->requirements($student, $procedure) === 1) {
                    $this->open($student, $procedure, $semester);
                } else {
                    $this->lock($student, $procedure, $semester);
                    // Retain locked placeholders for milestones not yet reached.
                    foreach (Document::where('activity_id', $procedure->activity_id)->get() as $document) {
                        $this->submissions($student, $procedure, $semester)->firstOrCreate(
                            ['document_id' => $document->id],
                            ['student_id' => $student->id, 'semester_id' => $semester->id,
                                'submission_document' => '-', 'submission_status' => 2]
                        );
                    }
                }
            }
        });
    }

    private function resetEvaluationWorkflow(Student $student, $procedure, Semester $semester): void
    {
        if (! $procedure->is_haveEva
            || $this->activities($student, $procedure, $semester)->where('sa_status', 3)->exists()) {
            return;
        }

        $nominations = Nomination::where('student_id', $student->id)
            ->where('activity_id', $procedure->activity_id)
            ->when($procedure->is_repeatable, fn ($query) => $query->where('semester_id', $semester->id))
            ->lockForUpdate()
            ->pluck('id');

        if ($nominations->isNotEmpty()) {
            DB::table('evaluators')->whereIn('nom_id', $nominations)->delete();
            Nomination::whereIn('id', $nominations)->delete();
        }

        Evaluation::where('student_id', $student->id)
            ->where('activity_id', $procedure->activity_id)
            ->when($procedure->is_repeatable, fn ($query) => $query->where('semester_id', $semester->id))
            ->delete();
    }

    private function ensureFreshNomination(Student $student, $procedure, Semester $semester): void
    {
        if (! $procedure->is_haveEva) {
            return;
        }

        $nominationExists = Nomination::where('student_id', $student->id)
            ->where('activity_id', $procedure->activity_id)
            ->when($procedure->is_repeatable, fn ($query) => $query->where('semester_id', $semester->id))
            ->exists();
        $evaluationExists = Evaluation::where('student_id', $student->id)
            ->where('activity_id', $procedure->activity_id)
            ->when($procedure->is_repeatable, fn ($query) => $query->where('semester_id', $semester->id))
            ->exists();

        if (! $nominationExists && ! $evaluationExists) {
            Nomination::create([
                'student_id' => $student->id,
                'activity_id' => $procedure->activity_id,
                'semester_id' => $semester->id,
                'nom_status' => 1,
            ]);
        }
    }

    private function lock(Student $student, $procedure, Semester $semester): void
    {
        // Confirmed work remains historical; enrollment checks block further student actions.
        if ($this->activities($student, $procedure, $semester)->exists()) {
            return;
        }
        $this->submissions($student, $procedure, $semester)->whereIn('submission_status', [1, 3, 4])
            ->update(['submission_status' => 2]);
    }

    private function open(Student $student, $procedure, Semester $semester): void
    {
        $due = Carbon::parse($semester->sem_startdate)->addWeeks((int) $procedure->timeline_week);
        $documents = Document::where('activity_id', $procedure->activity_id)->get();
        if ($documents->isEmpty()) {
            return;
        }
        foreach ($documents as $document) {
            $submission = $this->submissions($student, $procedure, $semester)->where('document_id', $document->id)->first();
            if ($submission && (int) $submission->submission_status !== 2) {
                continue;
            }
            $values = ['submission_duedate' => $due, 'semester_id' => $semester->id,
                'submission_status' => $submission && $submission->submission_document !== '-' ? 3 : ($due->isPast() ? 4 : 1)];
            if ($submission) {
                $submission->update($values);
            } else {
                Submission::create($values + ['student_id' => $student->id, 'document_id' => $document->id, 'submission_document' => '-']);
            }
        }
        if (! $procedure->is_haveEva) {
            return;
        }
        $previous = $procedure->is_repeatable ? DB::table('evaluators as e')->join('nominations as n', 'n.id', '=', 'e.nom_id')
            ->where('n.student_id', $student->id)->where('n.activity_id', $procedure->activity_id)
            ->where('n.semester_id', '<', $semester->id)->where('e.eva_status', 3)
            ->select('e.staff_id', 'e.eva_role', 'n.semester_id')->orderByDesc('n.semester_id')->distinct()->get() : collect();
        if ($previous->isNotEmpty()) {
            $previous = $previous->where('semester_id', $previous->first()->semester_id);
            foreach ($previous as $evaluator) {
                if ((int) $procedure->evaluation_mode === 1 || (int) $evaluator->eva_role === 1) {
                    Evaluation::firstOrCreate(['student_id' => $student->id, 'activity_id' => $procedure->activity_id,
                        'semester_id' => $semester->id, 'staff_id' => $evaluator->staff_id], ['evaluation_status' => 1]);
                }
            }
        } else {
            $key = ['student_id' => $student->id, 'activity_id' => $procedure->activity_id];
            if ($procedure->is_repeatable) {
                $key['semester_id'] = $semester->id;
            }
            Nomination::firstOrCreate($key, ['nom_status' => 1, 'semester_id' => $semester->id]);
        }
    }

    public function manual(int $studentId, int $activityId, int $option): void
    {
        $this->snapshot = null;
        DB::transaction(function () use ($studentId, $activityId, $option) {
            $student = Student::whereKey($studentId)->lockForUpdate()->firstOrFail();
            $semester = Semester::where('sem_status', 1)->firstOrFail();
            $procedure = Procedure::where('programme_id', $student->programme_id)->where('activity_id', $activityId)->firstOrFail();
            $status = $this->status($student, $procedure, $semester);
            if ($option === 1 && $status === 1) {
                $this->open($student, $procedure, $semester);
            } elseif ($option === 2 && $status === 2) {
                $this->lock($student, $procedure, $semester);
            } else {
                throw ValidationException::withMessages(['eligibility' => 'Eligibility has changed or this action is not allowed. Refresh the overview.']);
            }
        });
    }
}
