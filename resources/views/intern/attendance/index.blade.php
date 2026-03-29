@extends('layouts.app')
@section('title', 'Attendance')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Attendance</div>
        <div class="page-meta">Backend-aligned status for this project</div>
    </div>
</div>

<div class="info-grid" style="margin-bottom: 28px;">
    <div class="info-card">
        <div class="info-card-label">Tracking Status</div>
        <div class="info-card-value">Not configured</div>
        <div class="info-card-sub">No attendance table or check-in endpoints are present in the backend yet.</div>
    </div>

    <div class="info-card">
        <div class="info-card-label">Current Mentor</div>
        @if($mentorAssignment?->mentor)
            <div class="info-card-value">{{ $mentorAssignment->mentor->name }}</div>
            <div class="info-card-sub">{{ $mentorAssignment->mentor->email }}</div>
        @else
            <div class="info-card-value">Not assigned</div>
            <div class="info-card-sub">A mentor assignment is required before attendance can be tied to a supervisor.</div>
        @endif
    </div>
</div>

<div class="table-card" style="padding: 28px;">
    <div class="section-label">Current Backend Scope</div>
    <p style="font-size: 14px; color: #555; line-height: 1.8; margin-bottom: 20px;">
        This project currently supports mentor assignment, topic assignment, exercises, submissions, AI evaluation,
        and mentor review. Attendance records, check-in/check-out actions, and office-network validation have not been
        implemented yet, so this page now reflects that state instead of showing broken controls.
    </p>

    @if($currentAssignment?->topic)
        <div class="section-label">Current Work Context</div>
        <div class="info-grid">
            <div class="info-card">
                <div class="info-card-label">Topic</div>
                <div class="info-card-value">{{ $currentAssignment->topic->title }}</div>
                <div class="info-card-sub">{{ $currentAssignment->topic->description }}</div>
            </div>
            <div class="info-card">
                <div class="info-card-label">Deadline</div>
                <div class="info-card-value">{{ optional($currentAssignment->deadline)->format('d M Y') ?? 'Not set' }}</div>
                <div class="info-card-sub">Status: {{ ucfirst(str_replace('_', ' ', $currentAssignment->status)) }}</div>
            </div>
        </div>
    @endif
</div>
@endsection
