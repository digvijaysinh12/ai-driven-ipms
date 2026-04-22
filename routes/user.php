<?php

use App\Http\Controllers\Intern\DashboardController as InternDashboardController;
use App\Http\Controllers\Intern\SubmissionController as InternSubmissionController;
use App\Http\Controllers\Intern\TaskController as InternTaskController;
use App\Http\Controllers\Intern\WaitingController;
use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\InternController as MentorInternController;
use App\Http\Controllers\Mentor\SubmissionReviewController;
use App\Http\Controllers\Mentor\TaskController as MentorTaskController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Routes (Intern & Mentor)
|--------------------------------------------------------------------------
*/

// Profile Management (Shared)
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// Intern Specific Routes
Route::prefix('intern')->name('intern.')->middleware(['role:intern', 'approved'])->group(function () {
    Route::get('/waiting', [WaitingController::class, 'index'])->name('waiting');

    Route::middleware('assigned')->group(function () {
        Route::get('/dashboard', [InternDashboardController::class, 'index'])->name('dashboard');
        Route::get('/tasks', [InternTaskController::class, 'index'])->name('tasks');
        Route::get('/tasks/{task}', [InternTaskController::class, 'show'])->name('tasks.show');
        Route::get('/tasks/{task}/execute', [InternTaskController::class, 'execute'])->name('tasks.execute');
        Route::post('/tasks/{task}/submit', [InternSubmissionController::class, 'submit'])->name('tasks.submit');
        Route::get('/tasks/{task}/results', [InternSubmissionController::class, 'showResults'])->name('tasks.results');
        Route::get('/submissions', [InternSubmissionController::class, 'index'])->name('submissions');
        Route::get('/submissions/{submission}', [InternSubmissionController::class, 'show'])->name('submissions.show');
        Route::get('/attendance', [InternDashboardController::class, 'attendance'])->name('attendance');
        Route::get('/performance', [InternDashboardController::class, 'performance'])->name('performance');
    });
});

// Mentor Specific Routes
Route::prefix('mentor')->name('mentor.')->middleware(['role:mentor', 'approved'])->group(function () {
    Route::get('/dashboard', [MentorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/interns', [MentorInternController::class, 'index'])->name('interns');
    Route::get('/interns/{internId}/progress', [MentorInternController::class, 'progress'])->name('interns.progress');
    Route::get('/tasks', [MentorTaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/create', [MentorTaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [MentorTaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [MentorTaskController::class, 'show'])->name('tasks.show');
    Route::match(['put', 'patch'], '/tasks/{task}', [MentorTaskController::class, 'update'])->name('tasks.update');
    Route::post('/tasks/{task}/assign', [MentorTaskController::class, 'assign'])->name('tasks.assign');
    Route::post('/tasks/{task}/generate', [MentorTaskController::class, 'generateQuestions'])->name('tasks.generateQuestions');
    
Route::put('/tasks/{task}/questions', [MentorTaskController::class, 'bulkUpdateQuestions'])
    ->name('tasks.questions.bulk-update');

    Route::post('/tasks/{task}/mark-ready', [MentorTaskController::class, 'markReady'])->name('tasks.markReady');

    Route::get('/submissions', [SubmissionReviewController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{submission}', [SubmissionReviewController::class, 'show'])->name('submissions.show');
    Route::post('/submissions/{submission}/review', [SubmissionReviewController::class, 'review'])->name('submissions.review');
    Route::post('/submissions/{submission}/ai-evaluate', [SubmissionReviewController::class, 'aiEvaluate'])->name('submissions.aiEvaluate');
});
