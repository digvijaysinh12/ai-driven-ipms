<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * HR Dashboard
     * Show only pending Intern & Mentor users
     */
    public function index()
    {
        $users = User::with('role')
            ->where('status', 'pending')
            ->whereHas('role', function ($query) {
                $query->whereIn('name', ['intern', 'mentor']);
            })
            ->latest()
            ->get();

        return view('hr.dashboard', compact('users'));
    }

    /**
     * Show all users categorized (Optional page)
     */
    public function users()
    {
        $pendingUsers = User::with('role')
            ->where('status', 'pending')
            ->get();

        $approvedUsers = User::with('role')
            ->where('status', 'approved')
            ->get();

        $rejectedUsers = User::with('role')
            ->where('status', 'rejected')
            ->get();

        return view('hr.users', compact(
            'pendingUsers',
            'approvedUsers',
            'rejectedUsers'
        ));
    }

    /**
     * Approve user
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);

        // Extra safety: HR cannot approve HR accounts
        if ($user->role->name === 'hr') {
            return redirect()->back()->with('error', 'Cannot approve HR account.');
        }

        $user->update([
            'status' => 'approved'
        ]);

        return redirect()->back()->with('success', 'User approved successfully.');
    }

    /**
     * Reject user
     */
    public function reject($id)
    {
        $user = User::findOrFail($id);

        if ($user->role->name === 'hr') {
            return redirect()->back()->with('error', 'Cannot reject HR account.');
        }

        $user->update([
            'status' => 'rejected'
        ]);

        return redirect()->back()->with('success', 'User rejected successfully.');
    }
}