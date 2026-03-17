<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst(str_replace('_',' ',$type)) }} Exercise · {{ $topic->title }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- jQuery --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    {{-- CodeMirror for code editor --}}
    <link  rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/codemirror.min.css">
    <link  rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/theme/dracula.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/edit/closebrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/edit/matchbrackets.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #0f0f0f;
            color: #e8e8e8;
            height: 100vh;
            overflow: hidden;
            user-select: none;
            -webkit-user-select: none;
        }

        /* ── Exam shell ── */
        .exam-shell {
            display: grid;
            grid-template-rows: 52px 1fr;
            height: 100vh;
        }

        /* ── Top bar ── */
        .exam-topbar {
            background: #1a1a1a;
            border-bottom: 1px solid #2a2a2a;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            gap: 20px;
        }

        .exam-topic {
            font-size: 13px;
            font-weight: 500;
            color: #e8e8e8;
            letter-spacing: -0.01em;
        }

        .exam-type-badge {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            color: #888;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: #2a2a2a;
            padding: 3px 10px;
            border-radius: 2px;
        }

        /* Question pill nav */
        .q-pills {
            display: flex;
            gap: 4px;
            flex: 1;
            justify-content: center;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .q-pills::-webkit-scrollbar { display: none; }

        .q-pill {
            width: 28px; height: 28px;
            border-radius: 2px;
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            border: 1px solid #2a2a2a;
            background: #1a1a1a;
            color: #666;
            transition: all 0.12s;
            flex-shrink: 0;
        }

        .q-pill:hover  { background: #2a2a2a; color: #aaa; }
        .q-pill.active { background: #e8e8e8; color: #1a1a1a; border-color: #e8e8e8; }
        .q-pill.answered { background: #1a3a1a; color: #4a9a4a; border-color: #2a5a2a; }
        .q-pill.answered.active { background: #4a9a4a; color: #fff; border-color: #4a9a4a; }

        .exam-exit-link {
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            color: #555;
            text-decoration: none;
            letter-spacing: 0.06em;
            white-space: nowrap;
            transition: color 0.12s;
        }

        .exam-exit-link:hover { color: #aaa; }

        /* ── Main area ── */
        .exam-main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100%;
            overflow: hidden;
        }

        /* ── Left: Question ── */
        .q-panel {
            background: #141414;
            border-right: 1px solid #242424;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .q-panel-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid #242424;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .q-index {
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            color: #555;
        }

        .q-type-tag {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            color: #666;
            background: #222;
            padding: 2px 8px;
            border-radius: 2px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .q-body {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            scrollbar-width: thin;
            scrollbar-color: #2a2a2a #141414;
        }

        .q-statement {
            font-size: 15px;
            color: #e8e8e8;
            line-height: 1.75;
            margin-bottom: 20px;
            user-select: none;
        }

        .code-snippet {
            background: #0a0a0a;
            border: 1px solid #2a2a2a;
            padding: 16px 20px;
            border-radius: 2px;
            font-family: 'DM Mono', monospace;
            font-size: 13px;
            line-height: 1.65;
            color: #c8c8c8;
            overflow-x: auto;
            margin-bottom: 20px;
            user-select: none;
        }

        /* MCQ */
        .mcq-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .mcq-label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 16px;
            border: 1px solid #2a2a2a;
            border-radius: 2px;
            cursor: pointer;
            font-size: 14px;
            color: #ccc;
            line-height: 1.5;
            transition: background 0.1s, border-color 0.1s;
        }

        .mcq-label:hover { background: #1e1e1e; border-color: #3a3a3a; }

        .mcq-label input[type="radio"] { display: none; }

        .mcq-label.selected {
            background: #1a2a1a;
            border-color: #3a7a3a;
            color: #e8e8e8;
        }

        .option-key {
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            color: #555;
            min-width: 18px;
            padding-top: 1px;
        }

        .mcq-label.selected .option-key { color: #4a9a4a; }

        /* True/False */
        .tf-options {
            display: flex;
            gap: 10px;
        }

        .tf-btn {
            flex: 1;
            padding: 14px;
            border: 1px solid #2a2a2a;
            border-radius: 2px;
            background: #1a1a1a;
            color: #888;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            text-align: center;
            transition: all 0.12s;
        }

        .tf-btn:hover { background: #222; color: #ccc; border-color: #3a3a3a; }
        .tf-btn.selected { background: #1a2a1a; border-color: #3a7a3a; color: #4a9a4a; }

        /* Text answer */
        .text-input {
            width: 100%;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 2px;
            padding: 14px 16px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: #e8e8e8;
            outline: none;
            resize: none;
            min-height: 100px;
            transition: border-color 0.12s;
            line-height: 1.6;
        }

        .text-input:focus { border-color: #3a3a3a; }

        /* Already answered banner */
        .answered-banner {
            background: #1a2a1a;
            border: 1px solid #2a5a2a;
            border-radius: 2px;
            padding: 10px 14px;
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            color: #4a9a4a;
            letter-spacing: 0.04em;
            margin-bottom: 14px;
        }

        /* ── Nav footer ── */
        .q-footer {
            padding: 14px 24px;
            border-top: 1px solid #242424;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-btn {
            background: #2a2a2a;
            color: #aaa;
            border: none;
            padding: 9px 20px;
            border-radius: 2px;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: background 0.12s, color 0.12s;
        }

        .nav-btn:hover { background: #3a3a3a; color: #e8e8e8; }
        .nav-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        .save-btn {
            background: #e8e8e8;
            color: #1a1a1a;
            border: none;
            padding: 9px 24px;
            border-radius: 2px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: background 0.12s;
        }

        .save-btn:hover { background: #fff; }
        .save-btn.saving { opacity: 0.6; }

        /* ── Right: Code editor / output ── */
        .editor-panel {
            background: #0f0f0f;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .editor-topbar {
            background: #1a1a1a;
            border-bottom: 1px solid #242424;
            padding: 0 16px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .editor-tabs {
            display: flex;
            gap: 0;
        }

        .editor-tab {
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            color: #555;
            padding: 0 14px;
            height: 40px;
            display: flex;
            align-items: center;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            letter-spacing: 0.04em;
            transition: color 0.12s;
        }

        .editor-tab.active { color: #e8e8e8; border-bottom-color: #e8e8e8; }

        .run-btn {
            background: #2a3a2a;
            color: #4aaa4a;
            border: 1px solid #2a5a2a;
            padding: 5px 16px;
            border-radius: 2px;
            font-size: 12px;
            font-family: 'DM Mono', monospace;
            cursor: pointer;
            letter-spacing: 0.04em;
            transition: background 0.12s;
        }

        .run-btn:hover { background: #1a4a1a; }

        /* CodeMirror overrides */
        .CodeMirror {
            height: 100%;
            font-family: 'DM Mono', monospace;
            font-size: 13.5px;
            line-height: 1.65;
            background: #0f0f0f !important;
        }

        .CodeMirror-scroll { padding-bottom: 20px; }

        .editor-cm-wrap {
            flex: 1;
            overflow: hidden;
            position: relative;
        }

        /* Output pane */
        .output-pane {
            flex: 1;
            background: #0a0a0a;
            display: none;
            flex-direction: column;
        }

        .output-pane.visible { display: flex; }

        .output-header {
            padding: 10px 16px;
            border-bottom: 1px solid #1a1a1a;
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            color: #555;
            letter-spacing: 0.08em;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .output-clear {
            background: none;
            border: none;
            color: #444;
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            cursor: pointer;
            letter-spacing: 0.06em;
            transition: color 0.12s;
        }

        .output-clear:hover { color: #888; }

        #output-content {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            font-family: 'DM Mono', monospace;
            font-size: 13px;
            line-height: 1.7;
            color: #d4d4d4;
            scrollbar-width: thin;
            scrollbar-color: #2a2a2a #0a0a0a;
            white-space: pre-wrap;
        }

        .output-error { color: #e87070; }

        /* ── Non-coding answer panel (blank/output) ── */
        .plain-answer-panel {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .answer-label {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            color: #555;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        /* Hide scrollbars on panel body for clean look */
        .q-body::-webkit-scrollbar { width: 4px; }
        .q-body::-webkit-scrollbar-track { background: #141414; }
        .q-body::-webkit-scrollbar-thumb { background: #2a2a2a; }
    </style>
</head>
<body>

<div class="exam-shell">

    {{-- ── Top bar ── --}}
    <header class="exam-topbar">
        <div style="display:flex;align-items:center;gap:10px;min-width:140px;">
            <span class="exam-topic">{{ $topic->title }}</span>
            <span class="exam-type-badge">{{ str_replace('_',' ',$type) }}</span>
        </div>

        {{-- Question pill nav --}}
        <div class="q-pills" id="q-pills">
            @foreach($questions as $i => $q)
                <div class="q-pill {{ $answeredMap[$q->id] ? 'answered' : '' }} {{ $i === 0 ? 'active' : '' }}"
                     data-index="{{ $i }}">
                    {{ $i + 1 }}
                </div>
            @endforeach
        </div>

        <a href="{{ route('intern.topic') }}" class="exam-exit-link">← Back to Topic</a>
    </header>

    {{-- ── Main ── --}}
    <div class="exam-main">

        {{-- LEFT: Question panel --}}
        <div class="q-panel">
            <div class="q-panel-header">
                <span class="q-index" id="q-index-label">Q1 / {{ count($questions) }}</span>
                <span class="q-type-tag" id="q-type-label">{{ str_replace('_',' ',$type) }}</span>
            </div>

            <div class="q-body" id="q-body">
                {{-- Rendered by JS --}}
            </div>

            <div class="q-footer">
                <button class="nav-btn" id="btn-prev" onclick="goTo(currentIndex - 1)">← Prev</button>

                <button class="save-btn" id="btn-save" onclick="saveCurrentAnswer()">
                    Save Answer
                </button>

                <button class="nav-btn" id="btn-next" onclick="goTo(currentIndex + 1)">Next →</button>
            </div>
        </div>

        {{-- RIGHT: Editor / Answer panel --}}
        <div class="editor-panel" id="editor-panel">

            @if($type === 'coding')
            {{-- Code editor with tabs --}}
            <div class="editor-topbar">
                <div class="editor-tabs">
                    <div class="editor-tab active" data-tab="editor">editor.php</div>
                    <div class="editor-tab" id="tab-output" data-tab="output">output</div>
                </div>
                <button class="run-btn" id="run-btn">▶ Run</button>
            </div>

            <div class="editor-cm-wrap" id="cm-wrap">
                {{-- CodeMirror mounts here --}}
            </div>

            <div class="output-pane" id="output-pane">
                <div class="output-header">
                    <span>OUTPUT</span>
                    <button class="output-clear" id="btn-clear-output">clear</button>
                </div>
                <div id="output-content"></div>
            </div>

            @elseif($type === 'output')
            {{-- Output type: read-only code on left, answer text on right --}}
            <div class="plain-answer-panel">
                <div class="answer-label">Your Answer</div>
                <textarea
                    class="text-input"
                    id="plain-answer"
                    placeholder="Type the exact output this code will produce..."
                    rows="5"
                    autocomplete="off"
                    spellcheck="false"></textarea>
                <div style="font-family:'DM Mono',monospace;font-size:10px;color:#444;margin-top:4px;">
                    Tip: Match spacing, capitalization and line breaks exactly
                </div>
            </div>

            @elseif($type === 'blank')
            <div class="plain-answer-panel">
                <div class="answer-label">Fill in the Blank</div>
                <textarea
                    class="text-input"
                    id="plain-answer"
                    placeholder="Type the word or phrase that fills the blank..."
                    rows="4"
                    autocomplete="off"
                    spellcheck="false"></textarea>
            </div>

            @else
            {{-- MCQ and true_false: answer captured in left panel --}}
            <div class="plain-answer-panel" style="justify-content:center;align-items:center;text-align:center;">
                <div style="font-family:'DM Mono',monospace;font-size:11px;color:#444;line-height:1.8;">
                    SELECT YOUR ANSWER<br>ON THE LEFT PANEL
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

@php
    $questionsJson = $questions->map(function($q) {
        return [
            'id'                => $q->id,
            'type'              => $q->type,
            'problem_statement' => $q->problem_statement,
            'code'              => $q->code,
            'option_a'          => $q->option_a,
            'option_b'          => $q->option_b,
            'option_c'          => $q->option_c,
            'option_d'          => $q->option_d,
        ];
    })->values();
@endphp

{{-- ── Question data passed to JS ── --}}
<script>
const QUESTIONS  = @json($questionsJson);
const ANSWERED   = @json($answeredMap);
const ANSWERS    = @json($savedAnswers);
const EXAM_TYPE  = "{{ $type }}";
const SUBMIT_URL = "{{ route('intern.exam.save') }}";
const RUN_URL    = "{{ route('intern.run.code') }}";

let currentIndex = 0;
let localAnswers = $.extend({}, ANSWERS); // in-memory answers keyed by question id
let cmEditor     = null;

// ── Boot ──────────────────────────────────────────────────────────────────
$(function () {

    // Set up jQuery AJAX defaults — CSRF header on every request
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
        }
    });

    // Init CodeMirror for coding type
    if (EXAM_TYPE === 'coding') {
        cmEditor = CodeMirror($('#cm-wrap')[0], {
            mode: 'application/x-httpd-php',
            theme: 'dracula',
            lineNumbers: true,
            autoCloseBrackets: true,
            matchBrackets: true,
            indentUnit: 4,
            tabSize: 4,
            indentWithTabs: false,
            value: '<?php\n\n',
            extraKeys: { 'Tab': cm => cm.replaceSelection('    ', 'end') }
        });

        $('#cm-wrap').css('position', 'relative');
        cmEditor.setSize('100%', '100%');

        // Block paste inside CodeMirror
        cmEditor.on('paste', function (cm, e) {
            e.preventDefault();
            showPasteWarning();
        });
    }

    goTo(0);
    setupAntiCheat();

    // Nav button click handlers
    $('#btn-prev').on('click', () => goTo(currentIndex - 1));
    $('#btn-next').on('click', () => goTo(currentIndex + 1));
    $('#btn-save').on('click', saveCurrentAnswer);

    // Question pill navigation
    $('#q-pills').on('click', '.q-pill', function () {
        goTo(parseInt($(this).data('index')));
    });

    // Editor tab switching
    $('.editor-tabs').on('click', '.editor-tab', function () {
        switchTab($(this).data('tab'));
    });

    // Run code button
    $('#run-btn').on('click', runCode);

    // Clear output button
    $('#btn-clear-output').on('click', clearOutput);
});

// ── Navigate to question index ──────────────────────────────────────────
function goTo(index) {
    if (index < 0 || index >= QUESTIONS.length) return;

    if (currentIndex !== index) collectCurrentAnswer();

    currentIndex = index;
    renderQuestion(QUESTIONS[index]);

    // Update pills
    $('.q-pill').each(function (i) {
        $(this).toggleClass('active', i === index);
    });

    // Scroll active pill into view
    const $activePill = $(`.q-pill[data-index="${index}"]`);
    if ($activePill.length) {
        $activePill[0].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }

    // Prev / Next button states
    $('#btn-prev').prop('disabled', index === 0);
    $('#btn-next').prop('disabled', index === QUESTIONS.length - 1);

    // Header counter
    $('#q-index-label').text(`Q${index + 1} / ${QUESTIONS.length}`);
}

// ── Render question in left panel ───────────────────────────────────────
function renderQuestion(q) {
    const $body  = $('#q-body');
    const saved  = localAnswers[q.id];
    let   html   = '';

    // Already-answered banner
    if (ANSWERED[q.id]) {
        html += `<div class="answered-banner">✓ Answer saved</div>`;
    }

    html += `<div class="q-statement">${escHtml(q.problem_statement)}</div>`;

    if (q.code) {
        html += `<pre class="code-snippet">${escHtml(q.code)}</pre>`;
    }

    if (q.type === 'mcq') {
        const opts = [
            { key: 'A', val: q.option_a },
            { key: 'B', val: q.option_b },
            { key: 'C', val: q.option_c },
            { key: 'D', val: q.option_d },
        ].filter(o => o.val);

        html += `<div class="mcq-options">`;
        $.each(opts, function (i, o) {
            const sel = saved === o.key ? 'selected' : '';
            html += `
            <label class="mcq-label ${sel}" data-key="${o.key}">
                <input type="radio" name="mcq" value="${o.key}" ${saved === o.key ? 'checked' : ''}>
                <span class="option-key">${o.key}</span>
                <span>${escHtml(o.val)}</span>
            </label>`;
        });
        html += `</div>`;

    } else if (q.type === 'true_false') {
        const tSel = saved === 'True'  ? 'selected' : '';
        const fSel = saved === 'False' ? 'selected' : '';
        html += `
        <div class="tf-options">
            <button class="tf-btn ${tSel}" data-val="True">True</button>
            <button class="tf-btn ${fSel}" data-val="False">False</button>
        </div>`;
    }

    $body.html(html);

    // Delegate MCQ click — inside q-body (re-rendered each time)
    $body.off('click', '.mcq-label').on('click', '.mcq-label', function () {
        const key = $(this).data('key');
        $('.mcq-label').removeClass('selected');
        $(this).addClass('selected');
        $('input[name="mcq"]', this).prop('checked', true);
        localAnswers[QUESTIONS[currentIndex].id] = key;
    });

    // Delegate True/False click
    $body.off('click', '.tf-btn').on('click', '.tf-btn', function () {
        const val = $(this).data('val');
        $('.tf-btn').removeClass('selected');
        $(this).addClass('selected');
        localAnswers[QUESTIONS[currentIndex].id] = val;
    });

    // Restore CodeMirror / plain textarea
    if (q.type === 'coding' && cmEditor) {
        cmEditor.setValue(saved || '<?php\n\n');
        cmEditor.clearHistory();
        setTimeout(() => cmEditor.refresh(), 50);

    } else if ($.inArray(q.type, ['blank', 'output']) !== -1) {
        $('#plain-answer').val(saved || '');
    }
}

// ── Collect current answer from DOM ─────────────────────────────────────
function collectCurrentAnswer() {
    const q = QUESTIONS[currentIndex];
    let answer = null;

    if (q.type === 'mcq') {
        const val = $('input[name="mcq"]:checked').val();
        answer = val || null;

    } else if (q.type === 'true_false') {
        const $sel = $('.tf-btn.selected');
        answer = $sel.length ? $sel.data('val') : null;

    } else if (q.type === 'coding' && cmEditor) {
        answer = cmEditor.getValue();

    } else {
        const val = $('#plain-answer').val().trim();
        answer = val || null;
    }

    if (answer) localAnswers[q.id] = answer;
    return answer;
}

// ── Save answer to server via jQuery AJAX ───────────────────────────────
function saveCurrentAnswer() {
    const answer = collectCurrentAnswer();
    const q      = QUESTIONS[currentIndex];

    if (!answer) {
        flashSaveBtn('No answer yet');
        return;
    }

    const $btn = $('#btn-save');
    $btn.text('Saving...').addClass('saving');

    $.ajax({
        url:         SUBMIT_URL,
        type:        'POST',
        contentType: 'application/json',
        data:        JSON.stringify({ question_id: q.id, submitted_code: answer }),
        success: function (data) {
            if (data.ok) {
                ANSWERED[q.id] = true;

                // Mark pill green
                $(`.q-pill[data-index="${currentIndex}"]`).addClass('answered');

                flashSaveBtn('Saved ✓');
                renderQuestion(q); // re-render to show answered banner
            } else {
                flashSaveBtn('Error — try again');
            }
        },
        error: function () {
            flashSaveBtn('Error — try again');
        }
    });
}

function flashSaveBtn(msg) {
    const $btn = $('#btn-save');
    $btn.text(msg).removeClass('saving');
    setTimeout(() => $btn.text('Save Answer'), 2000);
}

// ── Tab switch (editor ↔ output) ─────────────────────────────────────────
function switchTab(tab) {
    if (tab === 'editor') {
        $('#cm-wrap').show();
        $('#output-pane').removeClass('visible');
        $('.editor-tab').eq(0).addClass('active');
        $('.editor-tab').eq(1).removeClass('active');
        setTimeout(() => cmEditor && cmEditor.refresh(), 10);
    } else {
        $('#cm-wrap').hide();
        $('#output-pane').addClass('visible');
        $('.editor-tab').eq(0).removeClass('active');
        $('.editor-tab').eq(1).addClass('active');
    }
}

// ── Run PHP code via jQuery AJAX ─────────────────────────────────────────
function runCode() {
    if (!cmEditor) return;
    const code = cmEditor.getValue();

    switchTab('output');
    $('#output-content').html('<span style="color:#555;">Running...</span>');

    $.ajax({
        url:         RUN_URL,
        type:        'POST',
        contentType: 'application/json',
        data:        JSON.stringify({ code }),
        success: function (data) {
            if (data.error) {
                $('#output-content').html(`<span class="output-error">${escHtml(data.error)}</span>`);
            } else {
                $('#output-content').text(data.output || '(no output)');
            }
        },
        error: function () {
            $('#output-content').html('<span class="output-error">Could not connect to runner.</span>');
        }
    });
}

function clearOutput() {
    $('#output-content').empty();
}

// ── Minimal restrictions (exercise mode — copy/paste allowed) ────────────
function setupAntiCheat() {
    // Right-click disabled to prevent "view source" inspection during exercise
    $(document).on('contextmenu', e => e.preventDefault());
}

function showPasteWarning() {} // no-op in exercise mode

// ── Utility ───────────────────────────────────────────────────────────────
function escHtml(str) {
    if (!str) return '';
    return $('<div>').text(str).html(); // jQuery's native HTML-escape
}
</script>

</body>
</html>