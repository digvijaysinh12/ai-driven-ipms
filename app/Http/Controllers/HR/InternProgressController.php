<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\MentorAssignment;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\View\View;

class InternProgressController extends Controller
{
    public function index(): View
    {
        $interns = User::query()
            ->whereHas('role', fn ($query) => $query->where('name', 'intern'))
            ->where('status', 'approved')
            ->with('currentMentorAssignment.mentor')
            ->get();

        foreach ($interns as $intern) {
            $intern->task_count = Task::query()
                ->whereHas('users', fn ($query) => $query->where('users.id', $intern->id))
                ->count();
            $intern->submission_count = TaskSubmission::where('intern_id', $intern->id)->count();
            $intern->reviewed_count = TaskSubmission::where('intern_id', $intern->id)
                ->where('status', 'reviewed')
                ->count();
        }

        return view('hr.intern_progress', compact('interns'));
    }

    public function show(int $id): View
    {
        $intern = User::with('role')->findOrFail($id);
        abort_unless($intern->role?->name === 'intern', 404);

        $mentorAssignment = MentorAssignment::with('mentor')
            ->where('intern_id', $id)
            ->where('is_active', 1)
            ->first();

        $tasks = Task::query()
            ->whereHas('users', fn ($query) => $query->where('users.id', $id))
            ->withCount('questions')
            ->with(['type:id,name,slug'])
            ->with('submissions')
            ->latest()
            ->get();

        $submissions = TaskSubmission::query()
            ->where('intern_id', $id)
            ->with(['task', 'reviewer'])
            ->latest('submitted_at')
            ->get();

        $totalSubmissions = $submissions->count();
        $reviewedCount = $submissions->where('status', 'reviewed')->count();

        return view('hr.intern_progress_show', compact(
            'intern',
            'mentorAssignment',
            'tasks',
            'submissions',
            'totalSubmissions',
            'reviewedCount'
        ));
    }
}
