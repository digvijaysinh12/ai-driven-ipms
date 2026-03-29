<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MentorAssignment;
use Illuminate\Support\Facades\DB;

class MentorAssignmentController extends Controller
{
<<<<<<< HEAD
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
=======

    /*
    |------------------------------------
    | Assign Mentor Page
    |------------------------------------
    */

    public function index()
    {
        // Interns without active mentor
        $interns = User::whereHas('role',function($q){
            $q->where('name','intern');
        })
        ->where('status','approved')
        ->whereDoesntHave('mentorAssignments',function($q){
            $q->where('is_active',true);
        })
        ->get();


        // All approved mentors
        $mentors = User::whereHas('role',function($q){
            $q->where('name','mentor');
        })
        ->where('status','approved')
        ->get();


        return view('hr.mentor_assignments',compact('interns','mentors'));
    }


    /*
    |------------------------------------
    | Assign Mentor Logic
    |------------------------------------
    */

    public function assign(Request $request)
    {

>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        $request->validate([
            'intern_id' => 'required|exists:users,id',
            'mentor_id' => 'required|exists:users,id',
        ]);

<<<<<<< HEAD
        DB::transaction(function () use ($request) {
            // Deactivate old assignment if any
            MentorAssignment::where('intern_id', $request->intern_id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
=======

        DB::transaction(function () use ($request){

            $intern = User::findOrFail($request->intern_id);

>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26

            // deactivate old assignment
            MentorAssignment::where('intern_id',$intern->id)
                ->where('is_active',true)
                ->update(['is_active'=>false]);


            // create new assignment
            MentorAssignment::create([
<<<<<<< HEAD
                'intern_id'   => $request->intern_id,
                'mentor_id'   => $request->mentor_id,
                'assigned_by' => auth()->id(),
                'is_active'   => true,
                'assigned_at' => now(),
=======
                'intern_id'=>$intern->id,
                'mentor_id'=>$request->mentor_id,
                'assigned_by'=>auth()->id(),
                'is_active'=>true,
                'assigned_at'=>now()
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
            ]);

        });

<<<<<<< HEAD
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
=======

        return back()->with('success','Mentor assigned successfully');

    }



    /*
    |------------------------------------
    | Intern - Mentor Mapping
    |------------------------------------
    */

    public function list()
    {

        $assignments = MentorAssignment::with([
            'intern',
            'mentor'
        ])
        ->where('is_active',true)
        ->latest()
        ->get();


        return view('hr.intern_mentor_list',compact('assignments'));

    }

>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
}