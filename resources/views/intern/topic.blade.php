@extends('layouts.intern')

@section('title', 'My Topic')

@section('content')

<style>
    :root {
        --primary-color: #2563eb;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --border-radius: 8px;
        --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-color: var(--gray-50);
        color: var(--gray-900);
        line-height: 1.6;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
        letter-spacing: -0.025em;
    }

    .page-meta {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin-top: 0.5rem;
        font-family: 'JetBrains Mono', monospace;
    }

    .deadline-badge {
        background-color: var(--gray-100);
        color: var(--gray-700);
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        font-size: 0.75rem;
        font-weight: 500;
        font-family: 'JetBrains Mono', monospace;
        white-space: nowrap;
    }

    .deadline-badge.overdue {
        background-color: #fef2f2;
        color: var(--danger-color);
    }

    .module-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .module-card {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .module-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .module-card.completed {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-color: var(--success-color);
    }

    .module-status {
        position: absolute;
        top: 1rem;
        right: 1rem;
        font-size: 0.625rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-family: 'JetBrains Mono', monospace;
    }

    .status-completed {
        background-color: #dcfce7;
        color: #166534;
    }

    .status-in-progress {
        background-color: #fef3c7;
        color: #92400e;
    }

    .status-not-started {
        background-color: var(--gray-100);
        color: var(--gray-600);
    }

    .module-type {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
        font-family: 'JetBrains Mono', monospace;
    }

    .module-count {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .module-label {
        font-size: 0.75rem;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-family: 'JetBrains Mono', monospace;
    }

    .progress-container {
        margin: 1rem 0;
    }

    .progress-bar {
        width: 100%;
        height: 6px;
        background-color: var(--gray-200);
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-color) 0%, #3b82f6 100%);
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    .progress-fill.completed {
        background: linear-gradient(90deg, var(--success-color) 0%, #34d399 100%);
    }

    .progress-text {
        font-size: 0.75rem;
        color: var(--gray-600);
        font-family: 'JetBrains Mono', monospace;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
        width: 100%;
    }

    .btn-primary:hover {
        background-color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: var(--shadow-lg);
    }

    .btn-primary.completed {
        background-color: var(--success-color);
        border: 2px solid var(--success-color);
    }

    .btn-primary.completed:hover {
        background-color: #059669;
    }

    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .result-card {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-top: 2rem;
        box-shadow: var(--shadow);
    }

    .result-header {
        display: flex;
        align-items: center;
        gap: 2rem;
        margin-bottom: 1.5rem;
    }

    .grade-display {
        text-align: center;
        min-width: 5rem;
    }

    .grade-letter {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.025em;
        margin-bottom: 0.25rem;
    }

    .grade-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-family: 'JetBrains Mono', monospace;
    }

    .result-content {
        flex: 1;
    }

    .result-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
        font-family: 'JetBrains Mono', monospace;
    }

    .result-text {
        font-size: 1rem;
        color: var(--gray-700);
        line-height: 1.6;
        margin-bottom: 0.75rem;
    }

    .result-meta {
        font-size: 0.75rem;
        color: var(--gray-500);
        font-family: 'JetBrains Mono', monospace;
    }

    .submit-banner {
        background: linear-gradient(135deg, var(--primary-color) 0%, #3b82f6 100%);
        color: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-top: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: var(--shadow-lg);
    }

    .submit-content h3 {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .submit-content p {
        font-size: 0.875rem;
        opacity: 0.9;
        margin: 0;
    }

    .btn-submit {
        background: white;
        color: var(--primary-color);
        border: 2px solid white;
        padding: 0.75rem 2rem;
        border-radius: var(--border-radius);
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-submit:hover {
        background: transparent;
        color: white;
    }

    .btn-submit:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .status-banner {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        color: #92400e;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        margin-top: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .status-icon {
        font-size: 1.5rem;
    }

    .status-content h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .status-content p {
        font-size: 0.875rem;
        margin: 0;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--gray-500);
    }

    .empty-state h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        font-size: 1rem;
        margin: 0;
    }

    @media (max-width: 768px) {
        .container {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
            gap: 1rem;
        }

        .module-grid {
            grid-template-columns: 1fr;
        }

        .result-header {
            flex-direction: column;
            gap: 1rem;
        }

        .submit-banner {
            flex-direction: column;
            gap: 1.5rem;
            text-align: center;
        }
    }
</style>

@if(!$assignment)
    <div class="empty-state">
        <h2>No Topic Assigned</h2>
        <p>Your mentor will assign a topic for you to practice soon.</p>
    </div>
@else

@php
    $topic        = $assignment->topic;
    $grouped      = $topic->questions->groupBy('type');
    $totalQ       = $topic->questions->count();
    $totalDone    = collect($submissionCounts)->sum('submitted');
    $isDeadlineOver = \Carbon\Carbon::parse($assignment->deadline)->isPast();
    $alreadyFinalSubmitted = in_array($assignment->status, ['submitted', 'evaluated']);
@endphp

<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $topic->title }}</h1>
            <p class="page-meta">{{ $topic->description }}</p>
        </div>
        <span class="deadline-badge {{ $isDeadlineOver ? 'overdue' : '' }}">
            {{ $isDeadlineOver ? 'Overdue' : 'Due' }}: {{ \Carbon\Carbon::parse($assignment->deadline)->format('M j, Y') }}
        </span>
    </div>

    <div class="module-grid">
        @foreach($grouped as $type => $questions)
            @php
                $counts = $submissionCounts[$type] ?? ['total' => count($questions), 'submitted' => 0];
                $total = $counts['total'];
                $done = $counts['submitted'];
                $pct = $total > 0 ? round(($done / $total) * 100) : 0;
                $isDone = $done >= $total && $total > 0;
                $hasStarted = $done > 0 && !$isDone;
            @endphp
            <div class="module-card {{ $isDone ? 'completed' : '' }}">
                <span class="module-status {{ $isDone ? 'status-completed' : ($hasStarted ? 'status-in-progress' : 'status-not-started') }}">
                    {{ $isDone ? 'Completed' : ($hasStarted ? 'In Progress' : 'Not Started') }}
                </span>

                <div class="module-type">{{ ucwords(str_replace('_', ' ', $type)) }}</div>
                <div class="module-count">{{ $total }}</div>
                <div class="module-label">Questions</div>

                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill {{ $isDone ? 'completed' : '' }}" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="progress-text">{{ $done }}/{{ $total }} answered</div>
                </div>

                @if($alreadyFinalSubmitted)
                    <button class="btn-primary completed" disabled>
                        ✓ Submitted
                    </button>
                @else
                    <a href="{{ route('intern.exam', [$assignment->id, $type]) }}" class="btn-primary {{ $isDone ? 'completed' : '' }}">
                        {{ $isDone ? '✓ Review' : ($hasStarted ? 'Continue' : 'Start') }}
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