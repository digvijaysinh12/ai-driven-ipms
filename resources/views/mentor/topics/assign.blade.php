@extends('layouts.mentor')
@section('title', 'Assignments')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; padding-bottom: 20px; border-bottom: 1px solid #e5e5e5; margin-bottom: 24px; }
    .page-title  { font-size: 20px; font-weight: 500; letter-spacing: -0.02em; }
    .page-meta   { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; margin-top: 4px; }
    .btn-create  { background: #1a1a1a; color: #fff; padding: 9px 18px; border-radius: 2px; font-size: 13px; font-weight: 500; text-decoration: none; transition: background 0.12s; }
    .btn-create:hover { background: #333; }

    .table-card  { background: #fff; border: 1px solid #e5e5e5; border-radius: 2px; overflow: hidden; }
    .data-table  { width: 100%; border-collapse: collapse; font-size: 13px; }
    .data-table th { text-align: left; padding: 12px 16px; font-size: 10px; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; color: #888; font-family: 'DM Mono', monospace; border-bottom: 2px solid #e5e5e5; }
    .data-table td { padding: 14px 16px; border-bottom: 1px solid #f0f0f0; color: #1a1a1a; vertical-align: middle; }
    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover td { background: #fafafa; }

    .badge { display: inline-block; padding: 2px 10px; border-radius: 2px; font-size: 10px; font-family: 'DM Mono', monospace; letter-spacing: 0.05em; text-transform: uppercase; }
    .badge-assigned   { background: #f0f0f0; color: #888; }
    .badge-in_progress{ background: #e8f0f5; color: #1a5092; }
    .badge-submitted  { background: #fff8e8; color: #8a6000; }
    .badge-evaluated  { background: #eaf5e8; color: #1a6a1a; }

    .grade-pill { font-family: 'DM Mono', monospace; font-size: 16px; font-weight: 500; width: 28px; height: 28px; border-radius: 2px; display: inline-flex; align-items: center; justify-content: center; }
    .grade-A { background: #eafaea; color: #1a7a1a; }
    .grade-B { background: #e8eeff; color: #1a3a8a; }
    .grade-C { background: #fffbe8; color: #7a6000; }
    .grade-D { background: #fff0e8; color: #7a3010; }
    .grade-E { background: #fff0f0; color: #8a0000; }
    .grade-none { background: #f0f0f0; color: #ccc; }

    .mono { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; }
    .tlink { font-size: 13px; color: #555; text-decoration: underline; text-underline-offset: 2px; }
    .tlink:hover { color: #1a1a1a; }
    .overdue { color: #c0392b !important; }
    .empty-state { padding: 56px; text-align: center; font-family: 'DM Mono', monospace; font-size: 13px; color: #aaa; }
</style>

<div class="page-header">
    <div>
        <div class="page-title">Assignments</div>
        <div class="page-meta">{{ $assignments->count() }} total</div>
    </div>
    <a href="{{ route('user.mentor.tasks.create') }}" class="btn-create">+ New Assignment</a>
</div>

<div class="table-card">
    @if($assignments->isEmpty())
        <div class="empty-state">No assignments yet.<br>Publish a topic first, then assign it to an intern.</div>
    @else
    <table class="data-table">
        <thead><tr>
            <th>Intern</th><th>Topic</th><th>Status</th><th>Grade</th><th>Deadline</th><th>Assigned</th>
        </tr></thead>
        <tbody>
        @foreach($assignments as $asgn)
        @php $isOverdue = \Carbon\Carbon::parse($asgn->deadline)->isPast() && !in_array($asgn->status, ['submitted','evaluated']); @endphp
        <tr>
            <td>
                <div style="font-weight:500;">{{ $asgn->intern->name ?? '—' }}</div>
                <div class="mono">{{ $asgn->intern->email ?? '' }}</div>
            </td>
            <td style="color:#555;">{{ $asgn->topic->title ?? '—' }}</td>
            <td><span class="badge badge-{{ $asgn->status }}">{{ ucfirst(str_replace('_',' ',$asgn->status)) }}</span></td>
            <td>
                @if($asgn->grade)
                    <span class="grade-pill grade-{{ $asgn->grade }}">{{ $asgn->grade }}</span>
                @else
                    <span class="grade-pill grade-none">—</span>
                @endif
            </td>
            <td class="mono {{ $isOverdue ? 'overdue' : '' }}">
                {{ \Carbon\Carbon::parse($asgn->deadline)->format('d M Y') }}
                @if($isOverdue) · overdue @endif
            </td>
            <td class="mono">{{ \Carbon\Carbon::parse($asgn->assigned_at)->format('d M Y') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
