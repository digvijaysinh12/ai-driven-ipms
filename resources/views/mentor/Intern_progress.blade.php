@extends('layouts.mentor')
@section('title', 'Intern Progress')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');
    .page-header {
        display: flex; justify-content: space-between;
        align-items: flex-start; padding-bottom: 20px;
        border-bottom: 1px solid #e5e5e5; margin-bottom: 28px;
    }
    .page-title { font-size: 20px; font-weight: 500; letter-spacing: -0.02em; }
    .page-meta  { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; margin-top: 4px; }
    .back-link  {
        font-family: 'DM Mono', monospace; font-size: 11px;
        letter-spacing: 0.08em; text-transform: uppercase;
        color: #888; text-decoration: none;
    }
    .back-link:hover { color: #1a1a1a; }

    .stat-mosaic {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 1px; background: #e5e5e5;
        border: 1px solid #e5e5e5; border-radius: 2px;
        overflow: hidden; margin-bottom: 28px;
    }
    .stat-cell { background: #fff; padding: 20px 22px; }
    .stat-label { font-family: 'DM Mono', monospace; font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; color: #aaa; margin-bottom: 6px; }
    .stat-value { font-family: 'DM Mono', monospace; font-size: 28px; font-weight: 400; color: #1a1a1a; line-height: 1; }
    .stat-value.muted { color: #ccc; }

    .section-label { font-family: 'DM Mono', monospace; font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: #aaa; margin-bottom: 14px; margin-top: 28px; }

    .table-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 2px; overflow: hidden; }
    .data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .data-table th { text-align: left; padding: 12px 16px; font-size: 10px; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; color: #888; font-family: 'DM Mono', monospace; border-bottom: 1px solid #e5e5e5; }
    .data-table td { padding: 13px 16px; border-bottom: 1px solid #f0f0f0; color: #1a1a1a; vertical-align: middle; }
    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover td { background: #fafafa; }

    .badge { display: inline-block; padding: 2px 8px; border-radius: 2px; font-size: 10px; font-family: 'DM Mono', monospace; letter-spacing: 0.05em; text-transform: uppercase; }
    .badge-assigned   { background: #f0f0f0; color: #888; }
    .badge-submitted  { background: #e8f0f5; color: #1a5092; }
    .badge-evaluated  { background: #eaf5e8; color: #1a6a1a; }
    .badge-ai_evaluated { background: #e8f0f5; color: #1a5092; }
    .badge-reviewed   { background: #eaf5e8; color: #1a6a1a; }

    .grade-pill {
        font-family: 'DM Mono', monospace; font-size: 18px; font-weight: 500;
        width: 32px; height: 32px; border-radius: 2px;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .grade-A { background: #eafaea; color: #1a7a1a; }
    .grade-B { background: #e8eeff; color: #1a3a8a; }
    .grade-C { background: #fffbe8; color: #7a6000; }
    .grade-D { background: #fff0e8; color: #7a3010; }
    .grade-E { background: #fff0f0; color: #8a0000; }
    .grade-none { background: #f0f0f0; color: #ccc; }

    .mono { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; }
    .q-text { max-width: 360px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .empty-row td { padding: 32px 16px; text-align: center; font-family: 'DM Mono', monospace; font-size: 12px; color: #ccc; }
</style>

<div class="page-header">
    <div>
        <div class="page-title">{{ $intern->name }}</div>
        <div class="page-meta">{{ $intern->email }} · Progress Overview</div>
    </div>
    <a href="{{ route('user.mentor.interns') }}" class="back-link">← All Interns</a>
</div>

{{-- Stats --}}
<div class="stat-mosaic">
    <div class="stat-cell">
        <div class="stat-label">Assignments</div>
        <div class="stat-value">{{ $assignments->count() }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Submitted</div>
        <div class="stat-value">{{ $totalSubmissions }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Evaluated</div>
        <div class="stat-value">{{ $evaluatedCount }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Avg Score</div>
        <div class="stat-value {{ $avgScore ? '' : 'muted' }}">{{ $avgScore ?? '—' }}</div>
    </div>
</div>

{{-- Topic Assignments --}}
<div class="section-label">Topic Assignments</div>
<div class="table-card" style="margin-bottom:0;">
    <table class="data-table">
        <thead><tr>
            <th>Topic</th><th>Status</th><th>Grade</th><th>Deadline</th><th>Feedback</th>
        </tr></thead>
        <tbody>
        @forelse($assignments as $asgn)
            <tr>
                <td style="font-weight:500;">{{ $asgn->topic->title ?? '—' }}</td>
                <td><span class="badge badge-{{ $asgn->status }}">{{ ucfirst($asgn->status) }}</span></td>
                <td>
                    @if($asgn->grade)
                        <span class="grade-pill grade-{{ $asgn->grade }}">{{ $asgn->grade }}</span>
                    @else
                        <span class="grade-pill grade-none">—</span>
                    @endif
                </td>
                <td class="mono">{{ \Carbon\Carbon::parse($asgn->deadline)->format('d M Y') }}</td>
                <td style="font-size:12px;color:#666;max-width:260px;">
                    {{ $asgn->feedback ? Str::limit($asgn->feedback, 80) : '—' }}
                </td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="5">No topic assignments</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Submissions --}}
<div class="section-label">All Submissions</div>
<div class="table-card">
    <table class="data-table">
        <thead><tr>
            <th>#</th><th>Question</th><th>Type</th><th>Status</th><th>Date</th>
        </tr></thead>
        <tbody>
        @forelse($submissions as $i => $sub)
            <tr>
                <td class="mono">{{ $i + 1 }}</td>
                <td><div class="q-text">{{ $sub->question->problem_statement ?? '—' }}</div></td>
                <td class="mono">{{ str_replace('_',' ', $sub->question->type ?? '') }}</td>
                <td><span class="badge badge-{{ $sub->status }}">{{ str_replace('_',' ', ucfirst($sub->status)) }}</span></td>
                <td class="mono">{{ $sub->created_at->format('d M Y') }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="5">No submissions yet</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
