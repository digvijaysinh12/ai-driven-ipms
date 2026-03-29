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
<<<<<<< HEAD
    /**
     * Pending approvals page
     */
=======

    /*
    |---------------------------------------
    | Pending approvals page
    |---------------------------------------
    */

>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
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

<<<<<<< HEAD
    /**
     * HR Dashboard — stats
     */
    public function users()
    {
        $totalUsers   = User::count();
        $totalInterns = User::whereHas('role', fn ($q) => $q->where('name', 'intern'))->count();
        $totalMentors = User::whereHas('role', fn ($q) => $q->where('name', 'mentor'))->count();

=======

    /*
    |---------------------------------------
    | HR Dashboard
    |---------------------------------------
    */

    public function users()
    {
        // User stats
        $totalUsers   = User::count();
        $totalInterns = User::where('role_id', 3)->count();
        $totalMentors = User::where('role_id', 2)->count();

        // Approval stats
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        $pendingUsers  = User::where('status', 'pending')->count();
        $approvedUsers = User::where('status', 'approved')->count();
        $rejectedUsers = User::where('status', 'rejected')->count();

<<<<<<< HEAD
        $assignedInterns = MentorAssignment::where('is_active', true)->count();
=======
        // Internship stats
        $assignedInterns = MentorAssignment::count();
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        $topics          = Topic::count();
        $questions       = Question::count();
        $assignments     = InternTopicAssignment::count();
        $submitted       = InternTopicAssignment::where('status', 'submitted')->count();
        $evaluated       = InternTopicAssignment::where('status', 'evaluated')->count();

        return view('hr.dashboard', compact(
<<<<<<< HEAD
            'totalUsers', 'totalInterns', 'totalMentors',
            'pendingUsers', 'approvedUsers', 'rejectedUsers',
            'assignedInterns', 'topics', 'questions',
            'assignments', 'submitted', 'evaluated'
=======
            'totalUsers',
            'totalInterns',
            'totalMentors',
            'pendingUsers',
            'approvedUsers',
            'rejectedUsers',
            'assignedInterns',
            'topics',
            'questions',
            'assignments',
            'submitted',
            'evaluated'
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        ));
    }


    /*
    |---------------------------------------
    | Approve user
    |---------------------------------------
    */

    public function approve($id)
    {
        $user = User::findOrFail($id);

        if ($user->role->name === 'hr') {
<<<<<<< HEAD
            return back()->with('error', 'Cannot approve HR account.');
        }

        if (! $user->email_verified_at) {
            return back()->with('error', 'User email not verified yet.');
=======
            return back()->with('error', 'Cannot approve HR account');
        }

        if (!$user->email_verified_at) {
            return back()->with('error', 'User email not verified');
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        }

        $user->update(['status' => 'approved']);

<<<<<<< HEAD
        return back()->with('success', 'User approved successfully.');
=======
        return back()->with('success', 'User approved successfully');
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    }


    /*
    |---------------------------------------
    | Reject user
    |---------------------------------------
    */

    public function reject($id)
    {
        $user = User::findOrFail($id);

        if ($user->role->name === 'hr') {
<<<<<<< HEAD
            return back()->with('error', 'Cannot reject HR account.');
=======
            return back()->with('error', 'Cannot reject HR account');
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        }

        $user->update(['status' => 'rejected']);

<<<<<<< HEAD
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
=======
        return back()->with('success', 'User rejected successfully');
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    }


    /*
    |---------------------------------------
    | Intern progress overview (generic)
    |---------------------------------------
    */

    public function internProgress()
    {
        return view('hr.intern_progress');
    }


    /*
    |---------------------------------------
    | Intern progress detail (by intern ID)
    |---------------------------------------
    */

    public function internProgressShow($id)
    {
        $intern = User::with('role')->findOrFail($id);

        abort_unless($intern->role->name === 'intern', 404);

        // Mentor assignment
        $mentorAssignment = MentorAssignment::with('mentor')
            ->where('intern_id', $id)
            ->where('is_active', 1)
            ->first();

        // Topic assignments with topic
        $topicAssignments = InternTopicAssignment::with('topic')
            ->where('intern_id', $id)
            ->latest('assigned_at')
            ->get();

        // Submission stats
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