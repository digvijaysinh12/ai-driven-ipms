@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="stat-mosaic" style="margin-bottom:28px;">
    <x-stat-card label="Assigned Topics" :value="$topicCount" />
    <x-stat-card label="Total Questions" :value="$questionCount" />
    <x-stat-card label="Submitted"       :value="$submittedCount" accent="accent" />
    <x-stat-card label="Pending"         :value="$pendingCount"   accent="warn" />
</div>

<div class="section-label" style="margin-bottom:14px;">Assignment Info</div>
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
                Due {{ \Carbon\Carbon::parse($currentAssignment->deadline)->format('d M Y') }}
                &nbsp;·&nbsp;
                <x-badge :status="$currentAssignment->status" />
            </div>
        @else
            <div class="info-card-sub">No topic assigned yet</div>
        @endif
    </div>
</div>
@endsection