<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HR\DashboardController;
use Illuminate\Support\Facades\Route;

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
| Default Dashboard (for normal users if needed)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified','approved'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| Intern Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','verified','checkrole:intern','approved'])
    ->prefix('intern')
    ->name('intern.')
    ->group(function(){
        
        // Intern Dashboard
        Route::get('/dashboard',function(){
            return view('intern.dashboard');
        })->name('dashboard');
    });


/*
|--------------------------------------------------------------------------
| HR Routes (Fully Protected)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'checkrole:hr'])
    ->prefix('hr')
    ->name('hr.')
    ->group(function () {

        // HR Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // View All Users (optional separate page)
        Route::get('/users', [DashboardController::class, 'users'])
            ->name('users');

        // Approve User
        Route::patch('/users/{id}/approve', [DashboardController::class, 'approve'])
            ->name('users.approve');

        // Reject User
        Route::patch('/users/{id}/reject', [DashboardController::class, 'reject'])
            ->name('users.reject');
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