<?php

namespace App\Http\Middleware;

use App\Models\ActivityForm;
use App\Models\Procedure;
use App\Models\Semester;
use App\Models\Submission;
use App\Services\SubmissionEligibility;
use Closure;
use Illuminate\Http\Request;

class EnsureSubmissionAccess
{
    public function handle(Request $request, Closure $next)
    {
        $student = $request->user('student');
        $semester = Semester::where('sem_status', 1)->first();
        $eligibility = app(SubmissionEligibility::class);
        abort_unless($student && $semester && $eligibility->enrolled($student, $semester), 403,
            'An active enrollment in the current semester is required.');

        if ($request->route('actID')) {
            $activityId = decrypt($request->route('actID'));
            $procedure = Procedure::where('programme_id', $student->programme_id)->where('activity_id', $activityId)->firstOrFail();
            $submissions = $eligibility->submissions($student, $procedure, $semester);
            abort_unless((clone $submissions)->whereIn('submission_status', [1, 3, 4])->exists()
                && ! (clone $submissions)->whereIn('submission_status', [2, 5])->exists(), 403, 'This submission is locked.');
            $target = $request->routeIs('student-confirm-correction-post') ? 2 : 1;
            abort_unless(ActivityForm::where('activity_id', $activityId)->where('af_target', $target)->where('af_status', 1)->exists(),
                422, 'An active activity form is required before confirmation.');
        } else {
            $id = $request->route('id') ? decrypt($request->route('id')) : $request->input('submission_id');
            $submission = Submission::with('document')->where('student_id', $student->id)->findOrFail($id);
            abort_unless(in_array((int) $submission->submission_status, [1, 3, 4], true), 403, 'This submission is locked.');
            $procedure = Procedure::where('programme_id', $student->programme_id)
                ->where('activity_id', $submission->document->activity_id)->firstOrFail();
            abort_if($procedure->is_repeatable && (int) $submission->semester_id !== (int) $semester->id, 403);
            if ($request->routeIs('student-submit-document-post')) {
                abort_unless((int) $request->input('document_id') === (int) $submission->document_id
                    && (int) $request->input('activity_id') === (int) $submission->document->activity_id, 422);
            }
        }

        return $next($request);
    }
}
