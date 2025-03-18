<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SOPController;
use App\Http\Controllers\SettingController;

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
});


Route::prefix('staff')->group(function () {

    //Standard Operation Procedure (SOP)

    /* Activity Setting */
    Route::get('/activity-setting', [SOPController::class, 'activitySetting'])->name('activity-setting');
    Route::post('/add-activity', [SOPController::class, 'addActivity'])->name('add-activity-post');
    Route::post('/update-activity/{id}', [SOPController::class, 'updateActivity'])->name('update-activity-post');
    Route::get('/delete-activity-{id}-{opt}', [SOPController::class, 'deleteActivity'])->name('delete-activity-get');

    /* Document Setting */
    Route::get('/document-setting', [SOPController::class, 'documentSetting'])->name('document-setting');
    Route::post('/add-document', [SOPController::class, 'addDocument'])->name('add-document-post');
    Route::post('/update-document/{id}', [SOPController::class, 'updateDocument'])->name('update-document-post');
    Route::get('/delete-document-{id}-{opt}', [SOPController::class, 'deleteDocument'])->name('delete-document-get');

    // System Setting 

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
