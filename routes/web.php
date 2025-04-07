<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SOPController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupervisionController;
use App\Http\Controllers\AuthenticateController;

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

    //Unfinish
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

    // Setting 

    /* Faculty Setting */
    Route::get('/faculty-setting', [SettingController::class, 'facultySetting'])->name('faculty-setting');
    Route::post('/add-faculty', [SettingController::class, 'addFaculty'])->name('add-faculty-post');
    Route::post('/update-faculty/{id}', [SettingController::class, 'updateFaculty'])->name('update-faculty-post');
    Route::get('/delete-faculty-{id}-{opt}', [SettingController::class, 'deleteFaculty'])->name('delete-faculty-get');

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
