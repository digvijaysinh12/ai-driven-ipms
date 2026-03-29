@extends('layouts.app')
@section('title', ucfirst(str_replace('_', ' ', $type)) . ' Questions')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title" style="text-transform: capitalize;">{{ str_replace('_', ' ', $type) }}</div>
        <div class="page-meta">{{ $questions->count() }} questions - {{ $topic->title }}</div>
    </div>
    <a href="{{ route('mentor.topics.show', $topic->id) }}" class="back-link"><- Back to Topic</a>
</div>

@if($questions->isEmpty())
    <div style="background:#fff;border:1px solid #e5e5e5;border-radius:2px;padding:56px;text-align:center;font-family:'DM Mono',monospace;font-size:13px;color:#aaa;">
        No {{ str_replace('_', ' ', $type) }} questions found.
    </div>
@else
    <div style="display:flex;flex-direction:column;gap:1px;background:#e5e5e5;border:1px solid #e5e5e5;border-radius:2px;overflow:hidden;">
        @foreach($questions as $i => $q)
            <div id="question-card-{{ $q->id }}" style="background:#fff;padding:24px 28px;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                    <span class="cell-mono">Q{{ $i + 1 }}</span>
                    <span style="font-family:'DM Mono',monospace;font-size:10px;background:#f0f0f0;color:#666;padding:2px 8px;border-radius:2px;text-transform:uppercase;letter-spacing:0.06em;">
                        {{ str_replace('_', ' ', $type) }}
                    </span>
                </div>

                <div style="font-size:14px;color:#1a1a1a;line-height:1.7;margin-bottom:14px;">
                    {{ $q->problem_statement }}
                </div>

                @if($q->code)
                    <pre class="code-block" style="margin-bottom:14px;">{{ $q->code }}</pre>
                @endif

                @if($type === 'mcq')
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:14px;">
                        @foreach(['A' => $q->option_a, 'B' => $q->option_b, 'C' => $q->option_c, 'D' => $q->option_d] as $key => $val)
                            @if($val)
                                <div style="display:flex;align-items:flex-start;gap:8px;padding:8px 12px;background:{{ $q->correct_answer === $key ? '#f0faf0' : '#f8f8f8' }};border:1px solid {{ $q->correct_answer === $key ? '#b8ddb8' : '#ebebeb' }};border-radius:2px;font-size:13px;">
                                    <span style="font-family:'DM Mono',monospace;font-size:11px;color:{{ $q->correct_answer === $key ? '#1a6a1a' : '#aaa' }};min-width:16px;">{{ $key }}</span>
                                    <span>{{ $val }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                @if(in_array($type, ['true_false', 'blank', 'output']))
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                        <span style="font-family:'DM Mono',monospace;font-size:10px;color:#aaa;text-transform:uppercase;letter-spacing:0.06em;">Answer</span>
                        <span style="font-family:'DM Mono',monospace;font-size:13px;color:#1a6a1a;background:#f0faf0;padding:3px 10px;border-radius:2px;border:1px solid #c6e6c6;">
                            {{ $q->correct_answer }}
                        </span>
                    </div>
                @endif

                @if($type === 'coding' && $q->referenceSolution?->solution_code)
                    <div style="font-family:'DM Mono',monospace;font-size:10px;color:#aaa;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">
                        Reference Solution
                    </div>
                    <pre class="code-block">{{ $q->referenceSolution->solution_code }}</pre>
                @endif
            </div>
        @endforeach
    </div>
@endif
@endsection
