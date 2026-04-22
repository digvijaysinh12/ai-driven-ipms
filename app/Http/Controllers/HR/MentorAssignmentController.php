<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\MentorAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MentorAssignmentController extends Controller
{
    public function index()
    {
        $interns = User::whereHas('role', fn ($q) => $q->where('name', 'intern'))
            ->where('status', 'approved')
            ->whereDoesntHave('mentorAssignments', fn ($q) => $q->where('is_active', true))
            ->get();

        $mentors = User::whereHas('role', fn ($q) => $q->where('name', 'mentor'))
            ->where('status', 'approved')
            ->get();

        foreach ($mentors as $mentor) {
            $mentor->assignedInternsCount = MentorAssignment::countInternsForMentor($mentor->id);
        }

        return view('hr.mentor_assignments', compact('interns', 'mentors'));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'intern_id' => 'required|exists:users,id',
            'mentor_id' => 'required|exists:users,id',
        ]);

        DB::transaction(function () use ($request) {

            MentorAssignment::where('intern_id', $request->intern_id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            MentorAssignment::create([
                'intern_id' => $request->intern_id,
                'mentor_id' => $request->mentor_id,
                'assigned_by' => auth()->id(),
                'is_active' => true,
                'assigned_at' => now(),
            ]);
        });

        return back()->with('success', 'Mentor assigned successfully.');
    }

    public function list()
    {
        $mappings = MentorAssignment::with(['intern', 'mentor'])
            ->where('is_active', true)
            ->latest()
            ->paginate(10); // ✅ IMPORTANT FIX

        return view('hr.intern_mentor_list', compact('mappings'));
    }
}
