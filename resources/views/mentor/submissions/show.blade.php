@extends('layouts.mentor')
@section('title', 'Review Submission')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 1px solid #e5e5e5; margin-bottom: 28px; }
    .page-title  { font-size: 20px; font-weight: 500; letter-spacing: -0.02em; }
    .page-meta   { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; margin-top: 4px; }
    .back-link   { font-family: 'DM Mono', monospace; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: #888; text-decoration: none; white-space: nowrap; }
    .back-link:hover { color: #1a1a1a; }

    .review-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1px; background: #e5e5e5; border: 1px solid #e5e5e5; border-radius: 2px; overflow: hidden; margin-bottom: 24px; }
    .review-panel { background: #fff; padding: 24px 26px; }

    .panel-label { font-family: 'DM Mono', monospace; font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: #aaa; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0; }

    .q-statement { font-size: 14px; color: #1a1a1a; line-height: 1.75; margin-bottom: 16px; }
    .code-block  { background: #1a1a1a; color: #d4d4d4; padding: 14px 18px; border-radius: 2px; font-family: 'DM Mono', monospace; font-size: 13px; line-height: 1.65; overflow-x: auto; margin-bottom: 14px; white-space: pre-wrap; }
    .answer-box  { background: #fafafa; border: 1px solid #ebebeb; border-radius: 2px; padding: 14px 16px; font-family: 'DM Mono', monospace; font-size: 13px; color: #333; line-height: 1.65; white-space: pre-wrap; }

    .mcq-options { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
    .mcq-opt { display: flex; align-items: flex-start; gap: 8px; padding: 8px 12px; border: 1px solid #ebebeb; border-radius: 2px; font-size: 13px; }
    .mcq-opt.correct { background: #f0faf0; border-color: #b8ddb8; }
    .mcq-opt.selected { background: #e8f0f5; border-color: #9ab8d8; }
    .mcq-opt.correct.selected { background: #f0faf0; border-color: #1a6a1a; }
    .opt-key { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; min-width: 18px; padding-top: 1px; }
    .mcq-opt.correct .opt-key { color: #1a6a1a; }

    .answer-label-row { font-family: 'DM Mono', monospace; font-size: 10px; color: #aaa; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; margin-top: 12px; }
    .correct-answer  { display: inline-block; background: #f0faf0; color: #1a6a1a; border: 1px solid #b8ddb8; padding: 3px 12px; border-radius: 2px; font-family: 'DM Mono', monospace; font-size: 13px; }
    .intern-answer   { display: inline-block; background: #e8f0f5; color: #1a3a6a; border: 1px solid #9ab8d8; padding: 3px 12px; border-radius: 2px; font-family: 'DM Mono', monospace; font-size: 13px; }

    .ref-note { font-size: 12px; color: #777; line-height: 1.6; margin-top: 10px; font-style: italic; }

    .status-row { display: flex; gap: 1px; background: #e5e5e5; border: 1px solid #e5e5e5; border-radius: 2px; overflow: hidden; margin-bottom: 24px; }
    .status-cell { flex: 1; background: #fff; padding: 16px 20px; }
    .status-label { font-family: 'DM Mono', monospace; font-size: 10px; color: #aaa; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 5px; }
    .status-value { font-family: 'DM Mono', monospace; font-size: 20px; font-weight: 400; color: #1a1a1a; }
    .status-value.muted { color: #ccc; }
    .status-value.good  { color: #1a6a1a; }
    .status-value.warn  { color: #92681a; }

    .review-form { background: #fff; border: 1px solid #e5e5e5; border-radius: 2px; padding: 24px 26px; }
    .form-title  { font-size: 15px; font-weight: 500; margin-bottom: 20px; }
    .form-label  { display: block; font-family: 'DM Mono', monospace; font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: #666; margin-bottom: 7px; }
    .form-input  { width: 100%; border: 1px solid #d4d4d4; border-radius: 2px; padding: 10px 12px; font-size: 14px; font-family: 'DM Sans', sans-serif; color: #1a1a1a; background: #fafafa; outline: none; transition: border-color 0.12s; }
    .form-input:focus  { border-color: #1a1a1a; background: #fff; }
    .form-textarea { min-height: 100px; resize: vertical; }
    .form-hint  { font-family: 'DM Mono', monospace; font-size: 10px; color: #bbb; margin-top: 4px; }
    .form-group { margin-bottom: 18px; }
    .form-actions { display: flex; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid #ebebeb; }
    .btn-primary { background: #1a1a1a; color: #fff; border: none; border-radius: 2px; padding: 10px 26px; font-size: 13px; font-weight: 500; cursor: pointer; font-family: 'DM Sans', sans-serif; transition: background 0.12s; }
    .btn-primary:hover { background: #333; }
    .btn-ghost   { background: none; color: #888; border: 1px solid #d4d4d4; border-radius: 2px; padding: 10px 20px; font-size: 13px; text-decoration: none; font-family: 'DM Sans', sans-serif; }
    .btn-ghost:hover { border-color: #888; color: #1a1a1a; }

    .already-reviewed { background: #f0faf0; border: 1px solid #c6e6c6; border-radius: 2px; padding: 14px 18px; font-family: 'DM Mono', monospace; font-size: 12px; color: #1a6a1a; }
</style>

<div class="page-header">
    <div>
        <div class="page-title">Review Submission</div>
        <div class="page-meta">
            {{ $submission->intern->name ?? '—' }} · {{ $question->topic->title ?? '—' }}
        </div>
    </div>
    <a href="{{ route('mentor.submissions.index') }}" class="back-link">← All Submissions</a>
</div>

{{-- Status bar --}}
<div class="status-row">
    <div class="status-cell">
        <div class="status-label">Intern</div>
        <div class="status-value" style="font-size:15px;font-family:'DM Sans',sans-serif;">{{ $submission->intern->name ?? '—' }}</div>
    </div>
    <div class="status-cell">
        <div class="status-label">Question Type</div>
        <div class="status-value" style="font-size:15px;">{{ ucfirst(str_replace('_',' ',$question->type)) }}</div>
    </div>
    <div class="status-cell">
        <div class="status-label">Status</div>
        <div class="status-value" style="font-size:14px;font-family:'DM Sans',sans-serif;">
            {{ ucfirst(str_replace('_',' ',$submission->status)) }}
        </div>
    </div>
    <div class="status-cell">
        <div class="status-label">Submitted</div>
        <div class="status-value muted" style="font-size:14px;">{{ $submission->created_at->format('d M Y') }}</div>
    </div>
</div>

{{-- Side by side: question + intern answer --}}
<div class="review-grid">

    {{-- Left: Question + Reference --}}
    <div class="review-panel">
        <div class="panel-label">Question &amp; Reference</div>

        <div class="q-statement">{{ $question->problem_statement }}</div>

        @if($question->code)
            <pre class="code-block">{{ $question->code }}</pre>
        @endif

        @if($question->type === 'mcq')
            <div class="mcq-options">
                @foreach(['A'=>$question->option_a,'B'=>$question->option_b,'C'=>$question->option_c,'D'=>$question->option_d] as $key => $val)
                    @if($val)
                    <div class="mcq-opt {{ $question->correct_answer === $key ? 'correct' : '' }}">
                        <span class="opt-key">{{ $key }}</span>
                        <span>{{ $val }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
        @endif

        @if(in_array($question->type, ['true_false','blank','output','mcq']))
            <div class="answer-label-row">Correct Answer</div>
            <span class="correct-answer">{{ $question->correct_answer }}</span>
        @endif

        @if($reference)
            <div class="answer-label-row" style="margin-top:16px;">Reference Solution</div>
            <pre class="code-block" style="font-size:12px;">{{ $reference->solution_code }}</pre>
            @if($reference->explanation)
                <div class="ref-note">{{ $reference->explanation }}</div>
            @endif
        @endif
    </div>

    {{-- Right: Intern's answer --}}
    <div class="review-panel">
        <div class="panel-label">Intern's Answer</div>

        @if($question->type === 'mcq')
            <div class="answer-label-row">Selected Option</div>
            <span class="intern-answer">{{ $submission->submitted_code }}</span>
            <div style="margin-top:8px;font-size:12px;color:#777;">
                @php $selectedOpt = $question->{'option_' . strtolower($submission->submitted_code)} ?? null; @endphp
                @if($selectedOpt) {{ $selectedOpt }} @endif
            </div>

        @elseif($question->type === 'true_false')
            <div class="answer-label-row">Selected</div>
            <span class="intern-answer">{{ $submission->submitted_code }}</span>

        @else
            <pre class="answer-box">{{ $submission->submitted_code }}</pre>
        @endif

        @if($submission->feedback)
            <div class="answer-label-row" style="margin-top:20px;">AI Feedback</div>
            <div style="font-size:13px;color:#555;line-height:1.7;background:#fafafa;border:1px solid #ebebeb;border-radius:2px;padding:12px 14px;">
                {{ $submission->feedback }}
            </div>
        @endif
    </div>
</div>

{{-- Review form --}}
@if($submission->status === 'reviewed')
    <div class="already-reviewed">
        ✓ You have already reviewed this submission.
        @if($submission->mentor_override_score !== null)
            Score given: <strong>{{ $submission->mentor_override_score }}/30</strong>
        @endif
    </div>
    <div style="margin-top:14px;">
        <a href="{{ route('mentor.submissions.index') }}" class="btn-ghost" style="display:inline-block;padding:9px 20px;border:1px solid #d4d4d4;border-radius:2px;font-size:13px;color:#888;text-decoration:none;">← Back to Submissions</a>
    </div>
@else
    <div class="review-form">
        <div class="form-title">Mentor Review</div>
        <form method="POST" action="{{ route('mentor.submissions.review', $submission->id) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Override Score <span style="color:#bbb;font-size:9px;">(0–30 · leave blank to accept AI score)</span></label>
                <input type="number" name="mentor_override_score" class="form-input"
                       min="0" max="30"
                       value="{{ old('mentor_override_score', $submission->ai_total_score) }}"
                       placeholder="0–30">
                <div class="form-hint">AI scored: {{ $submission->ai_total_score ?? '—' }}/30</div>
                @error('mentor_override_score')
                    <div style="font-family:'DM Mono',monospace;font-size:10px;color:#c0392b;margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Feedback for Intern</label>
                <textarea name="feedback" class="form-input form-textarea"
                          placeholder="Write feedback visible to the intern...">{{ old('feedback', $submission->feedback) }}</textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Save Review</button>
                <a href="{{ route('mentor.submissions.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
@endif
@endsection