@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="section-label" style="margin-bottom:10px;">Users</div>
<div class="stat-mosaic" style="margin-bottom:24px;">
    <x-stat-card label="Total Users"  :value="$totalUsers" />
    <x-stat-card label="Interns"      :value="$totalInterns" />
    <x-stat-card label="Mentors"      :value="$totalMentors" />
    <x-stat-card label="Pending"      :value="$pendingUsers"  accent="warn" />
    <x-stat-card label="Approved"     :value="$approvedUsers" accent="accent" />
    <x-stat-card label="Rejected"     :value="$rejectedUsers" />
</div>

<div class="section-label" style="margin-bottom:10px;">Activity</div>
<div class="stat-mosaic">
    <x-stat-card label="Assigned Interns" :value="$assignedInterns" />
    <x-stat-card label="Topics"           :value="$topics" />
    <x-stat-card label="Questions"        :value="$questions" />
    <x-stat-card label="Assignments"      :value="$assignments" />
    <x-stat-card label="Submitted"        :value="$submitted" />
    <x-stat-card label="Evaluated"        :value="$evaluated" accent="accent" />
</div>
@endsection