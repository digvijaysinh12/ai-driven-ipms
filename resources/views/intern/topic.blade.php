@extends('layouts.intern')

@section('title', 'My Topic')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

    .page-header {
        padding-bottom: 20px;
        border-bottom: 1px solid #e5e5e5;
        margin-bottom: 28px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .page-title { font-size: 20px; font-weight: 500; letter-spacing: -0.02em; }

    .page-meta {
        font-family: 'DM Mono', monospace;
        font-size: 11px; color: #aaa;
        margin-top: 4px; letter-spacing: 0.04em;
    }

    .deadline-pill {
        font-family: 'DM Mono', monospace;
        font-size: 11px; color: #555;
        background: #f0f0f0;
        padding: 6px 14px;
        border-radius: 2px;
        white-space: nowrap;
    }

    .deadline-pill.overdue { background: #fdf0f0; color: #c0392b; }

    /* ── Module grid ── */
    .module-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1px;
        background: #e5e5e5;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        overflow: hidden;
    }

    .module-card {
        background: #fff;
        padding: 24px 22px 20px;
        display: flex;
        flex-direction: column;
        gap: 0;
        transition: background 0.12s;
        position: relative;
    }

    .module-card:hover { background: #fafafa; }

    /* completed card */
    .module-card.done { background: #f9fdf9; }
    .module-card.done:hover { background: #f4fcf4; }

    .module-type {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #aaa;
        margin-bottom: 10px;
    }

    .module-count {
        font-family: 'DM Mono', monospace;
        font-size: 32px;
        font-weight: 400;
        color: #1a1a1a;
        line-height: 1;
        margin-bottom: 2px;
    }

    .module-count-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        color: #bbb;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    /* progress bar */
    .module-progress {
        margin: 14px 0 16px;
        height: 2px;
        background: #ebebeb;
        border-radius: 1px;
        overflow: hidden;
    }

    .module-progress-fill {
        height: 100%;
        background: #1a1a1a;
        border-radius: 1px;
        transition: width 0.3s ease;
    }

    .module-progress-fill.full { background: #2ecc71; }

    .module-progress-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        color: #aaa;
        margin-top: -10px;
        margin-bottom: 14px;
    }

    /* start button */
    .btn-start {
        display: block;
        text-align: center;
        background: #1a1a1a;
        color: #fff;
        text-decoration: none;
        padding: 9px 0;
        border-radius: 2px;
        font-size: 12px;
        font-weight: 500;
        font-family: 'DM Sans', sans-serif;
        transition: background 0.12s;
    }

    .btn-start:hover { background: #333; }

    .btn-start.done-btn {
        background: #fff;
        color: #2ecc71;
        border: 1px solid #2ecc71;
    }

    .btn-start.done-btn:hover { background: #f4fcf4; }

    /* status badge */
    .module-status {
        position: absolute;
        top: 14px; right: 14px;
        font-family: 'DM Mono', monospace;
        font-size: 9px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 2px 8px;
        border-radius: 2px;
    }

    .status-done    { background: #eafaea; color: #1a7a1a; }
    .status-started { background: #fff8e8; color: #8a6a00; }
    .status-new     { background: #f0f0f0; color: #999; }

    /* ── Grade result card ── */
    .grade-result-card {
        margin-top: 24px;
        border: 1px solid;
        border-radius: 2px;
        padding: 24px 28px;
        display: flex;
        gap: 28px;
        align-items: flex-start;
    }

    .grade-left {
        text-align: center;
        min-width: 80px;
    }

    .grade-letter {
        font-family: 'DM Mono', monospace;
        font-size: 56px;
        font-weight: 500;
        line-height: 1;
        letter-spacing: -0.03em;
    }

    .grade-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-top: 4px;
    }

    .grade-right { flex: 1; }

    .grade-feedback-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #aaa;
        margin-bottom: 8px;
    }

    .grade-feedback-text {
        font-size: 14px;
        line-height: 1.7;
        color: #333;
        margin-bottom: 12px;
    }

    .grade-meta {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        color: #bbb;
        letter-spacing: 0.04em;
    }

    /* ── Final Submit Banner ── */
    .final-banner {
        margin-top: 24px;
        background: #1a1a1a;
        border-radius: 2px;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
    }

    .final-banner-text {
        color: #fff;
        font-size: 14px;
        font-weight: 500;
    }

    .final-banner-sub {
        font-family: 'DM Mono', monospace;
        font-size: 11px;
        color: #888;
        margin-top: 3px;
    }

    .btn-final-submit {
        background: #fff;
        color: #1a1a1a;
        border: none;
        padding: 10px 28px;
        border-radius: 2px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        white-space: nowrap;
        text-decoration: none;
        transition: background 0.12s;
    }

    .btn-final-submit:hover { background: #f0f0f0; }
    .btn-final-submit:disabled { opacity: 0.4; cursor: not-allowed; }

    /* ── Submitted state ── */
    .submitted-banner {
        margin-top: 24px;
        background: #f4fcf4;
        border: 1px solid #c6e6c6;
        border-radius: 2px;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .submitted-banner-icon {
        font-size: 22px;
    }

    .submitted-banner-text {
        font-size: 14px;
        font-weight: 500;
        color: #1a6a1a;
    }

    .submitted-banner-sub {
        font-family: 'DM Mono', monospace;
        font-size: 11px;
        color: #888;
        margin-top: 3px;
    }

    .empty-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        padding: 56px;
        text-align: center;
        font-family: 'DM Mono', monospace;
        font-size: 13px;
        color: #aaa;
    }
</style>

@if(!$assignment)
    <div class="empty-card">No topic has been assigned to you yet.<br>Your mentor will assign one soon.</div>
@else

@php
    $topic        = $assignment->topic;
    $grouped      = $topic->questions->groupBy('type');
    $totalQ       = $topic->questions->count();
    $totalDone    = collect($submissionCounts)->sum('submitted');
    $isDeadlineOver = \Carbon\Carbon::parse($assignment->deadline)->isPast();
    $alreadyFinalSubmitted = in_array($assignment->status, ['submitted', 'evaluated']);
@endphp

<div class="page-header">
    <div>
        <div class="page-title">{{ $topic->title }}</div>
        <div class="page-meta">{{ $topic->description }}</div>
    </div>
    <span class="deadline-pill {{ $isDeadlineOver ? 'overdue' : '' }}">
        {{ $isDeadlineOver ? 'Overdue · ' : 'Due · ' }}
        {{ \Carbon\Carbon::parse($assignment->deadline)->format('d M Y') }}
    </span>
</div>

{{-- Module cards --}}
<div class="module-grid">
    @foreach($grouped as $type => $questions)
        @php
            $counts   = $submissionCounts[$type] ?? ['total' => count($questions), 'submitted' => 0];
            $total    = $counts['total'];
            $done     = $counts['submitted'];
            $pct      = $total > 0 ? round(($done / $total) * 100) : 0;
            $isDone   = $done >= $total && $total > 0;
            $hasStarted = $done > 0 && !$isDone;
        @endphp
        <div class="module-card {{ $isDone ? 'done' : '' }}">

            {{-- Status badge --}}
            @if($isDone)
                <span class="module-status status-done">Complete</span>
            @elseif($hasStarted)
                <span class="module-status status-started">In Progress</span>
            @else
                <span class="module-status status-new">Not Started</span>
            @endif

            <div class="module-type">{{ str_replace('_', ' ', $type) }}</div>
            <div class="module-count">{{ $total }}</div>
            <div class="module-count-label">Questions</div>

            <div class="module-progress">
                <div class="module-progress-fill {{ $isDone ? 'full' : '' }}"
                     style="width: {{ $pct }}%"></div>
            </div>
            <div class="module-progress-label">{{ $done }}/{{ $total }} answered</div>

            {{-- If already final-submitted, disable navigation --}}
            @if($alreadyFinalSubmitted)
                <span class="btn-start done-btn" style="cursor:default;">
                    ✓ Submitted
                </span>
            @else
                <a href="{{ route('intern.exam', [$assignment->id, $type]) }}"
                   class="btn-start {{ $isDone ? 'done-btn' : '' }}">
                    {{ $isDone ? '✓ Review' : ($hasStarted ? 'Continue →' : 'Start Test →') }}
                </a>
            @endif
        </div>
    @endforeach
</div>

{{-- Grade result card (shown after AI evaluation) --}}
@if($alreadyFinalSubmitted && $assignment->grade)
    @php
        $gradeColors = [
            'A' => ['bg'=>'#f0faf0','border'=>'#b8ddb8','text'=>'#1a5a1a','label'=>'Excellent'],
            'B' => ['bg'=>'#f0f5ff','border'=>'#b8ccee','text'=>'#1a3a7a','label'=>'Good'],
            'C' => ['bg'=>'#fffbf0','border'=>'#e8d8a0','text'=>'#7a5a00','label'=>'Average'],
            'D' => ['bg'=>'#fff5f0','border'=>'#e8c8b8','text'=>'#7a3a1a','label'=>'Below Average'],
            'E' => ['bg'=>'#fdf0f0','border'=>'#e8b8b8','text'=>'#7a1a1a','label'=>'Needs Improvement'],
        ];
        $gc = $gradeColors[$assignment->grade] ?? $gradeColors['E'];
    @endphp
    <div class="grade-result-card" style="background:{{ $gc['bg'] }};border-color:{{ $gc['border'] }};">
        <div class="grade-left">
            <div class="grade-letter" style="color:{{ $gc['text'] }};">{{ $assignment->grade }}</div>
            <div class="grade-label" style="color:{{ $gc['text'] }};">{{ $gc['label'] }}</div>
        </div>
        <div class="grade-right">
            @if($assignment->feedback)
                <div class="grade-feedback-label">AI Feedback</div>
                <div class="grade-feedback-text">{{ $assignment->feedback }}</div>
            @endif
            <div class="grade-meta">Exercise submitted · Mentor review pending</div>
        </div>
    </div>

{{-- Grade pending (submitted but AI still processing) --}}
@elseif($alreadyFinalSubmitted)
    <div class="final-banner" style="background:#2a2a2a;">
        <div>
            <div class="final-banner-text">Exercise submitted — AI evaluation in progress</div>
            <div class="final-banner-sub">Your grade will appear here once ready. Check back shortly.</div>
        </div>
        <div style="font-family:'DM Mono',monospace;font-size:28px;color:#444;">…</div>
    </div>

{{-- Show grade result from session flash (right after submit) --}}
@elseif(session('exercise_result'))
    @php $res = session('exercise_result'); @endphp
    <div class="final-banner" style="background:#1a1a1a;">
        <div>
            <div class="final-banner-text">
                Grade: <strong style="font-size:22px;letter-spacing:-0.02em;">{{ $res['grade'] }}</strong>
                &nbsp;·&nbsp; {{ $res['summary'] ?? '' }}
            </div>
            <div class="final-banner-sub">{{ $res['feedback'] ?? '' }}</div>
        </div>
    </div>

{{-- Submit button — always available, intern submits when ready --}}
@else
    <div class="final-banner">
        <div>
            <div class="final-banner-text">
                {{ $totalDone }}/{{ $totalQ }} questions answered
            </div>
            <div class="final-banner-sub">
                You can submit at any time — AI will evaluate all your answers together and give you a grade.
            </div>
        </div>
        <form method="POST" action="{{ route('intern.final.submit', $assignment->id) }}"
              onsubmit="return confirm('Submit exercise for AI grading? You cannot change answers after this.')">
            @csrf
            <button type="submit" class="btn-final-submit"
                    {{ $totalDone === 0 ? 'disabled' : '' }}>
                Submit Exercise →
            </button>
        </form>
    </div>
@endif

@endif

@endsection