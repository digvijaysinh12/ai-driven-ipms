<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Higher level routing for public areas, auth inclusion, and role groups.
|
*/

Route::get('/', fn () => view('welcome'))->name('home');

// Send each role to the right dashboard.
Route::get('/dashboard', function (Request $request) {
    if (!$user = $request->user()) {
        return redirect()->route('login');
    }

    return match ($user->role?->name) {
        'mentor' => redirect()->route('user.mentor.dashboard'),
        'intern' => redirect()->route('user.intern.dashboard'),
        'hr' => redirect()->route('admin.dashboard'),
        default => abort(403),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/run-code', [\App\Http\Controllers\CodeExecutionController::class, 'run'])
    ->middleware(['auth'])
    ->name('run_code');

// Auth routes.
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/

Route::name('user.')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        // Shared profile routes.
        Route::middleware('auth')->group(function () {
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });

        // Intern routes.
        require __DIR__.'/intern.php';

        // Mentor routes.
        require __DIR__.'/mentor.php';
    });

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'role:hr'])
    ->group(__DIR__.'/admin.php');
