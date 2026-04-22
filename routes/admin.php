<?php

use App\Http\Controllers\HR\DashboardController as HRDashboardController;
use App\Http\Controllers\HR\InternProgressController as HRInternProgressController;
use App\Http\Controllers\HR\MentorAssignmentController;
use App\Http\Controllers\HR\UserController as HRUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin (HR) Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [HRDashboardController::class, 'index'])->name('dashboard');
Route::get('/attendance', [HRDashboardController::class, 'attendance'])->name('attendance');
Route::get('/users', [HRUserController::class, 'index'])->name('users');
Route::patch('/users/{id}/approve', [HRUserController::class, 'approve'])->name('users.approve');
Route::patch('/users/{id}/reject', [HRUserController::class, 'reject'])->name('users.reject');
Route::post('/assigned-mentor', [MentorAssignmentController::class, 'assign'])->name('assigned.mentor');
Route::get('/mentor-assignments', [MentorAssignmentController::class, 'index'])->name('mentor.assignments');
Route::get('/intern-mentor-list', [MentorAssignmentController::class, 'list'])->name('intern.mentor.list');
Route::get('/intern-progress', [HRInternProgressController::class, 'index'])->name('intern.progress');
Route::get('/intern-progress/{id}', [HRInternProgressController::class, 'show'])->name('intern.progress.show');
