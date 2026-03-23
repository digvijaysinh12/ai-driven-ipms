@extends('layouts.app')
@section('title', 'Intern Progress — ' . $intern->name)

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ $intern->name }}</div>
        <div class="page-meta">{{ $intern->email }}</div>
    </div>
    <a href="{{ route('hr.intern.mentor.list') }}" class="back-link">← Intern–Mentor Map</a>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;margin-bottom:28px;">

    <div class="stat-card">
        <div class="stat-label">Mentor</div>
        <div class="stat-value" style="font-size:15px;">
            {{ $mentorAssignment?->mentor?->name ?? '—' }}
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Topics Assigned</div>
        <div class="stat-value">{{ $topicAssignments->count() }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Submissions</div>
        <div class="stat-value">{{ $totalSubmissions }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Reviewed</div>
        <div class="stat-value">{{ $reviewedCount }}</div>
    </div>

</div>

{{-- Topic Assignments Table --}}
<div class="table-card">
    @if($topicAssignments->isEmpty())
        <div class="empty-state">No topic assignments yet.</div>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th>Topic</th>
                <th>Status</th>
                <th>Grade</th>
                <th>Deadline</th>
                <th>Assigned</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topicAssignments as $asgn)
            @php
                $isOverdue = \Carbon\Carbon::parse($asgn->deadline)->isPast()
                    && !in_array($asgn->status, ['submitted', 'evaluated']);
            @endphp
            <tr>
                <td class="cell-name">{{ $asgn->topic->title ?? '—' }}</td>
                <td>
                    <span class="badge badge-{{ $asgn->status }}">
                        {{ ucfirst(str_replace('_', ' ', $asgn->status)) }}
                    </span>
                </td>
                <td>
                    @if($asgn->grade)
                        <span class="grade-pill grade-{{ $asgn->grade }}">{{ $asgn->grade }}</span>
                    @else
                        <span class="cell-mono">—</span>
                    @endif
                </td>
                <td class="cell-mono {{ $isOverdue ? 'text-danger' : '' }}">
                    {{ \Carbon\Carbon::parse($asgn->deadline)->format('d M Y') }}
                    @if($isOverdue) <span style="color:#c0392b;">· overdue</span> @endif
                </td>
                <td class="cell-mono">
                    {{ \Carbon\Carbon::parse($asgn->assigned_at)->format('d M Y') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
