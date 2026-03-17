<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MentorAssignment;
use App\Models\Topic;
use App\Models\Question;
use App\Models\InternTopicAssignment;

class DashboardController extends Controller
{

    /*
    |---------------------------------------
    | Pending approvals page
    |---------------------------------------
    */

    public function index()
    {
        $users = User::with('role')
            ->where('status', 'pending')
            ->whereNotNull('email_verified_at')
            ->whereHas('role', function ($query) {
                $query->whereIn('name', ['intern', 'mentor']);
            })
            ->latest()
            ->get();

        return view('hr.approvals', compact('users'));
    }


    /*
    |---------------------------------------
    | HR Dashboard
    |---------------------------------------
    */

    public function users()
    {

        // User stats
        $totalUsers = User::count();

        $totalInterns = User::where('role_id',3)->count();

        $totalMentors = User::where('role_id',2)->count();


        // Approval stats
        $pendingUsers = User::where('status','pending')->count();

        $approvedUsers = User::where('status','approved')->count();

        $rejectedUsers = User::where('status','rejected')->count();


        // Internship stats
        $assignedInterns = MentorAssignment::count();

        $topics = Topic::count();

        $questions = Question::count();

        $assignments = InternTopicAssignment::count();

        $submitted = InternTopicAssignment::where('status','submitted')->count();

        $evaluated = InternTopicAssignment::where('status','evaluated')->count();


        return view('hr.dashboard',compact(

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
            return back()->with('error','Cannot approve HR account');
        }

        if(!$user->email_verified_at){
            return back()->with('error','User email not verified');
        }

        $user->update([
            'status' => 'approved'
        ]);

        return back()->with('success','User approved successfully');
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
            return back()->with('error','Cannot reject HR account');
        }

        $user->update([
            'status' => 'rejected'
        ]);

        return back()->with('success','User rejected successfully');
    }

        public function internProgress()
    {
        return view('hr.intern_progress');
    }

}