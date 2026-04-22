<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\MentorAssignment;
use App\Models\TaskSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $intern = Auth::user();

        $mentorAssignment = MentorAssignment::query()
            ->where('intern_id', $intern->id)
            ->where('is_active', true)
            ->with('mentor')
            ->first();

        $mentor = $mentorAssignment?->mentor;

        // ✅ FIX: use user_id
        $tasks = $intern->assignedTasks()
            ->with(['type', 'submissions' => fn($q) => $q->where('user_id', $intern->id)])
            ->withCount('questions')
            ->latest('task_user.assigned_at')
            ->get();

        // ✅ FIX: user_id + load status
        $submissions = TaskSubmission::query()
            ->where('user_id', $intern->id)
            ->with(['task', 'reviewer', 'status'])
            ->latest('submitted_at')
            ->get();

        $totalTasksCount = $tasks->count();

        // ✅ FIX: proper status filtering
        $completedTasksCount = $submissions->filter(function ($s) {
            return in_array($s->status?->slug, ['completed', 'ai_evaluated']);
        })->count();

        // ✅ FIX: correct pending logic
        $submittedTaskIds = $submissions->pluck('task_id')->unique();
        $pendingTasksCount = $totalTasksCount - $submittedTaskIds->count();

        $averageScore = $submissions->whereNotNull('score')->avg('score') ?? 0;

        return view('intern.dashboard', compact(
            'mentor',
            'tasks',
            'submissions',
            'totalTasksCount',
            'completedTasksCount',
            'pendingTasksCount',
            'averageScore'
        ));
    }

    public function attendance(): View
    {
        $internId = Auth::id();

        $mentorAssignment = MentorAssignment::query()
            ->where('intern_id', $internId)
            ->where('is_active', true)
            ->with('mentor')
            ->first();

        $todayAttendance = Attendance::query()
            ->where('user_id', $internId)
            ->whereDate('date', today())
            ->latest('login_time')
            ->first();

        $recentAttendances = Attendance::query()
            ->where('user_id', $internId)
            ->latest('login_time')
            ->take(10)
            ->get();

        $totalTrackedSeconds = Attendance::query()
            ->where('user_id', $internId)
            ->sum('total_seconds');

        return view('intern.attendance.index', compact(
            'mentorAssignment',
            'todayAttendance',
            'recentAttendances',
            'totalTrackedSeconds'
        ));
    }

    public function performance(): View
    {
        $intern = Auth::user();

        $tasks = $intern->assignedTasks()
            ->with(['type', 'submissions' => fn($q) => $q->where('user_id', $intern->id)])
            ->withCount('questions')
            ->get();

        $submissions = TaskSubmission::query()
            ->where('user_id', $intern->id)
            ->with(['task', 'reviewer', 'status'])
            ->latest('submitted_at')
            ->get();

        $totalTasks = $tasks->count();
        $totalSubmissions = $submissions->count();

        return view('intern.performance.index', compact(
            'tasks',
            'submissions',
            'totalTasks',
            'totalSubmissions'
        ));
    }
}