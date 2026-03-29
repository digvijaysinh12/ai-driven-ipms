<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\User;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($this->homeRouteFor($request->user()).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended($this->homeRouteFor($request->user()).'?verified=1');
    }

    private function homeRouteFor(User $user): string
    {
        return match ($user->role->name ?? null) {
            'hr' => route('hr.dashboard', absolute: false),
            'mentor' => route('mentor.dashboard', absolute: false),
            'intern' => route('intern.dashboard', absolute: false),
            default => url('/'),
        };
    }
}
