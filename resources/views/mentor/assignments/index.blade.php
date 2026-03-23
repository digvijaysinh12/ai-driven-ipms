@extends('layouts.app')
@section('title', 'Assignments')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Assignments</div>
        <div class="page-meta">{{ $assignments->count() }} total</div>
    </div>
    <a href="{{ route('mentor.topics.assign') }}" class="btn-primary">+ New Assignment</a>
</div>

<div class="table-card">
    @if($assignments->isEmpty())
        <div class="empty-state">No assignments yet.<br>Publish a topic first, then assign it to an intern.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Intern</th><th>Topic</th><th>Status</th><th>Grade</th><th>Deadline</th><th>Assigned</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignments as $asgn)
                    @php
                        $isOverdue = \Carbon\Carbon::parse($asgn->deadline)->isPast()
                                     && !in_array($asgn->status, ['submitted', 'evaluated']);
                    @endphp
                    <tr>
                        <td>
                            <div class="cell-name">{{ $asgn->intern->name ?? '—' }}</div>
                            <div class="cell-mono">{{ $asgn->intern->email ?? '' }}</div>
                        </td>
                        <td style="color:#555;">{{ $asgn->topic->title ?? '—' }}</td>
                        <td><x-badge :status="$asgn->status" /></td>
                        <td>
                            @if($asgn->grade)
                                <span class="grade-pill grade-{{ $asgn->grade }}">{{ $asgn->grade }}</span>
                            @else
                                <span class="grade-pill grade-none">—</span>
                            @endif
                        </td>
                        <td class="cell-mono {{ $isOverdue ? 'cell-overdue' : '' }}">
                            {{ \Carbon\Carbon::parse($asgn->deadline)->format('d M Y') }}
                            @if($isOverdue) · overdue @endif
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