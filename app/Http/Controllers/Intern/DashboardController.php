<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        $assignment = auth()->user()
            ->currentMentorAssignment()   // <-- () important
            ->with('mentor.technology')
            ->first();
        return view('intern.dashboard',compact('assignment'));
    }
}
