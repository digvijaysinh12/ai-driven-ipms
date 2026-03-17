@extends('layouts.intern')

@section('title', 'My Submissions')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

    .page-header {
        padding-bottom: 20px;
        border-bottom: 1px solid #e5e5e5;
        margin-bottom: 28px;
    }

    .page-title { font-size: 20px; font-weight: 500; letter-spacing: -0.02em; }
    .page-meta  { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; margin-top: 4px; }

    /* ── Assignment result cards ── */
    .section-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #aaa;
        margin-bottom: 14px;
    }

    .assignment-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1px;
        background: #e5e5e5;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 32px;
    }

    .assignment-card {
        background: #fff;
        padding: 22px 24px;
    }

    .assignment-topic {
        font-size: 14px;
        font-weight: 500;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .assignment-meta {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        color: #aaa;
        letter-spacing: 0.04em;
        margin-bottom: 14px;
    }

    .grade-display {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .grade-big {
        font-family: 'DM Mono', monospace;
        font-size: 40px;
        font-weight: 500;
        line-height: 1;
        letter-spacing: -0.03em;
    }

    .grade-A { color: #1a7a1a; }
    .grade-B { color: #1a3a8a; }
    .grade-C { color: #7a6000; }
    .grade-D { color: #7a3010; }
    .grade-E { color: #8a0000; }
    .grade-pending { color: #ccc; }

    .grade-info { flex: 1; }

    .grade-label-text {
        font-size: 13px;
        font-weight: 500;
        color: #1a1a1a;
    }

    .grade-feedback-snippet {
        font-size: 12px;
        color: #777;
        margin-top: 3px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 2px;
        font-size: 10px;
        font-family: 'DM Mono', monospace;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .badge-assigned   { background: #f0f0f0; color: #888; }
    .badge-submitted  { background: #e8f0f5; color: #1a5092; }
    .badge-evaluated  { background: #eaf5e8; color: #1a6a1a; }

    /* ── Individual answers table ── */
    .table-wrapper {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        overflow: hidden;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .data-table thead tr { border-bottom: 2px solid #e5e5e5; }

    .data-table th {
        text-align: left;
        padding: 12px 16px;
        font-size: 10px;
        font-weight: 500;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #888;
        font-family: 'DM Mono', monospace;
    }

    .data-table td {
        padding: 13px 16px;
        border-bottom: 1px solid #f0f0f0;
        color: #333;
        vertical-align: middle;
    }

    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover td { background: #fafafa; }

    .q-text {
        max-width: 380px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .empty-state {
        padding: 56px;
        text-align: center;
        font-family: 'DM Mono', monospace;
        font-size: 13px;
        color: #aaa;
    }
</style>

<div class="page-header">
    <div class="page-title">My Submissions</div>
    <div class="page-meta">{{ $totalSubmissions }} answers saved</div>
</div>

{{-- Exercise grades ── --}}
<div class="section-label">Exercise Results</div>

@if($assignments->isEmpty())
    <p style="font-family:'DM Mono',monospace;font-size:12px;color:#aaa;margin-bottom:28px;">No exercises assigned yet.</p>
@else
    <div class="assignment-grid">
        @foreach($assignments as $asgn)
        @php
            $gradeLabels = ['A'=>'Excellent','B'=>'Good','C'=>'Average','D'=>'Below Average','E'=>'Needs Improvement'];
        @endphp
        <div class="assignment-card">
            <div class="assignment-topic">{{ $asgn->topic->title ?? '—' }}</div>
            <div class="assignment-meta">
                Assigned {{ \Carbon\Carbon::parse($asgn->assigned_at)->format('d M Y') }}
                &nbsp;·&nbsp;
                <span class="badge badge-{{ $asgn->status }}">{{ ucfirst($asgn->status) }}</span>
            </div>

            <div class="grade-display">
                @if($asgn->grade)
                    <div class="grade-big grade-{{ $asgn->grade }}">{{ $asgn->grade }}</div>
                    <div class="grade-info">
                        <div class="grade-label-text">{{ $gradeLabels[$asgn->grade] ?? '' }}</div>
                        @if($asgn->feedback)
                            <div class="grade-feedback-snippet">{{ $asgn->feedback }}</div>
                        @endif
                    </div>
                @elseif($asgn->status === 'submitted')
                    <div class="grade-big grade-pending">…</div>
                    <div class="grade-info">
                        <div class="grade-label-text" style="color:#aaa;">Evaluation in progress</div>
                    </div>
                @else
                    <div class="grade-big grade-pending">—</div>
                    <div class="grade-info">
                        <div class="grade-label-text" style="color:#aaa;">Not submitted yet</div>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
@endif

{{-- Individual answers ── --}}
<div class="section-label">All Saved Answers</div>

<div class="table-wrapper">
    @if($submissions->isEmpty())
        <div class="empty-state">No answers saved yet. Open an exercise from My Topic to start.</div>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Question</th>
                <th>Type</th>
                <th>Status</th>
                <th>Saved</th>
            </tr>
        </thead>
        <tbody>
            @foreach($submissions as $i => $sub)
            <tr>
                <td style="font-family:'DM Mono',monospace;font-size:11px;color:#aaa;">{{ $i+1 }}</td>
                <td><div class="q-text">{{ $sub->question->problem_statement ?? '—' }}</div></td>
                <td style="font-family:'DM Mono',monospace;font-size:10px;color:#aaa;text-transform:uppercase;">
                    {{ str_replace('_',' ', $sub->question->type ?? '') }}
                </td>
                <td>
                    <span class="badge badge-{{ $sub->status }}">
                        {{ str_replace('_',' ', ucfirst($sub->status)) }}
                    </span>
                </td>
                <td style="font-family:'DM Mono',monospace;font-size:11px;color:#aaa;">
                    {{ $sub->created_at->format('d M Y') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection