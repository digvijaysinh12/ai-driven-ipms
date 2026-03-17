<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\MentorAssignment;
use Illuminate\Support\Facades\DB;

class MentorAssignmentController extends Controller
{

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

        $request->validate([
            'intern_id' => 'required|exists:users,id',
            'mentor_id' => 'required|exists:users,id'
        ]);


        DB::transaction(function () use ($request){

            $intern = User::findOrFail($request->intern_id);


            // deactivate old assignment
            MentorAssignment::where('intern_id',$intern->id)
                ->where('is_active',true)
                ->update(['is_active'=>false]);


            // create new assignment
            MentorAssignment::create([
                'intern_id'=>$intern->id,
                'mentor_id'=>$request->mentor_id,
                'assigned_by'=>auth()->id(),
                'is_active'=>true,
                'assigned_at'=>now()
            ]);

        });


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

}