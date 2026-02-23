<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ApprovalController extends Controller
{
    public function index(){
        $pendingUsers = User::where('status','pending')->get();
        return view('hr.approvals', compact('pendingUsers'));
    }
}
