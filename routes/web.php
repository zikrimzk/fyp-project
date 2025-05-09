<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SOPController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupervisionController;
use App\Http\Controllers\AuthenticateController;
use App\Http\Controllers\SubmissionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

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
    Route::get('/confirm-student-submission-{actID}', [SubmissionController::class, 'confirmStudentSubmission'])->name('student-confirm-submission-get');
    Route::get('/view-final-document/{actID}/{filename}', [SubmissionController::class, 'viewFinalDocument'])->where('filename', '.*')->name('student-view-final-document-get');

});



Route::prefix('staff')->middleware('auth:staff')->group(function () {

    //Authentication - Account Management

    /* Staff Dashboard */
    Route::get('/dashboard', [AuthenticateController::class, 'staffDashboard'])->name('staff-dashboard');

    /* Staff Profile */
    Route::get('/my-profile', [AuthenticateController::class, 'staffProfile'])->name('staff-profile');
    Route::post('/update-profile', [AuthenticateController::class, 'updateStaffProfile'])->name('update-staff-profile');
    Route::post('/update-password', [AuthenticateController::class, 'updateStaffPassword'])->name('update-staff-password');


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

    // Submission

    /* Submission Management */
    Route::get('/submission-management', [SubmissionController::class, 'submissionManagement'])->name('submission-management');
    Route::get('/assign-student-submission', [SubmissionController::class, 'assignSubmission'])->name('assign-student-submission');
    Route::post('/update-submission-{id}', [SubmissionController::class, 'updateSubmission'])->name('update-submission-post');
    Route::get('/archive-submission-{id}-{opt}', [SubmissionController::class, 'archiveSubmission'])->name('archive-submission-get');
    Route::post('/update-multiple-submission', [SubmissionController::class, 'updateMultipleSubmission'])->name('update-multiple-submission-post');
    Route::post('/archive-multiple-submission', [SubmissionController::class, 'archiveMultipleSubmission'])->name('archive-multiple-submission-post');
    Route::get('/download-multiple-submission', [SubmissionController::class, 'downloadMultipleSubmission'])->name('download-multiple-submission-get');




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
});
