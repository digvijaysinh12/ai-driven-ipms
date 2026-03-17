@extends('layouts.intern')

@section('title', 'Questions')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding-bottom: 18px;
        border-bottom: 1px solid #e5e5e5;
        margin-bottom: 24px;
    }

    .page-title { font-size: 18px; font-weight: 500; letter-spacing: -0.01em; }

    .page-meta {
        font-family: 'DM Mono', monospace;
        font-size: 11px; color: #aaa; margin-top: 3px;
    }

    .back-link {
        font-family: 'DM Mono', monospace;
        font-size: 11px; letter-spacing: 0.08em;
        text-transform: uppercase; color: #888;
        text-decoration: none;
    }

    .back-link:hover { color: #1a1a1a; }

    .question-list {
        display: flex;
        flex-direction: column;
        gap: 1px;
        background: #e5e5e5;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        overflow: hidden;
    }

    .question-row {
        background: #fff;
        padding: 18px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    .question-row:hover { background: #fafafa; }

    .q-left { flex: 1; min-width: 0; }

    .q-num {
        font-family: 'DM Mono', monospace;
        font-size: 10px; color: #aaa;
        letter-spacing: 0.06em;
        margin-bottom: 5px;
    }

    .q-text {
        font-size: 13.5px;
        color: #1a1a1a;
        line-height: 1.5;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 600px;
    }

    .q-right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
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

    .badge-done { background: #eaf5e8; color: #1a6a1a; }
    .badge-pending { background: #f0f0f0; color: #888; }

    .btn-solve {
        background: #1a1a1a;
        color: #fff;
        text-decoration: none;
        padding: 6px 16px;
        border-radius: 2px;
        font-size: 12px;
        font-weight: 500;
        font-family: 'DM Sans', sans-serif;
        transition: background 0.12s;
    }

    .btn-solve:hover { background: #333; }

    .btn-view {
        background: #fff;
        color: #1a1a1a;
        text-decoration: none;
        padding: 6px 16px;
        border-radius: 2px;
        font-size: 12px;
        border: 1px solid #d4d4d4;
        font-family: 'DM Sans', sans-serif;
        transition: border-color 0.12s;
    }

    .btn-view:hover { border-color: #888; }
</style>

<div class="page-header">
    <div>
        <div class="page-title">{{ str_replace('_', ' ', $type) }}</div>
        <div class="page-meta">{{ $questions->count() }} questions · {{ $topic->title }}</div>
    </div>
    <a href="{{ route('intern.topic') }}" class="back-link">← Back</a>
</div>

<div class="question-list">
    @foreach($questions as $index => $question)
        @php $submitted = $submissions->has($question->id); @endphp
        <div class="question-row">
            <div class="q-left">
                <div class="q-num">Q{{ $index + 1 }}</div>
                <div class="q-text">{{ $question->problem_statement }}</div>
            </div>
            <div class="q-right">
                @if($submitted)
                    <span class="badge badge-done">Submitted</span>
                    <a href="{{ route('intern.solve', $question->id) }}" class="btn-view">View</a>
                @else
                    <span class="badge badge-pending">Pending</span>
                    <a href="{{ route('intern.solve', $question->id) }}" class="btn-solve">Solve</a>
                @endif
            </div>
        </div>
    @endforeach
</div>

@endsection