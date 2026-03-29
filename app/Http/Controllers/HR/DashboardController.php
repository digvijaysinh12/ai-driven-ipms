<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MentorAssignment;
use App\Models\Topic;
use App\Models\Question;
use App\Models\InternTopicAssignment;
use App\Models\Submission;

class DashboardController extends Controller
{
    /**
     * Pending approvals page
     */
    public function index()
    {
        $users = User::with('role')
            ->where('status', 'pending')
            ->whereNotNull('email_verified_at')
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['intern', 'mentor']))
            ->latest()
            ->get();

        return view('hr.approvals', compact('users'));
    }

    /**
     * HR Dashboard — stats
     */
    public function users()
    {
        $totalUsers   = User::count();
        $totalInterns = User::whereHas('role', fn ($q) => $q->where('name', 'intern'))->count();
        $totalMentors = User::whereHas('role', fn ($q) => $q->where('name', 'mentor'))->count();

        $pendingUsers  = User::where('status', 'pending')->count();
        $approvedUsers = User::where('status', 'approved')->count();
        $rejectedUsers = User::where('status', 'rejected')->count();

        $assignedInterns = MentorAssignment::where('is_active', true)->count();
        $topics          = Topic::count();
        $questions       = Question::count();
        $assignments     = InternTopicAssignment::count();
        $submitted       = InternTopicAssignment::where('status', 'submitted')->count();
        $evaluated       = InternTopicAssignment::where('status', 'evaluated')->count();

        return view('hr.dashboard', compact(
            'totalUsers', 'totalInterns', 'totalMentors',
            'pendingUsers', 'approvedUsers', 'rejectedUsers',
            'assignedInterns', 'topics', 'questions',
            'assignments', 'submitted', 'evaluated'
        ));
    }

    /**
     * Approve user
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);

        if ($user->role->name === 'hr') {
            return back()->with('error', 'Cannot approve HR account.');
        }

        if (! $user->email_verified_at) {
            return back()->with('error', 'User email not verified yet.');
        }

        $user->update(['status' => 'approved']);

        return back()->with('success', 'User approved successfully.');
    }

    /**
     * Reject user
     */
    public function reject($id)
    {
        $user = User::findOrFail($id);

        if ($user->role->name === 'hr') {
            return back()->with('error', 'Cannot reject HR account.');
        }

        $user->update(['status' => 'rejected']);

        return back()->with('success', 'User rejected successfully.');
    }

    /**
     * Intern progress overview
     */
    public function internProgress()
    {
        $interns = User::whereHas('role', fn ($q) => $q->where('name', 'intern'))
            ->where('status', 'approved')
            ->with('currentMentorAssignment.mentor')
            ->get();

        return view('hr.intern_progress', compact('interns'));
    }

    /**
     * Intern progress detail
     */
    public function internProgressShow($id)
    {
        $intern = User::with('role')->findOrFail($id);
        abort_unless($intern->role->name === 'intern', 404);

        $mentorAssignment = MentorAssignment::with('mentor')
            ->where('intern_id', $id)
            ->where('is_active', 1)
            ->first();

        $topicAssignments = InternTopicAssignment::with('topic')
            ->where('intern_id', $id)
            ->latest('assigned_at')
            ->get();

        $totalSubmissions = Submission::where('intern_id', $id)->count();
        $reviewedCount    = Submission::where('intern_id', $id)
            ->where('status', 'reviewed')
            ->count();

        return view('hr.intern_progress_show', compact(
            'intern',
            'mentorAssignment',
            'topicAssignments',
            'totalSubmissions',
            'reviewedCount'
        ));
    }
}