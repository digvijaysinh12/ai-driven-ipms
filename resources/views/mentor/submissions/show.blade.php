@extends('layouts.app')
@section('title', 'Review Submission')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Review Submission</div>
        <div class="page-meta">{{ $submission->intern->name ?? '—' }} · {{ $question->topic->title ?? '—' }}</div>
    </div>
    <a href="{{ route('mentor.submissions.index') }}" class="back-link">← All Submissions</a>
</div>

{{-- Status bar --}}
<div class="stat-mosaic" style="margin-bottom:24px;">
    <div class="stat-cell">
        <div class="stat-label">Intern</div>
        <div style="font-size:14px;font-weight:500;">{{ $submission->intern->name ?? '—' }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Question Type</div>
        <div style="font-size:14px;font-weight:500;">{{ ucfirst(str_replace('_', ' ', $question->type)) }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Status</div>
        <x-badge :status="$submission->status" />
    </div>
    <div class="stat-cell">
        <div class="stat-label">AI Score</div>
        <div class="stat-value">{{ $submission->ai_total_score ?? '—' }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Submitted</div>
        <div class="cell-mono">{{ $submission->created_at->format('d M Y') }}</div>
    </div>
</div>

{{-- Side by side: question + intern answer --}}
<div class="review-grid">

    {{-- Left: Question + Reference --}}
    <div class="review-panel">
        <div class="panel-label">Question &amp; Reference</div>

        <div style="font-size:14px;color:#1a1a1a;line-height:1.75;margin-bottom:16px;">
            {{ $question->problem_statement }}
        </div>

        @if($question->code)
            <pre class="code-block" style="margin-bottom:14px;">{{ $question->code }}</pre>
        @endif

        @if($question->type === 'mcq')
            <div style="margin-bottom:14px;">
                @foreach(['A' => $question->option_a, 'B' => $question->option_b, 'C' => $question->option_c, 'D' => $question->option_d] as $key => $val)
                    @if($val)
                        <div class="mcq-opt {{ $question->correct_answer === $key ? 'correct' : '' }}">
                            <span class="opt-key">{{ $key }}</span>
                            <span>{{ $val }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        @if(in_array($question->type, ['true_false', 'blank', 'output', 'mcq']))
            <div style="font-family:'DM Mono',monospace;font-size:10px;color:#aaa;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                Correct Answer
            </div>
            <span style="font-family:'DM Mono',monospace;font-size:13px;color:#1a6a1a;background:#f0faf0;padding:3px 12px;border-radius:2px;border:1px solid #b8ddb8;">
                {{ $question->correct_answer }}
            </span>
        @endif

        @if(isset($reference) && $reference)
            <div style="font-family:'DM Mono',monospace;font-size:10px;color:#aaa;text-transform:uppercase;letter-spacing:0.06em;margin:16px 0 6px;">
                Reference Solution
            </div>
            <pre class="code-block" style="font-size:12px;">{{ $reference->solution_code }}</pre>
            @if($reference->explanation)
                <div style="font-size:12px;color:#777;margin-top:6px;font-style:italic;">{{ $reference->explanation }}</div>
            @endif
        @endif
    </div>

    {{-- Right: Intern's answer --}}
    <div class="review-panel">
        <div class="panel-label">Intern's Answer</div>

        @if($question->type === 'mcq')
            <div style="font-family:'DM Mono',monospace;font-size:10px;color:#aaa;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Selected Option</div>
            <span style="font-family:'DM Mono',monospace;font-size:13px;color:#1a3a6a;background:#e8f0f5;padding:3px 12px;border-radius:2px;border:1px solid #9ab8d8;">
                {{ $submission->submitted_code }}
            </span>
        @else
            <pre class="answer-box">{{ $submission->submitted_code }}</pre>
        @endif

        @if($submission->feedback)
            <div style="font-family:'DM Mono',monospace;font-size:10px;color:#aaa;text-transform:uppercase;letter-spacing:0.06em;margin:20px 0 6px;">AI Feedback</div>
            <div style="font-size:13px;color:#555;line-height:1.7;background:#fafafa;border:1px solid #ebebeb;border-radius:2px;padding:12px 14px;">
                {{ $submission->feedback }}
            </div>
        @endif
    </div>
</div>

{{-- Review form / already reviewed --}}
@if($submission->status === 'reviewed')
    <div style="background:#f0faf0;border:1px solid #c6e6c6;border-radius:2px;padding:14px 18px;font-family:'DM Mono',monospace;font-size:12px;color:#1a6a1a;">
        ✓ You have already reviewed this submission.
        @if($submission->mentor_override_score !== null)
            Score given: <strong>{{ $submission->mentor_override_score }}/30</strong>
        @endif
    </div>
    <div style="margin-top:14px;">
        <a href="{{ route('mentor.submissions.index') }}" class="btn-outline">← Back to Submissions</a>
    </div>
@else
    <div class="form-card" style="max-width:100%;">
        <div style="font-size:15px;font-weight:500;margin-bottom:20px;">Mentor Review</div>
        <form method="POST" action="{{ route('mentor.submissions.review', $submission->id) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">
                    Override Score
                    <span style="font-family:'DM Mono',monospace;font-size:9px;color:#bbb;margin-left:6px;">0–30 · leave blank to accept AI score</span>
                </label>
                <input type="number" name="mentor_override_score" class="form-input"
                       min="0" max="30"
                       value="{{ old('mentor_override_score', $submission->ai_total_score) }}"
                       placeholder="0–30">
                <div class="form-hint">AI scored: {{ $submission->ai_total_score ?? '—' }}/30</div>
                @error('mentor_override_score') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Feedback for Intern</label>
                <textarea name="feedback" class="form-textarea"
                          placeholder="Write feedback visible to the intern...">{{ old('feedback', $submission->feedback) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Save Review</button>
                <a href="{{ route('mentor.submissions.index') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endif
@endsection