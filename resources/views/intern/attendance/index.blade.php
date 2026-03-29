@extends('layouts.app')
@section('title', 'Attendance')

<<<<<<< HEAD
=======
@push('scripts')
<script src="{{ asset('js/attendance.js') }}"></script>
@endpush

>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Attendance</div>
<<<<<<< HEAD
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
=======
        <div class="page-meta">{{ now()->format('F Y') }}</div>
    </div>
    {{-- Summary stats --}}
    <div style="display:flex;gap:1px;background:#e5e5e5;border:1px solid #e5e5e5;
                border-radius:2px;overflow:hidden;">
        <div class="stat-cell" style="padding:14px 20px;">
            <div class="stat-label">Present</div>
            <div class="stat-value accent" style="font-size:22px;">{{ $presentDays }}</div>
        </div>
        <div class="stat-cell" style="padding:14px 20px;">
            <div class="stat-label">Absent</div>
            <div class="stat-value" style="font-size:22px;">{{ $absentDays }}</div>
        </div>
        <div class="stat-cell" style="padding:14px 20px;">
            <div class="stat-label">Half Day</div>
            <div class="stat-value warn" style="font-size:22px;">{{ $halfDays }}</div>
        </div>
        <div class="stat-cell" style="padding:14px 20px;">
            <div class="stat-label">Attendance %</div>
            <div class="stat-value" style="font-size:22px;">{{ $attendancePercent }}%</div>
        </div>
    </div>
</div>

{{-- ── Check-in card ── --}}
@if($isOfficeNetwork)
    <div class="attendance-checkin-card">
        <div>
            @if($todayRecord && !$todayRecord->check_out_at)
                <div class="checkin-status">
                    Checked in at {{ \Carbon\Carbon::parse($todayRecord->check_in_at)->format('h:i A') }}
                </div>
                <div class="checkin-time" id="checkin-timer"
                     data-checkin-time="{{ $todayRecord->check_in_at }}">
                    00:00:00
                </div>
            @elseif($todayRecord && $todayRecord->check_out_at)
                <div class="checkin-status">
                    Today: {{ \Carbon\Carbon::parse($todayRecord->check_in_at)->format('h:i A') }}
                    → {{ \Carbon\Carbon::parse($todayRecord->check_out_at)->format('h:i A') }}
                </div>
                <div style="font-family:'DM Mono',monospace;font-size:14px;
                            color:#1a6a1a;margin-top:4px;">✓ Checked out</div>
            @else
                <div class="checkin-status">
                    You are on the office network. You can check in now.
                </div>
            @endif
        </div>

        <div>
            @if(!$todayRecord)
                <button id="btn-checkin" class="btn-primary">Check In</button>
            @elseif(!$todayRecord->check_out_at)
                <button id="btn-checkout" class="btn-outline">Check Out</button>
            @endif
        </div>
    </div>

@else
    <div class="network-warning">
        You are not on the office network.
        Check-in is only available from the office WiFi ({{ config('attendance.office_ip_ranges')[0] ?? '192.168.1.x' }}).
    </div>
@endif

{{-- ── Monthly calendar ── --}}
<div class="attendance-calendar">
    <div class="calendar-header">
        @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)
            <div class="calendar-day-name">{{ $day }}</div>
        @endforeach
    </div>

    <div class="calendar-grid">
        @foreach($calendarDays as $day)
            <div class="calendar-cell
                {{ !$day['current_month'] ? 'other-month' : '' }}
                {{ $day['is_today'] ? 'today' : '' }}">
                <div class="cell-date {{ $day['is_today'] ? 'today' : '' }}">
                    {{ $day['date'] }}
                </div>
                @if($day['status'])
                    <div class="cell-status cell-{{ $day['status'] }}">
                        {{ $day['status'] === 'half_day' ? 'half' : $day['status'] }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
