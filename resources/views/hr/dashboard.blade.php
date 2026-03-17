@extends('layouts.hr')

@section('title', 'Dashboard')

@section('content')

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1px;
        background: #e5e5e5;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 1px;
    }

    .stat-card {
        background: #fff;
        padding: 20px 22px;
    }

    .stat-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #888;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 500;
        letter-spacing: -0.03em;
        color: #1a1a1a;
        font-family: 'DM Sans', sans-serif;
        line-height: 1;
    }

    .section-header {
        margin-bottom: 14px;
    }

    .section-title {
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #888;
        font-family: 'DM Mono', monospace;
    }
</style>

<div class="section-header">
    <div class="section-title">Users</div>
</div>

<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-label">Total Users</div>
        <div class="stat-value">{{ $totalUsers }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Interns</div>
        <div class="stat-value">{{ $totalInterns }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Mentors</div>
        <div class="stat-value">{{ $totalMentors }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending</div>
        <div class="stat-value">{{ $pendingUsers }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Approved</div>
        <div class="stat-value">{{ $approvedUsers }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Rejected</div>
        <div class="stat-value">{{ $rejectedUsers }}</div>
    </div>
</div>

<div class="section-header">
    <div class="section-title">Activity</div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Assigned Interns</div>
        <div class="stat-value">{{ $assignedInterns }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Topics</div>
        <div class="stat-value">{{ $topics }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Questions</div>
        <div class="stat-value">{{ $questions }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Assignments</div>
        <div class="stat-value">{{ $assignments }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Submitted</div>
        <div class="stat-value">{{ $submitted }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Evaluated</div>
        <div class="stat-value">{{ $evaluated }}</div>
    </div>
</div>

@endsection