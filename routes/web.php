<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;

// ── HR ───────────────────────────────────────────────────────────────────────
use App\Http\Controllers\HR\DashboardController as HRDashboardController;
use App\Http\Controllers\HR\MentorAssignmentController;

// ── Intern ───────────────────────────────────────────────────────────────────
use App\Http\Controllers\Intern\WaitingController;
use App\Http\Controllers\Intern\DashboardController as InternDashboardController;
use App\Http\Controllers\Intern\TopicController as InternTopicController;
use App\Http\Controllers\Intern\SubmissionController;

// ── Mentor ───────────────────────────────────────────────────────────────────
use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\TopicController;
use App\Http\Controllers\Mentor\InternController;
use App\Http\Controllers\Mentor\TopicAssignController;
use App\Http\Controllers\Mentor\SubmissionReviewController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| Intern Routes
|--------------------------------------------------------------------------
*/


// Waiting: approved but no mentor yet
Route::get('/intern/waiting', [WaitingController::class, 'index'])
    ->middleware(['auth', 'verified', 'approved'])
    ->name('intern.waiting');

// All intern routes
Route::prefix('intern')
    ->middleware(['auth', 'verified', 'approved', 'assigned'])
    ->name('intern.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [InternDashboardController::class, 'index'])
            ->name('dashboard');

        // Topic overview (module cards)
        Route::get('/topic', [InternTopicController::class, 'index'])
            ->name('topic');

        // ── Exam mode ─────────────────────────────────────────────────
        // Open a module as an exam (one question at a time)
        Route::get('/exam/{assignmentId}/{type}', [InternTopicController::class, 'exam'])
            ->name('exam');

        // AJAX: save a single answer during exam (no evaluation)
        Route::post('/exam/save', [InternTopicController::class, 'saveAnswer'])
            ->name('exam.save');

        // FINAL SUBMIT: evaluates all answers via AI, locks module
        Route::post('/final-submit/{assignmentId}', [SubmissionController::class, 'finalSubmit'])
            ->name('final.submit');

        // PHP code runner for coding questions
        Route::post('/run-code', [SubmissionController::class, 'runCode'])
            ->name('run.code');

        // Submissions list (scores hidden until mentor reviews)
        Route::get('/submissions', [SubmissionController::class, 'index'])
            ->name('submissions');
    });


/*
|--------------------------------------------------------------------------
| Mentor Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'approved', 'checkrole:mentor'])
    ->prefix('mentor')
    ->name('mentor.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [MentorDashboardController::class, 'index'])
            ->name('dashboard');

        // Interns list + progress
        Route::get('/interns', [InternController::class, 'index'])
            ->name('interns');

        Route::get('/interns/{internId}/progress', [InternController::class, 'progress'])
            ->name('interns.progress');

        // All assignments made by this mentor
        Route::get('/assignments', [TopicAssignController::class, 'index'])
            ->name('assignments');

        // ── Assign topic to intern ──────────────────────────────
        // IMPORTANT: These two routes must come BEFORE Route::resource('topics')
        // to avoid being swallowed by topics/{topic} pattern

        Route::get('/assign', [TopicAssignController::class, 'create'])
            ->name('topics.assign');

        Route::post('/assign', [TopicAssignController::class, 'store'])
            ->name('topics.assign.store');

        // ── Topic CRUD ──────────────────────────────────────────
        Route::resource('topics', TopicController::class);

        // AI question generation
        Route::post('topics/{topic}/generate-ai', [TopicController::class, 'generateAI'])
            ->name('topics.generateAI');

        // Publish topic
        Route::post('topics/{topic}/publish', [TopicController::class, 'publish'])
            ->name('topics.publish');

        // View questions by type
        Route::get('topics/{topic}/questions/{type}', [TopicController::class, 'showQuestions'])
            ->name('topics.questions');

        // ── Submission review ───────────────────────────────────
        Route::get('/submissions', [SubmissionReviewController::class, 'index'])
            ->name('submissions.index');

        Route::get('/submissions/{id}', [SubmissionReviewController::class, 'show'])
            ->name('submissions.show');

        Route::post('/submissions/{id}/review', [SubmissionReviewController::class, 'review'])
            ->name('submissions.review');
    });


/*
|--------------------------------------------------------------------------
| HR Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'checkrole:hr'])
    ->prefix('hr')
    ->name('hr.')
    ->group(function () {

        Route::get('/dashboard', [HRDashboardController::class, 'users'])
            ->name('dashboard');

        Route::get('/users', [HRDashboardController::class, 'index'])
            ->name('users');

        Route::patch('/users/{id}/approve', [HRDashboardController::class, 'approve'])
            ->name('users.approve');

        Route::patch('/users/{id}/reject', [HRDashboardController::class, 'reject'])
            ->name('users.reject');

        Route::post('/assigned-mentor', [MentorAssignmentController::class, 'assign'])
            ->name('assigned.mentor');

        Route::get('/mentor-assignments', [MentorAssignmentController::class, 'index'])
            ->name('mentor.assignments');

        Route::get('/intern-mentor', [MentorAssignmentController::class, 'index'])
            ->name('hr.intern.mentor');

        Route::get('/intern-mentor-list', [MentorAssignmentController::class, 'list'])
            ->name('intern.mentor.list');

        Route::get('/intern-progress', [HRDashboardController::class, 'internProgress'])
            ->name('intern.progress');
    });


/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';