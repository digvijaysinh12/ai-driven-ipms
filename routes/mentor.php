<?php

use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\InternController as MentorInternController;
use App\Http\Controllers\Mentor\SubmissionReviewController;
use App\Http\Controllers\Mentor\TaskController as MentorTaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mentor Routes
|--------------------------------------------------------------------------
*/

// Main mentor area.
Route::prefix('mentor')
    ->name('mentor.')
    ->middleware(['role:mentor', 'approved'])
    ->group(function () {
        // Dashboard and intern progress.
        Route::get('/dashboard', [MentorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/interns', [MentorInternController::class, 'index'])->name('interns');
        Route::get('/interns/{internId}/progress', [MentorInternController::class, 'progress'])->name('interns.progress');

        // Task management.
        Route::get('/tasks', [MentorTaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/create', [MentorTaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [MentorTaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}', [MentorTaskController::class, 'show'])->name('tasks.show');
        Route::match(['put', 'patch'], '/tasks/{task}', [MentorTaskController::class, 'update'])->name('tasks.update');
        Route::post('/tasks/{task}/assign', [MentorTaskController::class, 'assign'])->name('tasks.assign');
        Route::post('/tasks/{task}/generate', [MentorTaskController::class, 'generateQuestions'])->name('tasks.generateQuestions');
        Route::put('/tasks/{task}/questions', [MentorTaskController::class, 'bulkUpdateQuestions'])->name('tasks.questions.bulk-update');
        Route::post('/tasks/{task}/mark-ready', [MentorTaskController::class, 'markReady'])->name('tasks.markReady');

        // Submission reviews.
        Route::get('/submissions', [SubmissionReviewController::class, 'index'])->name('submissions.index');
        Route::get('/submissions/{submission}', [SubmissionReviewController::class, 'show'])->name('submissions.show');
        Route::post('/submissions/{submission}/review', [SubmissionReviewController::class, 'review'])->name('submissions.review');
        Route::post('/submissions/{submission}/ai-evaluate', [SubmissionReviewController::class, 'aiEvaluate'])->name('submissions.aiEvaluate');
    });
