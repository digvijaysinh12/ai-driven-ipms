<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $mentorId = Auth::id();

        $assignedInternsCount = DB::table('mentor_assignments')
            ->where('mentor_id', $mentorId)
            ->where('is_active', 1)
            ->count();

        $tasks = Task::query()
            ->where('created_by', $mentorId)
            ->with('submissions')
            ->get();

        $taskIds = $tasks->pluck('id');

        $totalTasksCount = $tasks->count();
        $draftTasksCount = $tasks->filter(fn (Task $task) => $task->isDraft())->count();
        $readyTasksCount = $tasks->filter(fn (Task $task) => $task->isReady())->count();

        $pendingSubmissionsCount = TaskSubmission::query()
            ->whereIn('task_id', $taskIds)
            ->pending()
            ->count();

        $reviewedCount = TaskSubmission::query()
            ->whereIn('task_id', $taskIds)
            ->reviewed()
            ->count();

        $recentTasks = Task::query()
            ->where('created_by', $mentorId)
            ->withCount('questions')
            ->latest()
            ->take(5)
            ->get();

        $recentSubmissions = TaskSubmission::query()
            ->whereIn('task_id', $taskIds)
            ->whereNotNull('submitted_at')
            ->with(['intern', 'task'])
            ->latest('submitted_at')
            ->take(5)
            ->get();

        $interns = User::whereHas('currentMentorAssignment', function ($q) use ($mentorId) {
            $q->where('mentor_id', $mentorId);
        })->get();

        return view('mentor.dashboard', compact(
            'assignedInternsCount',
            'totalTasksCount',
            'draftTasksCount',
            'readyTasksCount',
            'pendingSubmissionsCount',
            'reviewedCount',
            'recentTasks',
            'recentSubmissions',
            'interns'
        ));
    }
}
