@extends('layouts.intern')

@section('title', 'Dashboard')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1px;
        background: #e5e5e5;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .stat-card { background: #fff; padding: 22px 24px; }

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
        line-height: 1;
    }

    .section-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #888;
        margin-bottom: 14px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1px;
        background: #e5e5e5;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        overflow: hidden;
    }

    .info-card { background: #fff; padding: 22px 24px; }

    .info-card-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #aaa;
        margin-bottom: 8px;
    }

    .info-card-value {
        font-size: 14px;
        font-weight: 500;
        color: #1a1a1a;
    }

    .info-card-sub {
        font-family: 'DM Mono', monospace;
        font-size: 12px;
        color: #888;
        margin-top: 3px;
    }

    .badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 2px;
        font-size: 11px;
        font-family: 'DM Mono', monospace;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        font-weight: 500;
    }

    .badge-assigned  { background: #e8f0f5; color: #1a5092; }
    .badge-submitted { background: #f5f0e8; color: #92681a; }
    .badge-completed { background: #eaf5e8; color: #1a6a1a; }
</style>

{{-- Stats Row --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Assigned Topics</div>
        <div class="stat-value">{{ $topicCount }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Questions</div>
        <div class="stat-value">{{ $questionCount }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Submitted</div>
        <div class="stat-value">{{ $submittedCount }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending</div>
        <div class="stat-value">{{ $pendingCount }}</div>
    </div>
</div>

{{-- Mentor & Topic Info --}}
<div class="section-label">Assignment Info</div>

<div class="info-grid">
    <div class="info-card">
        <div class="info-card-label">Mentor</div>
        @if($mentor)
            <div class="info-card-value">{{ $mentor->name }}</div>
            <div class="info-card-sub">{{ $mentor->email }}</div>
        @else
            <div class="info-card-sub">Not assigned yet</div>
        @endif
    </div>

    <div class="info-card">
        <div class="info-card-label">Current Topic</div>
        @if($currentAssignment)
            <div class="info-card-value">{{ $currentAssignment->topic->title }}</div>
            <div class="info-card-sub">
                Deadline: {{ \Carbon\Carbon::parse($currentAssignment->deadline)->format('d M Y') }}
                &nbsp;·&nbsp;
                <span class="badge badge-{{ $currentAssignment->status }}">
                    {{ ucfirst($currentAssignment->status) }}
                </span>
            </div>
        @else
            <div class="info-card-sub">No topic assigned yet</div>
        @endif
    </div>
</div>

@endsection