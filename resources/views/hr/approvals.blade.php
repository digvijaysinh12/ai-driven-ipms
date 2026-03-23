@extends('layouts.app')
@section('title', 'User Approvals')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">User Approvals</div>
        <div class="page-meta">Pending review</div>
    </div>
</div>

<div class="table-card">
    @forelse($users as $user)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #f0f0f0;">
            <div>
                <div class="cell-name" style="font-size:14px;">{{ $user->name }}</div>
                <div class="cell-mono" style="margin-top:2px;">{{ $user->email }}</div>
            </div>
            <div class="action-group">
                <form method="POST" action="{{ route('hr.users.approve', $user->id) }}">
                    @csrf @method('PATCH')
                    <button class="btn-primary btn-sm">Approve</button>
                </form>
                <form method="POST" action="{{ route('hr.users.reject', $user->id) }}">
                    @csrf @method('PATCH')
                    <button class="btn-outline btn-sm">Reject</button>
                </form>
            </div>
        </div>
    @empty
        <div class="empty-state">No pending approvals.</div>
    @endforelse
</div>
@endsection