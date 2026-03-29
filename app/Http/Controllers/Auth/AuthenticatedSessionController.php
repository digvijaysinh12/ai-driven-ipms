<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = auth()->user();

        // Check email verification
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // HR bypasses status check (seeded as approved)
        if ($user->role->name !== 'hr' && $user->status !== 'approved') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $user->status === 'rejected'
                ? 'Your account has been rejected. Please contact HR.'
                : 'Your account is pending HR approval. Please wait.';

            return redirect()->route('login')->with('error', $message);
        }

        return match ($user->role->name) {
            'hr'     => redirect()->route('hr.dashboard'),
            'mentor' => redirect()->route('mentor.dashboard'),
            'intern' => redirect()->route('intern.dashboard'),
            default  => redirect()->route('dashboard'),
        };
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}