<?php

use App\Http\Controllers\Api\Mentor\DashboardController;
use App\Http\Controllers\Api\Mentor\SubmissionReviewController;
use App\Http\Controllers\Api\Mentor\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1/mentor')
    ->name('api.v1.mentor.')
    ->middleware(['auth:sanctum', 'role:mentor'])
    ->group(function () {
        
        // Dashboard Stats
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Task Management
        Route::post('/tasks/generate-questions-preview', [TaskController::class, 'generatePreview'])->name('tasks.generate-preview');
        Route::post('/tasks/store-full', [TaskController::class, 'storeFull'])->name('tasks.store-full');
        Route::apiResource('tasks', TaskController::class);
        Route::post('/tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');
        Route::post('/tasks/{task}/generate-questions', [TaskController::class, 'generateQuestions'])->name('tasks.generate-questions');

        // Submission Reviews
        Route::get('/submissions', [SubmissionReviewController::class, 'index'])->name('submissions.index');
        Route::get('/submissions/{submission}', [SubmissionReviewController::class, 'show'])->name('submissions.show');
        Route::post('/submissions/{submission}/review', [SubmissionReviewController::class, 'review'])->name('submissions.review');
        Route::post('/submissions/{submission}/ai-evaluate', [SubmissionReviewController::class, 'aiEvaluate'])->name('submissions.ai-evaluate');

    });
