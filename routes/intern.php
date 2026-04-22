<?php

use App\Http\Controllers\Intern\DashboardController as InternDashboardController;
use App\Http\Controllers\Intern\SubmissionController as InternSubmissionController;
use App\Http\Controllers\Intern\TaskController as InternTaskController;
use App\Http\Controllers\Intern\WaitingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Intern Routes
|--------------------------------------------------------------------------
*/

// Waiting page for approved interns without a mentor.
Route::get('/intern/waiting', [WaitingController::class, 'index'])
    ->name('intern.waiting');

// Main intern area.
Route::prefix('intern')
    ->name('intern.')
    ->middleware(['role:intern', 'approved'])
    ->group(function () {
        Route::middleware('assigned')->group(function () {
            // Dashboard and task pages.
            Route::get('/dashboard', [InternDashboardController::class, 'index'])->name('dashboard');
            Route::get('/tasks', [InternTaskController::class, 'index'])->name('tasks');
            Route::get('/tasks/{task}', [InternTaskController::class, 'show'])->name('tasks.show');
            Route::get('/tasks/{task}/execute', [InternTaskController::class, 'execute'])->name('tasks.execute');
            Route::post('/tasks/{task}/save-answer', [InternSubmissionController::class, 'saveAnswer'])->name('tasks.save_answer');
            Route::post('/tasks/{task}/submit', [InternSubmissionController::class, 'submit'])->name('tasks.submit');
            Route::get('/tasks/{task}/results', [InternSubmissionController::class, 'showResults'])->name('tasks.results');
            Route::get('/submissions', [InternSubmissionController::class, 'index'])->name('submissions');
            Route::get('/submissions/{submission}', [InternSubmissionController::class, 'show'])->name('submissions.show');
            Route::get('/attendance', [InternDashboardController::class, 'attendance'])->name('attendance');
            Route::get('/performance', [InternDashboardController::class, 'performance'])->name('performance');
        });
    });
