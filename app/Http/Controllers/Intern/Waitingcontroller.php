<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\MentorAssignment;

class WaitingController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // Show waiting page
    // Intern is approved by HR but no mentor has been assigned yet
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $intern = Auth::user();

        // Check if a mentor was actually assigned
        // (middleware should block this, but double check here)
        $mentorAssignment = MentorAssignment::where('intern_id', $intern->id)
            ->where('is_active', true)
            ->first();

        // If mentor is now assigned, redirect to dashboard
        if ($mentorAssignment) {
            return redirect()->route('intern.dashboard');
        }

        return view('intern.waiting', compact('intern'));
    }
}