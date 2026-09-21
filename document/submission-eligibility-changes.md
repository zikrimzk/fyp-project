# Submission eligibility changes

## Workflow

- Enrollment processing opens activities only for an active student with active enrollment in the current system semester, sufficient enrolled-semester count, and completed prerequisite activities.
- Final submission approval does not trigger automatic opening of another activity.
- Individual enrollment, imports, individual/bulk enrollment status changes, and removal use the shared eligibility service.
- Inactive or removed enrollment locks unconfirmed submissions. Confirmed activity history is retained, and student upload/confirmation routes require active current enrollment.
- Reactivation restores eligible submissions, preserving uploaded files. Completed and archived activities are not automatically reopened.
- Repeatable activities use current-semester records. Older unconfirmed repeatable drafts are archived without deleting their uploaded files. Confirmed activity history is preserved.
- Nomination/evaluation creation avoids duplicates; non-repeatable nominations survive re-enrollment. Existing nomination/review records are retained when a submission is manually locked.
- Deadlines retain the configured calculation: current semester start plus the activity's timeline in weeks.

## Committee overview

Submission Final Overview now links to **View Student Eligibility >** after Export Data. The renamed page defaults to all activities and statuses and has no sidebar entry or explanatory note block.

Each row represents a student/activity pair. Approve and Revert remain available, including bulk actions across activities and table pages. The backend rechecks every action; an invalid item rolls back the bulk operation. Confirmation dialogs are shared rather than generated for every row.

Additional statuses show unmet semester requirements and lack of active enrollment. The list is restricted to students enrolled in the selected semester, defaulting to the current semester. Historical semester views use enrollment counts and recorded activity history up to that semester, and are view-only. Records are not immutable historical snapshots: later edits to an existing record cannot reconstruct its earlier state.

## Implementation

- Shared rules: `app/Services/SubmissionEligibility.php`.
- Student access checks: `app/Http/Middleware/EnsureSubmissionAccess.php`.
- Enrollment changes and submission assignment use transactions.
- No database migration is required. Existing records are not automatically bulk-processed merely by deploying these files.
- The old submission-suggestion URL redirects to the renamed page.

## Verification

Run the tests with the SQLite PDO extension enabled:

```powershell
php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit
```

The eligibility tests explicitly use an isolated in-memory SQLite database and the project's migrations. They cover semester/prerequisite gates, enrollment/import/bulk actions, preservation of uploads and completed history, repeated evaluation creation, manual action rollback, overview rendering, backend access checks, and transaction rollback.

Before the university trial, perform a browser walkthrough with representative trial accounts on the deployed MySQL environment, including form confirmation and nomination/evaluation review. Automated tests do not replace that environment-specific check.
