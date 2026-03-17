@extends('layouts.mentor')
@section('title', ucfirst(str_replace('_',' ',$type)) . ' Questions')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 1px solid #e5e5e5; margin-bottom: 28px; }
    .page-title  { font-size: 20px; font-weight: 500; letter-spacing: -0.02em; text-transform: capitalize; }
    .page-meta   { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; margin-top: 4px; }
    .back-link   { font-family: 'DM Mono', monospace; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: #888; text-decoration: none; white-space: nowrap; }
    .back-link:hover { color: #1a1a1a; }

    .question-stack { display: flex; flex-direction: column; gap: 1px; background: #e5e5e5; border: 1px solid #e5e5e5; border-radius: 2px; overflow: hidden; }
    .q-card { background: #fff; padding: 24px 28px; }
    .q-card:hover { background: #fafafa; }

    .q-meta { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    .q-num  { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; }
    .q-type-tag { font-family: 'DM Mono', monospace; font-size: 10px; background: #f0f0f0; color: #666; padding: 2px 8px; border-radius: 2px; text-transform: uppercase; letter-spacing: 0.06em; }

    .q-statement { font-size: 14px; color: #1a1a1a; line-height: 1.7; margin-bottom: 14px; }

    .code-block { background: #1a1a1a; color: #d4d4d4; padding: 14px 18px; border-radius: 2px; font-family: 'DM Mono', monospace; font-size: 13px; line-height: 1.6; overflow-x: auto; margin-bottom: 14px; }

    .mcq-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 14px; }
    .mcq-option { display: flex; align-items: flex-start; gap: 8px; padding: 8px 12px; background: #f8f8f8; border: 1px solid #ebebeb; border-radius: 2px; font-size: 13px; }
    .mcq-option.correct { background: #f0faf0; border-color: #b8ddb8; }
    .option-key { font-family: 'DM Mono', monospace; font-size: 11px; color: #aaa; min-width: 16px; padding-top: 1px; }
    .mcq-option.correct .option-key { color: #1a6a1a; }

    .answer-row { display: flex; align-items: flex-start; gap: 10px; margin-top: 10px; }
    .answer-label { font-family: 'DM Mono', monospace; font-size: 10px; color: #aaa; text-transform: uppercase; letter-spacing: 0.06em; padding-top: 2px; white-space: nowrap; }
    .answer-val { font-family: 'DM Mono', monospace; font-size: 13px; color: #1a6a1a; background: #f0faf0; padding: 3px 10px; border-radius: 2px; border: 1px solid #c6e6c6; }

    .ref-box { background: #f5f5f4; border: 1px solid #e5e5e5; border-radius: 2px; padding: 12px 14px; font-size: 13px; color: #444; line-height: 1.6; margin-top: 10px; }
    .ref-label { font-family: 'DM Mono', monospace; font-size: 10px; color: #aaa; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }

    .empty-state { padding: 56px; text-align: center; background: #fff; border: 1px solid #e5e5e5; border-radius: 2px; font-family: 'DM Mono', monospace; font-size: 13px; color: #aaa; }
</style>

<div class="page-header">
    <div>
        <div class="page-title">{{ str_replace('_',' ', $type) }}</div>
        <div class="page-meta">{{ $questions->count() }} questions · {{ $topic->title }}</div>
    </div>
    <a href="{{ route('mentor.topics.show', $topic->id) }}" class="back-link">← Back to Topic</a>
</div>

@if($questions->isEmpty())
    <div class="empty-state">No {{ $type }} questions found.</div>
@else
<div class="question-stack">
    @foreach($questions as $i => $q)
    <div class="q-card">
        <div class="q-meta">
            <span class="q-num">Q{{ $i + 1 }}</span>
            <span class="q-type-tag">{{ str_replace('_',' ',$type) }}</span>
        </div>

        <div class="q-statement">{{ $q->problem_statement }}</div>

        @if($q->code)
            <pre class="code-block">{{ $q->code }}</pre>
        @endif

        @if($type === 'mcq')
            <div class="mcq-grid">
                @foreach(['A'=>$q->option_a,'B'=>$q->option_b,'C'=>$q->option_c,'D'=>$q->option_d] as $key => $val)
                    @if($val)
                    <div class="mcq-option {{ $q->correct_answer === $key ? 'correct' : '' }}">
                        <span class="option-key">{{ $key }}</span>
                        <span>{{ $val }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
        @endif

        @if(in_array($type, ['true_false','blank','output']))
            <div class="answer-row">
                <span class="answer-label">Answer</span>
                <span class="answer-val">{{ $q->correct_answer }}</span>
            </div>
        @endif

        @if($q->referenceSolution)
            <div style="margin-top:14px;">
                <div class="ref-label">Reference Solution</div>
                <pre class="ref-box">{{ $q->referenceSolution->solution_code }}</pre>
                @if($q->referenceSolution->explanation)
                    <div style="font-size:12px;color:#777;margin-top:6px;">{{ $q->referenceSolution->explanation }}</div>
                @endif
            </div>
        @endif
    </div>
    @endforeach
</div>
@endif
@endsection