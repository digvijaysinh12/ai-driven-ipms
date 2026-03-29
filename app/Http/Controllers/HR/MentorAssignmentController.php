<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MentorAssignment;
use Illuminate\Support\Facades\DB;

class MentorAssignmentController extends Controller
{
    /**
     * Assign Mentor Page
     */
    public function index()
    {
        // Interns without active mentor
        $interns = User::whereHas('role', fn ($q) => $q->where('name', 'intern'))
            ->where('status', 'approved')
            ->whereDoesntHave('mentorAssignments', fn ($q) => $q->where('is_active', true))
            ->get();

        // All approved mentors
        $mentors = User::whereHas('role', fn ($q) => $q->where('name', 'mentor'))
            ->where('status', 'approved')
            ->get();

        return view('hr.mentor_assignments', compact('interns', 'mentors'));
    }

    /**
     * Assign Mentor Logic
     */
    public function assign(Request $request)
    {
        $request->validate([
            'intern_id' => 'required|exists:users,id',
            'mentor_id' => 'required|exists:users,id',
        ]);

        DB::transaction(function () use ($request) {
            // Deactivate old assignment if any
            MentorAssignment::where('intern_id', $request->intern_id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Create new assignment
            MentorAssignment::create([
                'intern_id'   => $request->intern_id,
                'mentor_id'   => $request->mentor_id,
                'assigned_by' => auth()->id(),
                'is_active'   => true,
                'assigned_at' => now(),
            ]);
        });

        return back()->with('success', 'Mentor assigned successfully.');
    }

    /**
     * Intern-Mentor Mapping List
     */
    public function list()
    {
        $assignments = MentorAssignment::with(['intern', 'mentor'])
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('hr.intern_mentor_list', compact('assignments'));
    }
}