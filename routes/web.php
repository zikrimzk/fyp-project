<?php

use Illuminate\Support\Facades\Route;
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
    return view('staff.index',[
        'title' => 'Dashboard'
    ]);
});

/* Faculty Setting */
Route::get('/test/faculty-setting',[SettingController::class,'facultySetting'])->name('faculty-setting');
Route::post('/test/add-faculty',[SettingController::class,'addFaculty'])->name('add-faculty-post');
Route::post('/test/update-faculty/{id}',[SettingController::class,'updateFaculty'])->name('update-faculty-post');
Route::get('/test/delete-faculty/{id}/{opt}',[SettingController::class,'facultySetting'])->name('delete-faculty-get');

/* Department Setting */
Route::get('/test/department-setting',[SettingController::class,'departmentSetting'])->name('department-setting');
