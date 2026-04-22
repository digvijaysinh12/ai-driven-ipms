<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\MentorAssignment;
use Illuminate\Support\Facades\Auth;

class WaitingController extends Controller
{
    // Show the waiting page when no mentor has been assigned yet.
    public function index()
    {
        $intern = Auth::user();

        // Double-check the assignment before showing the page.
        $mentorAssignment = MentorAssignment::where('intern_id', $intern->id)
            ->where('is_active', true)
            ->first();

        // If a mentor is assigned, go to the dashboard.
        if ($mentorAssignment) {
            return redirect()->route('user.intern.dashboard');
        }

        return view('intern.waiting', compact('intern'));
    }
}
