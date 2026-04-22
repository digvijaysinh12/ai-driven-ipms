@extends('layouts.app')
@section('title', 'Attendance')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Attendance</div>
        <div class="page-meta">Login and logout are tracked automatically from the approved office network.</div>
    </div>
</div>

<div class="info-grid" style="margin-bottom: 28px;">
    <div class="info-card">
        <div class="info-card-label">Today's Status</div>
        <div class="info-card-value">
            @if($todayAttendance?->logout_time)
                Checked out
            @elseif($todayAttendance?->login_time)
                Checked in
            @else
                No record yet
            @endif
        </div>
        <div class="info-card-sub">
            @if($todayAttendance?->login_time)
                Login: {{ $todayAttendance->login_time->format('d M Y h:i A') }}
            @else
                Attendance starts automatically when you log in from office WiFi.
            @endif
        </div>
    </div>

    <div class="info-card">
        <div class="info-card-label">Tracked Time</div>
        <div class="info-card-value">{{ gmdate('H:i', $totalTrackedSeconds) }} hrs</div>
        <div class="info-card-sub">Total completed office time across all recorded sessions.</div>
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
    <div class="section-label">Attendance Policy</div>
    <p style="font-size: 14px; color: #555; line-height: 1.8; margin-bottom: 20px;">
        Login and logout are only accepted from the configured office network IPs. Each login creates an attendance
        session, and each logout closes the latest open session by saving the logout time and total worked seconds.
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

<div class="table-card" style="padding: 0; margin-top: 24px;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Login Time</th>
                <th>Logout Time</th>
                <th>Total Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentAttendances as $attendance)
                <tr>
                    <td>{{ $attendance->date->format('d M Y') }}</td>
                    <td>{{ $attendance->login_time->format('h:i A') }}</td>
                    <td>{{ $attendance->logout_time?->format('h:i A') ?? 'Open session' }}</td>
                    <td>
                        @if($attendance->total_seconds > 0)
                            {{ gmdate('H:i', $attendance->total_seconds) }} hrs
                        @else
                            --
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $attendance->logout_time ? 'badge-success' : 'badge-warning' }}">
                            {{ $attendance->logout_time ? 'Completed' : 'Active' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No attendance sessions have been recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
