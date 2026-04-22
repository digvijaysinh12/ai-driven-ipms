<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $pendingUsers = User::with('role')
            ->where('status', 'pending')
            ->whereNotNull('email_verified_at')
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['intern', 'mentor']))
            ->latest()
            ->paginate(10); // ✅ FIX pagination

        return view('hr.approvals', compact('pendingUsers')); // ✅ FIX variable name
    }

    public function approve($id)
    {
        $user = User::with('role')->findOrFail($id);

        if (optional($user->role)->name === 'hr') {
            return back()->with('error', 'Cannot approve HR account.');
        }

        if (! $user->email_verified_at) {
            return back()->with('error', 'User email not verified yet.');
        }

        $user->update(['status' => 'approved']);

        return back()->with('success', 'User approved successfully.');
    }

    public function reject($id)
    {
        $user = User::with('role')->findOrFail($id);

        if (optional($user->role)->name === 'hr') {
            return back()->with('error', 'Cannot reject HR account.');
        }

        $user->update(['status' => 'rejected']);

        return back()->with('success', 'User rejected successfully.');
    }
}
