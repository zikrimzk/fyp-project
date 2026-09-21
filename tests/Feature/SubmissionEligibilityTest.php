<?php

namespace Tests\Feature;

use App\Models\Nomination;
use App\Models\Procedure;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\Submission;
use App\Services\SubmissionEligibility;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubmissionEligibilityTest extends TestCase
{
    private Student $student;

    private Semester $semester;

    private SubmissionEligibility $eligibility;

    protected function setUp(): void
    {
        parent::setUp();
        // Never migrate or write to the application's configured MySQL database.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        DB::table('faculties')->insert(['id' => 1, 'fac_name' => 'Test', 'fac_code' => 'TEST']);
        DB::table('programmes')->insert(['id' => 1, 'prog_name' => 'PITA', 'prog_code' => 'PITA', 'prog_mode' => 'FT', 'fac_id' => 1]);
        DB::table('students')->insert(['id' => 1, 'student_name' => 'Zikri', 'student_matricno' => 'Z001',
            'student_email' => 'zikri@example.test', 'student_password' => 'test', 'student_gender' => 'M', 'programme_id' => 1, 'student_semcount' => 1]);
        $this->student = Student::findOrFail(1);
        $this->semester = Semester::create(['sem_label' => '2022/2023', 'sem_startdate' => now()->toDateString(),
            'sem_enddate' => now()->addMonths(6)->toDateString(), 'sem_status' => 1]);
        DB::table('student_semesters')->insert(['student_id' => 1, 'semester_id' => $this->semester->id, 'ss_status' => 1]);
        foreach ([1 => 1, 2 => 4] as $id => $minimum) {
            DB::table('activities')->insert(['id' => $id, 'act_name' => $id === 1 ? 'First Stage' : 'Proposal Defence']);
            DB::table('documents')->insert(['id' => $id, 'doc_name' => 'Report', 'activity_id' => $id]);
            DB::table('procedures')->insert(['activity_id' => $id, 'programme_id' => 1, 'activity_type' => 1,
                'act_seq' => $id, 'timeline_sem' => $minimum, 'timeline_week' => 4, 'init_status' => 2, 'is_haveEva' => 1]);
        }
        $this->eligibility = new SubmissionEligibility;
    }

    private function sync(): void
    {
        $this->eligibility->sync('Z001');
    }

    private function completeFirstStage(): void
    {
        StudentActivity::create(['student_id' => 1, 'activity_id' => 1, 'semester_id' => $this->semester->id,
            'sa_status' => 3, 'sa_final_submission' => 'signed.pdf', 'sa_signature_data' => '{}']);
    }

    public function test_semester_count_and_completed_prerequisites_are_both_required(): void
    {
        $this->sync();
        $this->assertDatabaseHas('submissions', ['document_id' => 1, 'submission_status' => 1]);
        $this->assertDatabaseHas('submissions', ['document_id' => 2, 'submission_status' => 2]);
        $this->student->update(['student_semcount' => 4]);
        $this->sync();
        $this->assertDatabaseHas('submissions', ['document_id' => 2, 'submission_status' => 2]);
        $this->completeFirstStage();
        // Completion itself does not invoke automatic opening.
        $this->assertDatabaseHas('submissions', ['document_id' => 2, 'submission_status' => 2]);
        $this->student->update(['student_semcount' => 3]);
        $this->sync();
        $this->assertDatabaseHas('submissions', ['document_id' => 2, 'submission_status' => 2]);
        $this->student->update(['student_semcount' => 4]);
        $this->sync();
        $this->assertDatabaseHas('submissions', ['document_id' => 2, 'submission_status' => 1]);
    }

    public function test_inactive_removed_and_reactivated_enrollment_preserves_uploads(): void
    {
        $this->sync();
        Submission::where('document_id', 1)->update(['submission_status' => 3, 'submission_document' => 'draft.pdf']);
        DB::table('student_semesters')->update(['ss_status' => 2]);
        $this->sync();
        $this->assertDatabaseHas('submissions', ['document_id' => 1, 'submission_status' => 2, 'submission_document' => 'draft.pdf']);
        DB::table('student_semesters')->delete();
        $this->sync();
        $this->assertDatabaseHas('submissions', ['document_id' => 1, 'submission_status' => 2]);
        DB::table('student_semesters')->insert(['student_id' => 1, 'semester_id' => $this->semester->id, 'ss_status' => 1]);
        $this->sync();
        $this->assertDatabaseHas('submissions', ['document_id' => 1, 'submission_status' => 3, 'submission_document' => 'draft.pdf']);
        $this->assertSame(1, Nomination::count());
    }

    public function test_only_current_active_enrollment_opens_submissions(): void
    {
        DB::table('student_semesters')->delete();
        $other = Semester::create(['sem_label' => 'Other', 'sem_startdate' => '2020-01-01', 'sem_enddate' => '2020-06-01', 'sem_status' => 3]);
        DB::table('student_semesters')->insert(['student_id' => 1, 'semester_id' => $other->id, 'ss_status' => 1]);
        $this->sync();
        $this->assertSame(0, Submission::count());
    }

    public function test_repeated_sync_preserves_completed_and_archived_work(): void
    {
        $this->sync();
        $this->completeFirstStage();
        Submission::where('document_id', 2)->update(['submission_status' => 5, 'submission_document' => 'archive.pdf']);
        $this->student->update(['student_semcount' => 4]);
        $this->sync();
        $this->sync();
        $this->assertSame(2, Submission::count());
        $this->assertSame(1, Nomination::count());
        $this->assertSame(1, StudentActivity::count());
        $this->assertDatabaseHas('submissions', ['document_id' => 2, 'submission_status' => 5, 'submission_document' => 'archive.pdf']);
    }

    public function test_repeatable_activity_is_scoped_to_current_semester(): void
    {
        DB::table('procedures')->where('activity_id', 1)->update(['is_repeatable' => 1, 'init_status' => 1]);
        $this->sync();
        $this->completeFirstStage();
        $this->sync();
        $this->assertSame(1, StudentActivity::count());
        $this->semester->update(['sem_status' => 3]);
        $next = Semester::create(['sem_label' => '2023/2024', 'sem_startdate' => now()->toDateString(), 'sem_enddate' => now()->addMonths(6)->toDateString(), 'sem_status' => 1]);
        DB::table('student_semesters')->insert(['student_id' => 1, 'semester_id' => $next->id, 'ss_status' => 1]);
        $this->sync();
        $this->sync();
        $this->assertSame(2, Submission::where('document_id', 1)->count());
        $this->assertSame(2, Nomination::where('activity_id', 1)->count());
        $this->assertSame(1, StudentActivity::count());
    }

    public function test_manual_actions_preserve_documents_and_reject_unmet_requirements(): void
    {
        $this->sync();
        $this->eligibility->manual(1, 1, 2);
        $this->assertDatabaseHas('submissions', ['document_id' => 1, 'submission_status' => 2]);
        $this->eligibility->manual(1, 1, 1);
        $this->assertSame(1, Nomination::count());
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->eligibility->manual(1, 2, 1);
    }

    public function test_overview_snapshot_matches_live_decisions_without_row_queries(): void
    {
        $this->sync();
        $procedures = Procedure::all();
        $expected = $procedures->map(fn ($p) => $this->eligibility->status($this->student, $p, $this->semester));
        $this->eligibility->preloadOverview();
        DB::enableQueryLog();
        $actual = $procedures->map(fn ($p) => $this->eligibility->status($this->student, $p, $this->semester));
        $this->assertEquals($expected, $actual);
        $this->assertCount(0, DB::getQueryLog());
    }

    public function test_inactive_student_cannot_upload_through_direct_route(): void
    {
        $this->sync();
        DB::table('student_semesters')->update(['ss_status' => 2]);
        $this->actingAs($this->student, 'student')->postJson(route('student-submit-document-post'), [
            'submission_id' => Submission::first()->id, 'document_id' => 1, 'activity_id' => 1,
        ])->assertForbidden();
    }

    private function committee(): void
    {
        $staff = new \App\Models\Staff;
        $staff->id = 1;
        $staff->staff_role = 1;
        $this->actingAs($staff, 'staff');
    }

    public function test_overview_defaults_to_all_activities_and_statuses_and_unique_pair_keys(): void
    {
        $this->sync();
        $this->committee();
        $response = $this->getJson(route('submission-eligibility'), ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertOk()->assertJsonPath('recordsTotal', 2);
        $rows = $response->json('data');
        $this->assertStringContainsString('1:1', $rows[0]['checkbox']);
        $this->assertStringContainsString('Submission Opened', $rows[0]['suggestion_status']);
        $this->assertStringContainsString('Semester Requirement Pending', $rows[1]['suggestion_status']);
        $this->assertStringContainsString('revertEligibilityModal', $rows[0]['action']);
    }

    public function test_bulk_actions_target_student_activity_pairs_across_activities(): void
    {
        DB::table('procedures')->where('activity_id', 2)->update(['act_seq' => 1, 'timeline_sem' => 1]);
        $this->sync();
        $this->committee();
        $this->postJson(route('multiple-submission-eligibility-approval-post'), [
            'selectedIds' => ['1:1', '1:2'], 'option' => 2,
        ])->assertOk();
        $this->assertSame(2, Submission::where('submission_status', 2)->count());
        $this->postJson(route('multiple-submission-eligibility-approval-post'), [
            'selectedIds' => ['1:1', '1:2'], 'option' => 1,
        ])->assertOk();
        $this->assertSame(2, Submission::where('submission_status', 1)->count());
        $this->assertSame(2, Nomination::count());
    }

    public function test_invalid_bulk_action_rolls_back_other_pairs(): void
    {
        $this->sync();
        $this->committee();
        $this->postJson(route('multiple-submission-eligibility-approval-post'), [
            'selectedIds' => ['1:1', '1:2'], 'option' => 2,
        ])->assertUnprocessable();
        $this->assertDatabaseHas('submissions', ['document_id' => 1, 'submission_status' => 1]);
    }

    public function test_individual_enrollment_endpoint_opens_then_status_change_locks(): void
    {
        DB::table('student_semesters')->delete();
        $this->committee();
        $this->post(route('assign-student-post', encrypt($this->semester->id)), ['student_matricno' => 'Z001'])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('submissions', ['document_id' => 1, 'submission_status' => 1]);
        $this->post(route('update-student-semester-post', [encrypt(1), encrypt($this->semester->id)]), [
            'student_semester_status_change' => 2,
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('submissions', ['document_id' => 1, 'submission_status' => 2]);
    }

    public function test_bulk_enrollment_status_and_removal_lock_submissions(): void
    {
        $this->sync();
        $this->committee();
        $this->postJson(route('update-multiple-student-semester-post'), [
            'student_ids' => [1], 'semester_id' => $this->semester->id, 'status' => 2,
        ])->assertOk();
        $this->assertDatabaseHas('submissions', ['document_id' => 1, 'submission_status' => 2]);
        $this->postJson(route('update-multiple-student-semester-post'), [
            'student_ids' => [1], 'semester_id' => $this->semester->id, 'status' => 1,
        ])->assertOk();
        $this->assertDatabaseHas('submissions', ['document_id' => 1, 'submission_status' => 1]);
        $this->postJson(route('delete-multiple-student-semester-post'), [
            'student_ids' => [1], 'semester_id' => $this->semester->id,
        ])->assertOk();
        $this->assertDatabaseHas('submissions', ['document_id' => 1, 'submission_status' => 2]);
    }

    public function test_import_opens_eligible_submissions_and_skips_duplicate_enrollment(): void
    {
        DB::table('student_semesters')->delete();
        $import = new \App\Imports\StudentSemesterImport;
        $import->collection(collect([collect(['student_matricno' => 'Z001'])]));
        $this->assertSame(1, $import->insertedCount);
        $this->assertDatabaseHas('submissions', ['document_id' => 1, 'submission_status' => 1]);
        $import->collection(collect([collect(['student_matricno' => 'Z001'])]));
        $this->assertSame(1, $import->skippedCount);
        $this->assertSame(1, Nomination::count());
    }

    public function test_eligibility_page_renders_all_filters_and_shared_dialogs(): void
    {
        $this->committee();
        $this->get(route('submission-eligibility'))->assertOk()
            ->assertSee('Submission Eligibility')->assertSee('All Activities')->assertSee('All Statuses')
            ->assertSee('approveEligibilityModal')->assertDontSee('How Eligibility is Determined');
    }

    public function test_nonrepeatable_nomination_is_not_duplicated_after_semester_change(): void
    {
        $this->sync();
        DB::table('student_semesters')->update(['ss_status' => 2]);
        $this->sync();
        $this->semester->update(['sem_status' => 3]);
        $next = Semester::create(['sem_label' => '2023/2024', 'sem_startdate' => now()->toDateString(), 'sem_enddate' => now()->addMonths(6)->toDateString(), 'sem_status' => 1]);
        DB::table('student_semesters')->insert(['student_id' => 1, 'semester_id' => $next->id, 'ss_status' => 1]);
        $this->sync();
        $this->assertSame(1, Nomination::where('activity_id', 1)->count());
    }

    public function test_repeatable_evaluations_reuse_confirmed_evaluators_without_duplicates(): void
    {
        DB::table('departments')->insert(['id' => 1, 'dep_name' => 'Test', 'dep_code' => 'TEST', 'fac_id' => 1]);
        DB::table('staff')->insert(['id' => 1, 'staff_id' => 'S001', 'staff_name' => 'Examiner',
            'staff_email' => 'examiner@example.test', 'staff_password' => 'test', 'department_id' => 1]);
        DB::table('procedures')->where('activity_id', 1)->update(['is_repeatable' => 1, 'init_status' => 1, 'evaluation_mode' => 1]);
        $this->sync();
        DB::table('evaluators')->insert(['staff_id' => 1, 'nom_id' => Nomination::first()->id, 'eva_status' => 3, 'eva_role' => 1]);
        $this->semester->update(['sem_status' => 3]);
        $next = Semester::create(['sem_label' => '2023/2024', 'sem_startdate' => now()->toDateString(), 'sem_enddate' => now()->addMonths(6)->toDateString(), 'sem_status' => 1]);
        DB::table('student_semesters')->insert(['student_id' => 1, 'semester_id' => $next->id, 'ss_status' => 1]);
        $this->sync();
        $this->sync();
        $this->assertSame(1, \App\Models\Evaluation::count());
        $this->assertDatabaseHas('evaluations', ['student_id' => 1, 'activity_id' => 1, 'semester_id' => $next->id, 'staff_id' => 1]);
        $this->assertDatabaseHas('submissions', ['document_id' => 1, 'semester_id' => $this->semester->id, 'submission_status' => 5]);
    }

    public function test_locked_submission_and_missing_form_cannot_be_bypassed(): void
    {
        $this->sync();
        $this->actingAs($this->student, 'student');
        $this->postJson(route('student-submit-document-post'), [
            'submission_id' => Submission::where('document_id', 2)->first()->id, 'document_id' => 2, 'activity_id' => 2,
        ])->assertForbidden();
        $this->postJson(route('student-confirm-submission-post', encrypt(1)))->assertUnprocessable();
        $this->assertSame(0, StudentActivity::count());
    }

    public function test_failed_assignment_rolls_back_enrollment_and_semester_count(): void
    {
        DB::table('student_semesters')->delete();
        $this->student->update(['student_semcount' => 0]);
        $this->semester->update(['sem_status' => 3]);
        $this->committee();
        $this->post(route('assign-student-post', encrypt($this->semester->id)), ['student_matricno' => 'Z001'])
            ->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseCount('student_semesters', 0);
        $this->assertSame(0, $this->student->fresh()->student_semcount);
    }
}
