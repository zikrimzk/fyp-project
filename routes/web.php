<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SOPController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupervisionController;

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

Route::get('/', function () {
    return view('staff.index', [
        'title' => 'Dashboard'
    ]);
})->name('dashboard');


Route::prefix('staff')->group(function () {

    // Supervision

    /* Student Management */
    Route::get('/student-management', [SupervisionController::class, 'studentManagement'])->name('student-management');
    Route::post('/add-student', [SupervisionController::class, 'addStudent'])->name('add-student-post');
    Route::post('/update-student-{id}', [SupervisionController::class, 'updateStudent'])->name('update-student-post');
    Route::get('/delete-student-{id}-{opt}', [SupervisionController::class, 'deleteStudent'])->name('delete-student-get');
    //Unfinished
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

    //Unfinished
    Route::post('/add-supervision-{id}', [SupervisionController::class, 'addSupervision'])->name('add-supervision-post');
    Route::post('/update-supervision-{id}', [SupervisionController::class, 'updateSupervision'])->name('update-supervision-post');
    Route::get('/delete-supervision-{id}', [SupervisionController::class, 'deleteSupervision'])->name('delete-supervision-get');



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
