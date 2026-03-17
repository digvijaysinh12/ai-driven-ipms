@extends('layouts.hr')

@section('title', 'User Approvals')

@section('content')

<style>
    .page-header {
        padding-bottom: 18px;
        border-bottom: 1px solid #e5e5e5;
        margin-bottom: 24px;
    }

    .page-title {
        font-size: 18px;
        font-weight: 500;
        letter-spacing: -0.01em;
    }

    .page-meta {
        font-family: 'DM Mono', monospace;
        font-size: 11px;
        color: #aaa;
        margin-top: 3px;
    }

    .user-list {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        overflow: hidden;
    }

    .user-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #f0f0f0;
    }

    .user-row:last-child { border-bottom: none; }

    .user-name {
        font-size: 14px;
        font-weight: 500;
        color: #1a1a1a;
    }

    .user-email {
        font-family: 'DM Mono', monospace;
        font-size: 12px;
        color: #888;
        margin-top: 2px;
    }

    .action-group {
        display: flex;
        gap: 8px;
    }

    .btn-approve {
        background: #1a1a1a;
        color: #fff;
        border: none;
        border-radius: 2px;
        padding: 7px 16px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        transition: background 0.12s;
    }

    .btn-approve:hover { background: #333; }

    .btn-reject {
        background: #fff;
        color: #1a1a1a;
        border: 1px solid #d4d4d4;
        border-radius: 2px;
        padding: 7px 16px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        transition: border-color 0.12s, color 0.12s;
    }

    .btn-reject:hover { border-color: #888; }

    .empty-state {
        padding: 40px 24px;
        text-align: center;
        font-size: 13px;
        color: #aaa;
        font-family: 'DM Mono', monospace;
    }
</style>

<div class="page-header">
    <div class="page-title">User Approvals</div>
    <div class="page-meta">Pending review</div>
</div>

<div class="user-list">
    @forelse($users as $user)
        <div class="user-row">
            <div>
                <div class="user-name">{{ $user->name }}</div>
                <div class="user-email">{{ $user->email }}</div>
            </div>
            <div class="action-group">
                <form method="POST" action="{{ route('hr.users.approve', $user->id) }}">
                    @csrf @method('PATCH')
                    <button class="btn-approve">Approve</button>
                </form>
                <form method="POST" action="{{ route('hr.users.reject', $user->id) }}">
                    @csrf @method('PATCH')
                    <button class="btn-reject">Reject</button>
                </form>
            </div>
        </div>
    @empty
        <div class="empty-state">No pending approvals.</div>
    @endforelse
</div>

@endsection