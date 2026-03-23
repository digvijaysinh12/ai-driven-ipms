@extends('layouts.app')
@section('title', 'My Topic')

@section('content')

@if(!$assignment)
    <div style="background:#fff;border:1px solid #e5e5e5;border-radius:2px;
                padding:56px;text-align:center;font-family:'DM Mono',monospace;
                font-size:13px;color:#aaa;">
        No topic has been assigned to you yet.<br>Your mentor will assign one soon.
    </div>

@else
    @php
        $topic       = $assignment->topic;
        $grouped     = $topic->questions->groupBy('type');
        $totalQ      = $topic->questions->count();
        $totalDone   = collect($submissionCounts)->sum('submitted');
        $isOverdue   = \Carbon\Carbon::parse($assignment->deadline)->isPast();
        $isSubmitted = in_array($assignment->status, ['submitted', 'evaluated']);
    @endphp

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <div class="page-title">{{ $topic->title }}</div>
            <div class="page-meta">{{ $topic->description }}</div>
        </div>
        <span style="font-family:'DM Mono',monospace;font-size:11px;
                     color:{{ $isOverdue ? '#c0392b' : '#555' }};
                     background:{{ $isOverdue ? '#fdf0f0' : '#f0f0f0' }};
                     padding:6px 14px;border-radius:2px;white-space:nowrap;">
            {{ $isOverdue ? 'Overdue · ' : 'Due · ' }}
            {{ \Carbon\Carbon::parse($assignment->deadline)->format('d M Y') }}
        </span>
    </div>

    {{-- Module cards --}}
    <div class="module-grid">
        @foreach($grouped as $type => $questions)
            @php
                $counts  = $submissionCounts[$type] ?? ['total' => count($questions), 'submitted' => 0];
                $total   = $counts['total'];
                $done    = $counts['submitted'];
                $pct     = $total > 0 ? round(($done / $total) * 100) : 0;
                $isDone  = $done >= $total && $total > 0;
                $started = $done > 0 && !$isDone;
            @endphp

            <div class="module-card {{ $isDone ? 'done' : '' }}">
                <span class="module-status
                    {{ $isDone ? 'status-done' : ($started ? 'status-started' : 'status-new') }}">
                    {{ $isDone ? 'Complete' : ($started ? 'In Progress' : 'Not Started') }}
                </span>

                <div class="module-type">{{ str_replace('_', ' ', $type) }}</div>
                <div class="module-count">{{ $total }}</div>
                <div class="module-count-label">Questions</div>

                <div class="module-progress">
                    <div class="module-progress-fill {{ $isDone ? 'full' : '' }}"
                         style="width:{{ $pct }}%"></div>
                </div>
                <div class="module-progress-label">{{ $done }}/{{ $total }} answered</div>

                @if($isSubmitted)
                    <span class="btn-primary"
                          style="display:block;text-align:center;opacity:0.5;cursor:default;">
                        ✓ Submitted
                    </span>
                @else
                    <a href="{{ route('intern.exam', [$assignment->id, $type]) }}"
                       class="{{ $isDone ? 'btn-outline' : 'btn-primary' }}"
                       style="display:block;text-align:center;">
                        {{ $isDone ? '✓ Review' : ($started ? 'Continue →' : 'Start →') }}
                    </a>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Bottom banner: grade / pending / submit --}}
    @if($isSubmitted && $assignment->grade)
        @php
            $gc = [
                'A' => ['#f0faf0','#b8ddb8','#1a5a1a','Excellent'],
                'B' => ['#f0f5ff','#b8ccee','#1a3a7a','Good'],
                'C' => ['#fffbf0','#e8d8a0','#7a5a00','Average'],
                'D' => ['#fff5f0','#e8c8b8','#7a3a1a','Below Average'],
                'E' => ['#fdf0f0','#e8b8b8','#7a1a1a','Needs Improvement'],
            ][$assignment->grade] ?? ['#fdf0f0','#e8b8b8','#7a1a1a','Needs Improvement'];
        @endphp
        <div style="margin-top:24px;background:{{ $gc[0] }};border:1px solid {{ $gc[1] }};
                    border-radius:2px;padding:24px 28px;display:flex;gap:28px;align-items:flex-start;">
            <div style="text-align:center;min-width:80px;">
                <div style="font-family:'DM Mono',monospace;font-size:56px;font-weight:500;
                            color:{{ $gc[2] }};line-height:1;">
                    {{ $assignment->grade }}
                </div>
                <div style="font-family:'DM Mono',monospace;font-size:10px;color:{{ $gc[2] }};
                            text-transform:uppercase;letter-spacing:0.08em;margin-top:4px;">
                    {{ $gc[3] }}
                </div>
            </div>
            <div>
                @if($assignment->feedback)
                    <div class="section-label" style="margin-bottom:6px;">AI Feedback</div>
                    <div style="font-size:14px;line-height:1.7;color:#333;margin-bottom:12px;">
                        {{ $assignment->feedback }}
                    </div>
                @endif
                <div class="cell-mono">Exercise submitted · Mentor review pending</div>
            </div>
        </div>

    @elseif($isSubmitted)
        <div class="final-banner" style="background:#2a2a2a;margin-top:24px;">
            <div>
                <div class="final-banner-text">Submitted — AI evaluation in progress</div>
                <div class="final-banner-sub">Your grade will appear here once ready.</div>
            </div>
            <div style="font-family:'DM Mono',monospace;font-size:28px;color:#444;">…</div>
        </div>

    @else
        <div class="final-banner" style="margin-top:24px;">
            <div>
                <div class="final-banner-text">
                    {{ $totalDone }}/{{ $totalQ }} questions answered
                </div>
                <div class="final-banner-sub">
                    Submit when ready — AI will evaluate all answers and give you a grade.
                </div>
            </div>
            <form method="POST" action="{{ route('intern.final.submit', $assignment->id) }}"
                  onsubmit="return confirm('Submit for AI grading? You cannot change answers after this.')">
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