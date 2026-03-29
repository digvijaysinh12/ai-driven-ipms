@extends('layouts.intern')

@section('title', 'Solve Question')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

    .solve-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1px;
        background: #e5e5e5;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        overflow: hidden;
        height: calc(100vh - 120px);
    }

    /* Left: Question panel */
    .question-panel {
        background: #fff;
        padding: 28px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .q-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 16px;
        border-bottom: 1px solid #ebebeb;
    }

    .q-num {
        font-family: 'DM Mono', monospace;
        font-size: 11px; color: #aaa;
    }

    .q-type-badge {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        background: #f0f0f0; color: #555;
        padding: 2px 8px; border-radius: 2px;
        text-transform: uppercase; letter-spacing: 0.06em;
    }

    .back-link {
        font-family: 'DM Mono', monospace;
        font-size: 11px; color: #aaa;
        text-decoration: none;
        margin-left: auto;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .back-link:hover { color: #1a1a1a; }

    .q-statement {
        font-size: 14px;
        color: #1a1a1a;
        line-height: 1.7;
    }

    .code-snippet {
        background: #1a1a1a;
        color: #d4d4d4;
        padding: 16px 20px;
        border-radius: 2px;
        font-family: 'DM Mono', monospace;
        font-size: 13px;
        line-height: 1.6;
        overflow-x: auto;
    }

    /* MCQ options */
    .mcq-form { display: flex; flex-direction: column; gap: 8px; }

    .mcq-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border: 1px solid #e5e5e5;
        border-radius: 2px;
        cursor: pointer;
        font-size: 13.5px;
        transition: background 0.1s, border-color 0.1s;
    }

    .mcq-option:hover { background: #fafafa; border-color: #ccc; }

    .mcq-option input[type="radio"] { accent-color: #1a1a1a; }

    .option-label {
        font-family: 'DM Mono', monospace;
        font-size: 11px; color: #aaa;
        min-width: 16px;
    }

    /* Right: Editor panel */
    .editor-panel {
        background: #fff;
        display: flex;
        flex-direction: column;
    }

    .editor-topbar {
        padding: 12px 20px;
        border-bottom: 1px solid #ebebeb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .editor-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px;
        color: #888;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .copy-warning {
        font-family: 'DM Mono', monospace;
        font-size: 10px; color: #aaa;
        letter-spacing: 0.04em;
    }

    .editor-area {
        flex: 1;
        padding: 0;
        position: relative;
    }

    #code-editor {
        width: 100%;
        height: 100%;
        min-height: 300px;
        background: #fafafa;
        border: none;
        outline: none;
        padding: 20px;
        font-family: 'DM Mono', monospace;
        font-size: 13px;
        line-height: 1.7;
        color: #1a1a1a;
        resize: none;
        box-sizing: border-box;
    }

    /* For non-coding types: text answer */
    #text-answer {
        width: 100%;
        height: 100%;
        min-height: 200px;
        background: #fafafa;
        border: none;
        outline: none;
        padding: 20px;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        line-height: 1.7;
        color: #1a1a1a;
        resize: none;
    }

    .editor-footer {
        padding: 14px 20px;
        border-top: 1px solid #ebebeb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-submit {
        background: #1a1a1a;
        color: #fff; border: none;
        padding: 9px 24px;
        border-radius: 2px;
        font-size: 13px; font-weight: 500;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        transition: background 0.12s;
    }

    .btn-submit:hover { background: #333; }

    .btn-cancel {
        background: #fff;
        color: #888;
        border: 1px solid #d4d4d4;
        padding: 9px 20px;
        border-radius: 2px;
        font-size: 13px;
        text-decoration: none;
        font-family: 'DM Sans', sans-serif;
    }

    /* Submitted state */
    .submitted-panel {
        background: #fff;
        display: flex;
        flex-direction: column;
    }

    .submitted-topbar {
        padding: 12px 20px;
        border-bottom: 1px solid #ebebeb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .score-row {
        display: flex;
        gap: 1px;
        background: #e5e5e5;
        border-bottom: 1px solid #e5e5e5;
    }

    .score-cell {
        flex: 1;
        background: #fff;
        padding: 14px 20px;
    }

    .score-cell-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px; color: #aaa;
        letter-spacing: 0.06em; text-transform: uppercase;
        margin-bottom: 4px;
    }

    .score-cell-value {
        font-family: 'DM Mono', monospace;
        font-size: 18px; font-weight: 500;
        color: #1a1a1a;
    }

    .feedback-area {
        flex: 1; padding: 20px;
        overflow-y: auto;
    }

    .feedback-label {
        font-family: 'DM Mono', monospace;
        font-size: 10px; color: #aaa;
        letter-spacing: 0.08em; text-transform: uppercase;
        margin-bottom: 10px;
    }

    .feedback-text {
        font-size: 13.5px; color: #444;
        line-height: 1.7;
        background: #fafafa;
        border: 1px solid #ebebeb;
        border-radius: 2px;
        padding: 14px 16px;
    }

    .submitted-code {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
    }
</style>

<div class="solve-layout">

    {{-- LEFT: Question --}}
    <div class="question-panel">
        <div class="q-header">
            <span class="q-num">Q{{ $question->id }}</span>
            <span class="q-type-badge">{{ str_replace('_', ' ', $question->type) }}</span>
            <a href="{{ route('intern.topic.questions', $question->type) }}" class="back-link">← Back</a>
        </div>

        <div class="q-statement">{{ $question->problem_statement }}</div>

        @if($question->code)
            <pre class="code-snippet">{{ $question->code }}</pre>
        @endif

        @if($question->type == 'mcq' && !$submission)
            {{-- MCQ options shown in left panel, form wraps both panels --}}
            <div class="mcq-form" id="mcq-options">
                @if(isset($question->option_a))
                    <label class="mcq-option">
                        <input type="radio" name="mcq_answer" value="A" form="submit-form">
                        <span class="option-label">A</span>
                        {{ $question->option_a }}
                    </label>
                @endif
                @if(isset($question->option_b))
                    <label class="mcq-option">
                        <input type="radio" name="mcq_answer" value="B" form="submit-form">
                        <span class="option-label">B</span>
                        {{ $question->option_b }}
                    </label>
                @endif
                @if(isset($question->option_c))
                    <label class="mcq-option">
                        <input type="radio" name="mcq_answer" value="C" form="submit-form">
                        <span class="option-label">C</span>
                        {{ $question->option_c }}
                    </label>
                @endif
                @if(isset($question->option_d))
                    <label class="mcq-option">
                        <input type="radio" name="mcq_answer" value="D" form="submit-form">
                        <span class="option-label">D</span>
                        {{ $question->option_d }}
                    </label>
                @endif
            </div>
        @endif
    </div>

    {{-- RIGHT: Editor or Submission view --}}
    @if($submission)
        {{-- Already submitted — show result --}}
        <div class="submitted-panel">
            <div class="submitted-topbar">
                <span class="editor-label">Your Submission</span>
                <span class="editor-label">
                    Status: {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                </span>
            </div>

            <div class="score-row">
                <div class="score-cell">
                    <div class="score-cell-label">AI Score</div>
                    <div class="score-cell-value">{{ $submission->ai_total_score ?? '—' }}</div>
                </div>
                <div class="score-cell">
                    <div class="score-cell-label">Mentor Score</div>
                    <div class="score-cell-value">{{ $submission->mentor_override_score ?? '—' }}</div>
                </div>
                <div class="score-cell">
                    <div class="score-cell-label">Final Score</div>
                    <div class="score-cell-value">{{ $submission->final_score ?? '—' }}</div>
                </div>
            </div>

            <div class="submitted-code">
                <div class="feedback-label">Your Answer</div>
                <pre class="code-snippet" style="margin-bottom:16px;">{{ $submission->submitted_code }}</pre>

                @if($submission->feedback)
                    <div class="feedback-label" style="margin-top:16px;">Feedback</div>
                    <div class="feedback-text">{{ $submission->feedback }}</div>
                @endif
            </div>
        </div>

    @else
        {{-- Not yet submitted — show editor --}}
        <form id="submit-form"
              method="POST"
              action="{{ route('intern.submit', $question->id) }}"
              class="editor-panel">
            @csrf

            <div class="editor-topbar">
                <span class="editor-label">
                    @if(in_array($question->type, ['coding','output']))
                        Code Editor
                    @else
                        Your Answer
                    @endif
                </span>
                @if(in_array($question->type, ['coding','output']))
                    <span class="copy-warning">Copy–paste disabled</span>
                @endif
            </div>

            <div class="editor-area">
                @if(in_array($question->type, ['coding','output']))
                    <textarea
                        id="code-editor"
                        name="submitted_code"
                        placeholder="Write your code here..."
                        required
                        spellcheck="false"
                        autocomplete="off"></textarea>
                @elseif($question->type == 'mcq')
                    {{-- MCQ answer is radio in left panel, hidden field here --}}
                    <textarea
                        id="text-answer"
                        name="submitted_code"
                        placeholder="Selected option will appear here..."
                        readonly
                        style="cursor:default; color:#888;"></textarea>
                @else
                    <textarea
                        id="text-answer"
                        name="submitted_code"
                        placeholder="Type your answer here..."
                        required></textarea>
                @endif
            </div>

            <div class="editor-footer">
                <a href="{{ route('intern.topic.questions', $question->type) }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Submit Answer</button>
            </div>
        </form>
    @endif

</div>

<script>
    // Disable copy-paste on code editor
    const editor = document.getElementById('code-editor');
    if (editor) {
        editor.addEventListener('paste', e => e.preventDefault());
        editor.addEventListener('copy', e => e.preventDefault());
        editor.addEventListener('cut', e => e.preventDefault());
    }

    // For MCQ: sync radio selection to hidden textarea
    const radios = document.querySelectorAll('input[name="mcq_answer"]');
    const textAnswer = document.getElementById('text-answer');
    if (radios.length && textAnswer) {
        radios.forEach(r => {
            r.addEventListener('change', () => {
                textAnswer.value = r.value;
            });
        });
    }
</script>

@endsection