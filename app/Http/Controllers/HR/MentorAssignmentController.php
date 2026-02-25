<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\MentorAssignment;
use Illuminate\Support\Facades\DB;

class MentorAssignmentController extends Controller
{
    public function index()
    {
        // Only approved intern without active mentor
        $interns = User::whereHas('role',function($q){
            $q->where('name','intern');
        })
        ->where('status','approved')
        ->whereDoesntHave('mentorAssignments',function($q){
            $q->where('is_active',true);
        })
        ->get();

        // All mentors
        $mentors = User::whereHas('role',function($q){
            $q->where('name','mentor');
        })
        ->where('status','approved')
        ->get();
        return view('hr.mentor_assignments',compact('interns','mentors'));
    }
    public function assign(Request $request){
        $request->validate([
            'intern_id' => 'required|exists:users,id',
            'mentor_id' => 'required|exists:users,id'
        ]);

        DB::transaction(function () use ($request){
            $intern = User::findOrFail($request->intern_id);

            // Deactivate old assignment
            MentorAssignment::where('intern_id',$intern->id)
                                ->where('is_active',true)
                                ->update(['is_active' =>false]);

            // Create new assignment
            MentorAssignment::create([
                'intern_id' => $intern->id,
                'mentor_id' => $request->mentor_id,
                'assigned_by' => auth()->id(),
                'is_active' => true,
                'assigned_at' => now()
            ]);
        });

        return back()->with('success','Mentor assigned successfully');
    }
}
