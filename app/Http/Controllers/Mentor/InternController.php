<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InternController extends Controller
{
    public function index(): View
    {
        $mentorId = Auth::id();

        $interns = DB::table('mentor_assignments')
            ->join('users', 'mentor_assignments.intern_id', '=', 'users.id')
            ->where('mentor_assignments.mentor_id', $mentorId)
            ->where('mentor_assignments.is_active', 1)
            ->select('users.id', 'users.name', 'users.email', 'mentor_assignments.assigned_at')
            ->get();

        $taskIds = Task::query()->where('created_by', $mentorId)->pluck('id');

        foreach ($interns as $intern) {
            $submissions = TaskSubmission::query()
                ->where('user_id', $intern->id)
                ->whereIn('task_id', $taskIds)
                ->with('status')
                ->get();

            $intern->total_submissions = $submissions->count();
            $intern->pending_reviews = $submissions->filter(fn($sub) => 
                in_array($sub->status?->slug, ['submitted', 'ai_evaluated'])
            )->count();
        }

        return view('mentor.interns.index', compact('interns'));
    }

    public function progress(int $internId): View
    {
        $mentorId = Auth::id();

        $assigned = DB::table('mentor_assignments')
            ->where('mentor_id', $mentorId)
            ->where('intern_id', $internId)
            ->where('is_active', 1)
            ->exists();

        abort_unless($assigned, 403, 'This intern is not assigned to you.');

        $intern = User::findOrFail($internId);

        $tasks = Task::query()
            ->where('created_by', $mentorId)
            ->whereHas('users', fn ($query) => $query->where('users.id', $internId))
            ->withCount('questions')
            ->with(['type:id,name,slug'])
            ->with('submissions')
            ->get();

        $taskIds = Task::query()->where('created_by', $mentorId)->pluck('id');

        $submissions = TaskSubmission::query()
            ->where('user_id', $internId)
            ->whereIn('task_id', $taskIds)
            ->with(['task', 'reviewer', 'status'])
            ->latest('submitted_at')
            ->get();

        $totalSubmissions = $submissions->count();
        $reviewedCount = $submissions->filter(fn($sub) => 
            $sub->status?->slug === 'completed'
        )->count();

        return view('mentor.interns.progress', compact(
            'intern',
            'tasks',
            'submissions',
            'totalSubmissions',
            'reviewedCount'
        ));
    }
}
