<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HR\DashboardController;
use App\Http\Controllers\HR\MentorAssignmentController;

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
| Default Dashboard (Optional)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'approved'])
  ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Intern Waiting Route (NO assigned middleware)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','verified','approved','checkrole:intern'])
    ->prefix('intern')
    ->name('intern.')
    ->group(function(){

        Route::get('/waiting', function(){
            return view('intern.waiting');
        })->name('waiting');

});


/*
|--------------------------------------------------------------------------
| Intern Protected Routes (WITH assigned middleware)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','verified','approved','assigned','checkrole:intern'])
    ->prefix('intern')
    ->name('intern.')
    ->group(function(){

        Route::get('/dashboard', function(){
            return view('intern.dashboard');
        })->name('dashboard');

});


/*
|--------------------------------------------------------------------------
| Mentor Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','verified','approved','checkrole:mentor'])
    ->prefix('mentor')
    ->name('mentor.')
    ->group(function(){

        Route::get('/dashboard', function(){
            return view('mentor.dashboard');
        })->name('dashboard');

        Route::get('/interns', function(){
            return view('mentor.interns');
        })->name('interns');

        Route::get('/assignments', function(){
            return view('mentor.assignments');
        })->name('assignments');

});

/*
|--------------------------------------------------------------------------
| HR Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','verified','checkrole:hr'])
    ->prefix('hr')
    ->name('hr.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'users'])
            ->name('dashboard');

        Route::get('/users', [DashboardController::class, 'index'])
            ->name('users');

        Route::patch('/users/{id}/approve', [DashboardController::class, 'approve'])
            ->name('users.approve');

        Route::patch('/users/{id}/reject', [DashboardController::class, 'reject'])
            ->name('users.reject');

        Route::post('/assigned-mentor', [MentorAssignmentController::class, 'assign'])
            ->name('assigned.mentor');

        Route::get('/mentor-assignments', 
            [MentorAssignmentController::class, 'index']
        )->name('mentor.assignments');
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

require __DIR__.'/auth.php';