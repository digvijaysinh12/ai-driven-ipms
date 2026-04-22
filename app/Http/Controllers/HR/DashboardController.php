<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalInterns = User::whereHas('role', fn ($query) => $query->where('name', 'intern'))->count();
        $totalMentors = User::whereHas('role', fn ($query) => $query->where('name', 'mentor'))->count();
        $pendingUsers = User::where('status', 'pending')->count();
        $approvedUsers = User::where('status', 'approved')->count();
        $totalTasks = Task::count();
        $totalSubmissions = TaskSubmission::count();
$reviewedCount = TaskSubmission::whereHas('status', function ($q) {
    $q->where('slug', 'completed');
})->count();

$pendingReviewCount = TaskSubmission::whereHas('status', function ($q) {
    $q->whereIn('slug', ['submitted', 'ai_evaluating', 'ai_evaluated']);
})->count();
        $todayAttendance = Attendance::whereDate('date', now()->toDateString())->count();
        $recentLogins = Attendance::with('user')->latest('login_time')->take(10)->get();

        return view('hr.dashboard', compact(
            'totalInterns',
            'totalMentors',
            'pendingUsers',
            'approvedUsers',
            'totalTasks',
            'totalSubmissions',
            'reviewedCount',
            'pendingReviewCount',
            'todayAttendance',
            'recentLogins'
        ));
    }

    public function attendance(Request $request): View
    {
        $query = Attendance::with('user')->latest('login_time');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $attendances = $query->paginate(20);
        $interns = User::whereHas('role', fn ($query) => $query->where('name', 'intern'))->get();

        return view('hr.attendance', compact('attendances', 'interns'));
    }
}
