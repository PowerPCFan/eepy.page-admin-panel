<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AdminController::class, 'index'])->name('admin.index');
Route::get('/manage/user', [AdminController::class, 'user'])->name('admin.user');
Route::get('/manage/utilities', [AdminController::class, 'utilities'])->name('admin.utilities');
Route::get('/componenttest', [AdminController::class, 'componentTest'])->name('admin.component-test');
Route::get('/manage/domain/{userId}/{domain}/{type?}', [AdminController::class, 'domainDetail'])->name('admin.domain.detail');
Route::get('/manage/manual-session', [AdminController::class, 'manualSession'])->name('admin.manual-session');
Route::post('/login', [AdminController::class, 'login'])->middleware('throttle:5,1')->name('admin.login');
Route::post('/search', [AdminController::class, 'search'])->name('admin.search');
Route::post('/action', [AdminController::class, 'action'])->name('admin.action');
Route::post('/manage/manual-session/terminate', [AdminController::class, 'terminateManualSession'])->name('admin.manual-session.terminate');
Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
