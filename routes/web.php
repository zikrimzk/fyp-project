<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SOPController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\NominationController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\SupervisionController;
use App\Http\Controllers\AuthenticateController;

Route::get('/', [AuthenticateController::class, 'mainLogin'])
    ->middleware('redirectIfAuthenticatedMulti')
    ->name('main-login');

Route::prefix('auth')->group(function () {

    /* User Login */
    Route::post('/authenticate-user', [AuthenticateController::class, 'authenticateUser'])
        ->name('user-authenticate');

    /* User Logout */
    Route::get('/logout-user', [AuthenticateController::class, 'logoutUser'])->name('user-logout');

    /* User Forgot Password */
    Route::get('/forgot-password', [AuthenticateController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('/request/reset-password', [AuthenticateController::class, 'requestResetPassword'])->name('request-reset-password');
    Route::get('/form-reset-password-{token}-{email}-{userType}', [AuthenticateController::class, 'resetPasswordForm'])->name('reset-password-form');
    Route::post('/reset-password-{token}-{email}-{userType}', [AuthenticateController::class, 'resetPassword'])->name('reset-password');
});


Route::prefix('student')->middleware('auth:student')->group(function () {

    //Authentication - Account Management

    /* Student Home */
    Route::get('/home', [AuthenticateController::class, 'studentHome'])->name('student-home');
    Route::post('/student-update-title-of-research-{id}', [SupervisionController::class, 'updateTitleOfResearch'])->name('student-update-titleOfResearch-post');


    /* Student Profile */
    Route::get('/my-profile', [AuthenticateController::class, 'studentProfile'])->name('student-profile');
    Route::post('/update-profile', [AuthenticateController::class, 'updateStudentProfile'])->name('update-student-profile');
    Route::post('/update-password', [AuthenticateController::class, 'updateStudentPassword'])->name('update-student-password');

    /* Programme & Activity Management */
    Route::get('/programme-overview', [SubmissionController::class, 'studentProgrammeOverview'])->name('student-programme-overview');
    Route::get('/view-document/{filename}', [SOPController::class, 'viewMaterialFile'])->where('filename', '.*')->name('student-view-material-get');

    /* Submission Management */
    Route::get('/document-submission-{id}', [SubmissionController::class, 'documentSubmission'])->name('student-document-submission');
    Route::post('/submit-document', [SubmissionController::class, 'submitDocument'])->name('student-submit-document-post');
    Route::get('/remove-document-{id}-{filename}', [SubmissionController::class, 'removeDocument'])->name('student-remove-document-get');
    Route::post('/confirm-student-submission-{actID}', [SubmissionController::class, 'confirmStudentSubmission'])->name('student-confirm-submission-post');
    Route::get('/view-final-document/{actID}/{semesterID}/{filename}/{opt}', [SubmissionController::class, 'viewFinalDocument'])->where('filename', '.*')->name('student-view-final-document-get');

    /* Correction Confirmation */
    Route::post('/confirm-correction-submission-{actID}', [SubmissionController::class, 'confirmStudentCorrection'])->name('student-confirm-correction-post');

    /* Journal Publication */
    Route::get('/journal-publication', [SubmissionController::class, 'journalPublicationManagement'])->name('student-journal-publication');
    Route::get('/get-journal-publication', [SubmissionController::class, 'getJournalPublication'])->name('get-journal-publication');
    Route::post('/add-journal-publication', [SubmissionController::class, 'addJournalPublication'])->name('student-add-journal-publication-post');
    Route::post('/update-journal-publication', [SubmissionController::class, 'updateJournalPublication'])->name('student-update-journal-publication-post');
    Route::post('/delete-journal-publication', [SubmissionController::class, 'deleteJournalPublication'])->name('student-delete-journal-publication-post');
});



Route::prefix('staff')->middleware('auth:staff')->group(function () {

    // ---------------------------------------------------------------------------------------------------------------------//
    // -----------------------------------------------------ALL STAFF ------------------------------------------------------//
    // ---------------------------------------------------------------------------------------------------------------------//

    //Authentication - Account Management

    /* Staff Dashboard */
    Route::get('/dashboard', [AuthenticateController::class, 'staffDashboard'])->name('staff-dashboard');

    /* Staff Profile */
    Route::get('/my-profile', [AuthenticateController::class, 'staffProfile'])->name('staff-profile');
    Route::post('/update-profile', [AuthenticateController::class, 'updateStaffProfile'])->name('update-staff-profile');
    Route::post('/update-password', [AuthenticateController::class, 'updateStaffPassword'])->name('update-staff-password');


    // ---------------------------------------------------------------------------------------------------------------------//
    // ---------------------------------------------COMMITTEE / DD / DEAN---------------------------------------------------//
    // ---------------------------------------------------------------------------------------------------------------------//

    // Supervision

    /* Student Management */
    Route::get('/student-management', [SupervisionController::class, 'studentManagement'])->name('student-management');
    Route::post('/add-student', [SupervisionController::class, 'addStudent'])->name('add-student-post');
    Route::post('/update-student-{id}', [SupervisionController::class, 'updateStudent'])->name('update-student-post');
    Route::get('/delete-student-{id}-{opt}', [SupervisionController::class, 'deleteStudent'])->name('delete-student-get');
    Route::post('/import-student-data', [SupervisionController::class, 'importStudent'])->name('import-student-post');
    Route::get('/export-student-data', [SupervisionController::class, 'exportStudent'])->name('export-student-get');
    Route::post('/update-multiple-student-status', [SupervisionController::class, 'updateStudentStatus'])->name('update-student-status-post');

    /* Staff Management */
    Route::get('/staff-management', [SupervisionController::class, 'staffManagement'])->name('staff-management');
    Route::post('/add-staff', [SupervisionController::class, 'addStaff'])->name('add-staff-post');
    Route::post('/update-staff-{id}', [SupervisionController::class, 'updateStaff'])->name('update-staff-post');
    Route::get('/delete-staff-{id}-{opt}', [SupervisionController::class, 'deleteStaff'])->name('delete-staff-get');
    Route::post('/import-staff-data', [SupervisionController::class, 'importStaff'])->name('import-staff-post');
    Route::get('/export-staff-data', [SupervisionController::class, 'exportStaff'])->name('export-staff-get');

    /* Supervision Arrangement */
    Route::get('/supervision-arrangement', [SupervisionController::class, 'supervisionArrangement'])->name('supervision-arrangement');
    Route::post('/update-title-of-research-{id}', [SupervisionController::class, 'updateTitleOfResearch'])->name('update-titleOfResearch-post');
    Route::post('/add-supervision-{id}', [SupervisionController::class, 'addSupervision'])->name('add-supervision-post');
    Route::post('/update-supervision-{id}', [SupervisionController::class, 'updateSupervision'])->name('update-supervision-post');
    Route::get('/delete-supervision-{id}', [SupervisionController::class, 'deleteSupervision'])->name('delete-supervision-get');
    Route::get('/export-supervision-data', [SupervisionController::class, 'exportSupervision'])->name('export-supervision-get');

    /* Student Enrollment */
    Route::get('/semester-enrollment', [SupervisionController::class, 'semesterEnrollment'])->name('semester-enrollment');
    Route::post('/get-student-data', [SupervisionController::class, 'getStudentData'])->name('get-student-data-post');
    Route::post('/enroll-new-semester-{semID}', [SupervisionController::class, 'assignStudentSemester'])->name('assign-student-post');
    Route::get('/student-semester-enrollment-list-{semID}', [SupervisionController::class, 'semesterStudentList'])->name('semester-student-list');
    Route::post('/update-status-student-semester-{studentID}-{semID}', [SupervisionController::class, 'updateStudentSemester'])->name('update-student-semester-post');
    Route::get('/delete-registered-student-{studentID}-{semID}', [SupervisionController::class, 'deleteStudentSemester'])->name('delete-student-semester-get');
    Route::post('/update-status-multiple-student-semester', [SupervisionController::class, 'updateMultipleStudentSemester'])->name('update-multiple-student-semester-post');
    Route::post('/delete-registered-multiple-student-semester', [SupervisionController::class, 'deleteMultipleStudentSemester'])->name('delete-multiple-student-semester-post');
    Route::post('/import-student-semester-data', [SupervisionController::class, 'importStudentNewSemester'])->name('import-student-semester-post');
    Route::get('/export-student-semester-enrollment-data', [SupervisionController::class, 'exportStudentSemester'])->name('export-student-semester-enrollment-get');


    // Submission

    /* Submission Management */
    Route::get('/submission-management', [SubmissionController::class, 'submissionManagement'])->name('submission-management');
    Route::get('/assign-student-submission', [SubmissionController::class, 'assignSubmission'])->name('assign-student-submission');
    Route::post('/update-submission-{id}', [SubmissionController::class, 'updateSubmission'])->name('update-submission-post');
    Route::get('/archive-submission-{id}-{opt}', [SubmissionController::class, 'archiveSubmission'])->name('archive-submission-get');
    Route::post('/update-multiple-submission', [SubmissionController::class, 'updateMultipleSubmission'])->name('update-multiple-submission-post');
    Route::post('/archive-multiple-submission', [SubmissionController::class, 'archiveMultipleSubmission'])->name('archive-multiple-submission-post');
    Route::get('/download-multiple-submission', [SubmissionController::class, 'downloadMultipleSubmission'])->name('download-multiple-submission-get');

    /* Submission Approval */
    Route::get('/submission-approval', [SubmissionController::class, 'submissionApproval'])->name('submission-approval');
    Route::post('/student-submission-approval/{stuActID}-{option}', [SubmissionController::class, 'studentActivitySubmissionApproval'])->name('staff-submission-approval-post');
    Route::get('/download-multiple-final-document', [SubmissionController::class, 'downloadMultipleFinalDocument'])->name('download-multiple-final-document-get');
    Route::post('/get-submission-review', [SubmissionController::class, 'getReview'])->name('get-review-data-post');
    Route::post('/update-review-activity', [SubmissionController::class, 'updateReview'])->name('update-review-post');
    Route::post('/delete-review-activity', [SubmissionController::class, 'deleteReview'])->name('delete-review-post');

    /* Correction Approval */
    Route::get('/correction-approval', [SubmissionController::class, 'correctionApproval'])->name('correction-approval');
    Route::post('/student-correction-approval/{actCorrID}-{option}', [SubmissionController::class, 'studentActivityCorrectionApproval'])->name('staff-correction-approval-post');

    /* Submission Suggestion */
    Route::get('/submission-suggestion', [SubmissionController::class, 'submissionSuggestion'])->name('submission-suggestion');
    Route::get('/submission-eligibility-approval/{studentID}/{activityID}/{opt}', [SubmissionController::class, 'studentSubmissionSuggestionApproval'])->name('submission-eligibility-approval-get');
    Route::post('/multiple-submission-eligibility-approval', [SubmissionController::class, 'multipleStudentSubmissionSuggestionApproval'])->name('multiple-submission-eligibility-approval-post');

    // Nomination
    Route::get('/nomination-{studentId}-{actId}-{semesterId}-{mode}', [NominationController::class, 'nominationStudent'])->name('nomination-student');
    Route::get('/view-nomination-form', [NominationController::class, 'viewNominationForm'])->name('view-nomination-form-get');
    Route::post('/submit-nomination-{studentId}-{mode}', [NominationController::class, 'submitNomination'])->name('submit-nomination-post');
    Route::get('/create-renomination-data/{nominationId}', [NominationController::class, 'reNominatedStudent'])->name('renomination-data-get');

    // Evaluation
    Route::get('/evaluation-student-{evaluationID}-{mode}', [EvaluationController::class, 'evaluationStudent'])->name('evaluation-student');
    Route::get('/view-evaluation-form', [EvaluationController::class, 'viewEvaluationForm'])->name('view-evaluation-form-get');
    Route::post('/submit-evaluation-{evaluationID}-{mode}', [EvaluationController::class, 'submitEvaluation'])->name('submit-evaluation-post');


    // Standard Operation Procedure (SOP)

    /* Activity + Document Setting */
    Route::get('/activity-setting', [SOPController::class, 'activitySetting'])->name('activity-setting');
    Route::get('/view-activity', [SOPController::class, 'viewActivity'])->name('view-activity-get');
    Route::post('/add-activity', [SOPController::class, 'addActivity'])->name('add-activity-post');
    Route::post('/update-activity', [SOPController::class, 'updateActivity'])->name('update-activity-post');
    Route::get('/delete-activity-{id}', [SOPController::class, 'deleteActivity'])->name('delete-activity-get');
    Route::get('/view-document-by-activity-{id}', [SOPController::class, 'viewDocumentByActivity'])->name('view-document-by-activity-get');
    Route::post('/add-document', [SOPController::class, 'addDocument'])->name('add-document-post');
    Route::post('/update-document', [SOPController::class, 'updateDocument'])->name('update-document-post');
    Route::get('/delete-document-{id}', [SOPController::class, 'deleteDocument'])->name('delete-document-get');

    /* Procedure Setting */
    Route::get('/procedure-setting', [SOPController::class, 'procedureSetting'])->name('procedure-setting');
    Route::post('/add-procedure', [SOPController::class, 'addProcedure'])->name('add-procedure-post');
    Route::post('/update-procedure-{actID}-{progID}', [SOPController::class, 'updateProcedure'])->name('update-procedure-post');
    Route::get('/delete-procedure-{actID}-{progID}', [SOPController::class, 'deleteProcedure'])->name('delete-procedure-get');
    Route::get('/view-material/{filename}', [SOPController::class, 'viewMaterialFile'])->where('filename', '.*')->name('view-material-get');

    /* Form Setting */
    Route::get('/form-setting', [SOPController::class, 'formSetting'])->name('form-setting');
    Route::post('/add-activity-form', [SOPController::class, 'addActivityForm'])->name('add-activity-form-post');
    Route::get('/delete-form-activity-{afID}', [SOPController::class, 'deleteActivityForm'])->name('delete-form-activity-get');

    /* Form Editor */
    Route::post('/form-get-started', [SOPController::class, 'formGetStarted'])->name('form-get-started-post');
    Route::get('/form-editor-{formID}-{afTarget}', [SOPController::class, 'formEditor'])->name('form-editor');
    Route::post('/get-activity-form-data', [SOPController::class, 'getActivityFormData'])->name('get-activity-form-data-post');
    Route::get('/activity-document-preview', [SOPController::class, 'previewActivityDocument'])->name('activity-document-preview-get');
    Route::get('/preview-activity-document', [SOPController::class, 'previewActivityDocumentbyHTML'])->name('preview-activity-document-get');
    Route::post('/add-form-field', [SOPController::class, 'addFormField'])->name('add-form-field-post');
    Route::post('/update-form-field', [SOPController::class, 'updateFormField'])->name('update-form-field-post');
    Route::post('/update-order-form-field', [SOPController::class, 'updateFormFieldOrder'])->name('update-order-form-field-post');
    Route::post('/delete-form-field', [SOPController::class, 'deleteFormField'])->name('delete-form-field-post');
    Route::get('/get-form-field-data', [SOPController::class, 'getFormFieldData'])->name('get-form-field-data-get');
    Route::get('/get-single-form-field-data', [SOPController::class, 'getSingleFormFieldData'])->name('get-single-form-field-data-get');
    Route::get('/get-table-columns', [SOPController::class, 'getTableColumnData'])->name('get-table-columns-get');


    // Setting 

    /* Faculty Setting */
    Route::get('/faculty-setting', [SettingController::class, 'facultySetting'])->name('faculty-setting');
    Route::post('/add-faculty', [SettingController::class, 'addFaculty'])->name('add-faculty-post');
    Route::post('/update-faculty/{id}', [SettingController::class, 'updateFaculty'])->name('update-faculty-post');
    Route::get('/delete-faculty-{id}-{opt}', [SettingController::class, 'deleteFaculty'])->name('delete-faculty-get');
    Route::post('/set-default-faculty', [SettingController::class, 'setDefaultFaculty'])->name('set-default-faculty-post');

    /* Department Setting */
    Route::get('/department-setting', [SettingController::class, 'departmentSetting'])->name('department-setting');
    Route::post('/add-department', [SettingController::class, 'addDepartment'])->name('add-department-post');
    Route::post('/update-department/{id}', [SettingController::class, 'updateDepartment'])->name('update-department-post');
    Route::get('/delete-department-{id}-{opt}', [SettingController::class, 'deleteDepartment'])->name('delete-department-get');

    /* Programme Setting */
    Route::get('/programme-setting', [SettingController::class, 'programmeSetting'])->name('programme-setting');
    Route::post('/add-programme', [SettingController::class, 'addProgramme'])->name('add-programme-post');
    Route::post('/update-programme/{id}', [SettingController::class, 'updateProgramme'])->name('update-programme-post');
    Route::get('/delete-programme-{id}-{opt}', [SettingController::class, 'deleteProgramme'])->name('delete-programme-get');

    /* Semester Setting */
    Route::get('/semester-setting', [SettingController::class, 'semesterSetting'])->name('semester-setting');
    Route::post('/add-semester', [SettingController::class, 'addSemester'])->name('add-semester-post');
    Route::post('/update-semester/{id}', [SettingController::class, 'updateSemester'])->name('update-semester-post');
    Route::get('/delete-semester-{id}-{opt}', [SettingController::class, 'deleteSemester'])->name('delete-semester-get');
    Route::post('/change-current-semester', [SettingController::class, 'changeCurrentSemester'])->name('change-semester-post');


    // ---------------------------------------------------------------------------------------------------------------------//
    // -------------------------------------------------- COMMITTEE --------------------------------------------------------//
    // ---------------------------------------------------------------------------------------------------------------------//

    // Nomination
    Route::get('/committee-nomination-{name}', [NominationController::class, 'committeeNomination'])->name('committee-nomination');

    // Evaluation
    Route::get('/committee-evaluation-{name}', [EvaluationController::class, 'committeeEvaluation'])->name('committee-evaluation');


    // ---------------------------------------------------------------------------------------------------------------------//
    // ------------------------------------------------ DEPUTY DEAN --------------------------------------------------------//
    // ---------------------------------------------------------------------------------------------------------------------//

    // Nomination
    Route::get('/deputydean-nomination-{name}', [NominationController::class, 'deputydeanNomination'])->name('deputydean-nomination');


    // ---------------------------------------------------------------------------------------------------------------------//
    // ----------------------------------------------------- DEAN ----------------------------------------------------------//
    // ---------------------------------------------------------------------------------------------------------------------//

    // Nomination
    Route::get('/dean-nomination-{name}', [NominationController::class, 'deanNomination'])->name('dean-nomination');


    // ---------------------------------------------------------------------------------------------------------------------//
    // ------------------------------------------SUPERVISOR / CO - SUPERVISOR ----------------------------------------------//
    // ---------------------------------------------------------------------------------------------------------------------//

    /* My Student */
    Route::get('/mysupervision-student-list', [SupervisorController::class, 'mySupervisionStudentList'])->name('my-supervision-student-list');
    Route::get('/export-mysupervision-student-data', [SupervisorController::class, 'exportMySupervisionStudentList'])->name('export-my-supervision-student-get');

    /* Submission Management */
    Route::get('/mysupervision-submission-management', [SupervisorController::class, 'mySupervisionSubmissionManagement'])->name('my-supervision-submission-management');

    /* Submission Approval */
    Route::get('/mysupervision-submission-approval', [SupervisorController::class, 'mySupervisionSubmissionApproval'])->name('my-supervision-submission-approval');

    /* Correction Approval */
    Route::get('/mysupervision-correction-approval', [SupervisorController::class, 'mySupervisionCorrectionApproval'])->name('my-supervision-correction-approval');

    /* Nomination */
    Route::get('/mysupervision-nomination-{name}', [SupervisorController::class, 'mySupervisionNomination'])->name('my-supervision-nomination');

    /* Evaluation Approval */
    Route::get('/mysupervision-evaluation-approval-{name}', [SupervisorController::class, 'mySupervisionEvaluationApproval'])->name('my-supervision-evaluation-approval');


    // ---------------------------------------------------------------------------------------------------------------------//
    // ---------------------------------------------- EXAMINER / PANEL -----------------------------------------------------//
    // ---------------------------------------------------------------------------------------------------------------------//

    // Evaluation
    Route::get('/examiner-panel-evaluation-{name}', [EvaluationController::class, 'examinerPanelEvaluation'])->name('examiner-panel-evaluation');
    Route::get('/examiner-panel-correction-approval', [SubmissionController::class, 'examinerPanelCorrectionApproval'])->name('examiner-panel-correction-approval');


    // ---------------------------------------------------------------------------------------------------------------------//
    // -------------------------------------------------- CHAIRMAN ---------------------------------------------------------//
    // ---------------------------------------------------------------------------------------------------------------------//

    // Evaluation
    Route::get('/chairman-evaluation-{name}', [EvaluationController::class, 'chairmanEvaluation'])->name('chairman-evaluation');
});
