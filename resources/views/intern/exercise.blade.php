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

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--gray-900);
            color: var(--gray-100);
            height: 100vh;
            overflow: hidden;
            user-select: none;
            -webkit-user-select: none;
        }

        /* ── Exam shell ── */
        .exam-shell {
            display: grid;
            grid-template-rows: 60px 1fr;
            height: 100vh;
        }

        /* ── Top bar ── */
        .exam-topbar {
            background: var(--gray-800);
            border-bottom: 1px solid var(--gray-700);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            gap: 1.5rem;
            box-shadow: var(--shadow);
        }

        .exam-topic {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-100);
            letter-spacing: -0.025em;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .exam-type-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--gray-400);
            background: var(--gray-700);
            padding: 0.25rem 0.75rem;
            border-radius: var(--border-radius);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 500;
        }

        /* Question pill nav */
        .q-pills {
            display: flex;
            gap: 0.5rem;
            flex: 1;
            justify-content: center;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .q-pills::-webkit-scrollbar {
            display: none;
        }

        .q-pill {
            width: 2rem;
            height: 2rem;
            border-radius: var(--border-radius);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 1px solid var(--gray-600);
            background: var(--gray-700);
            color: var(--gray-400);
            transition: all 0.2s ease;
            flex-shrink: 0;
            position: relative;
        }

        .q-pill:hover {
            background: var(--gray-600);
            color: var(--gray-200);
            border-color: var(--gray-500);
        }

        .q-pill.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            box-shadow: var(--shadow);
        }

        .q-pill.answered {
            background: var(--success-color);
            color: white;
            border-color: var(--success-color);
        }

        .q-pill.answered.active {
            background: #059669;
            border-color: #059669;
        }

        .exam-exit-link {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--gray-400);
            text-decoration: none;
            letter-spacing: 0.05em;
            white-space: nowrap;
            transition: color 0.2s ease;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--gray-600);
        }

        .exam-exit-link:hover {
            color: var(--gray-200);
            background: var(--gray-700);
            border-color: var(--gray-500);
        }

        /* ── Main area ── */
        .exam-main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100%;
            overflow: hidden;
        }

        /* ── Left: Question ── */
        .q-panel {
            background: var(--gray-800);
            border-right: 1px solid var(--gray-700);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .q-panel-header {
            padding: 1.5rem 2rem 1rem;
            border-bottom: 1px solid var(--gray-700);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .q-index {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.875rem;
            color: var(--gray-400);
            font-weight: 500;
        }

        .q-type-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--gray-300);
            background: var(--gray-700);
            padding: 0.25rem 0.75rem;
            border-radius: var(--border-radius);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 500;
        }

        .q-body {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            scrollbar-width: thin;
            scrollbar-color: var(--gray-600) var(--gray-800);
        }

        .q-body::-webkit-scrollbar {
            width: 6px;
        }

        .q-body::-webkit-scrollbar-track {
            background: var(--gray-800);
        }

        .q-body::-webkit-scrollbar-thumb {
            background: var(--gray-600);
            border-radius: 3px;
        }

        .q-body::-webkit-scrollbar-thumb:hover {
            background: var(--gray-500);
        }

        .q-statement {
            font-size: 1.125rem;
            color: var(--gray-100);
            line-height: 1.75;
            margin-bottom: 1.5rem;
            user-select: none;
        }

        .code-snippet {
            background: var(--gray-900);
            border: 1px solid var(--gray-700);
            padding: 1.25rem 1.5rem;
            border-radius: var(--border-radius);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.875rem;
            line-height: 1.6;
            color: var(--gray-200);
            overflow-x: auto;
            margin-bottom: 1.5rem;
            user-select: none;
            box-shadow: var(--shadow);
        }

        /* MCQ */
        .mcq-options {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .mcq-label {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border: 1px solid var(--gray-600);
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            color: var(--gray-300);
            line-height: 1.5;
            transition: all 0.2s ease;
            background: var(--gray-700);
        }

        .mcq-label:hover {
            background: var(--gray-600);
            border-color: var(--gray-500);
            color: var(--gray-100);
        }

        .mcq-label input[type="radio"] {
            display: none;
        }

        .mcq-label.selected {
            background: rgba(16, 185, 129, 0.1);
            border-color: var(--success-color);
            color: var(--gray-100);
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }

        .option-key {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.875rem;
            color: var(--gray-400);
            min-width: 1.25rem;
            padding-top: 0.125rem;
            font-weight: 600;
        }

        .mcq-label.selected .option-key {
            color: var(--success-color);
        }

        /* True/False */
        .tf-options {
            display: flex;
            gap: 1rem;
        }

        .tf-btn {
            flex: 1;
            padding: 1rem 1.5rem;
            border: 1px solid var(--gray-600);
            border-radius: var(--border-radius);
            background: var(--gray-700);
            color: var(--gray-300);
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s ease;
        }

        .tf-btn:hover {
            background: var(--gray-600);
            color: var(--gray-100);
            border-color: var(--gray-500);
        }

        .tf-btn.selected {
            background: rgba(16, 185, 129, 0.1);
            border-color: var(--success-color);
            color: var(--success-color);
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }

        /* Text answer */
        .text-input {
            width: 100%;
            background: var(--gray-700);
            border: 1px solid var(--gray-600);
            border-radius: var(--border-radius);
            padding: 1rem 1.25rem;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            color: var(--gray-100);
            outline: none;
            resize: vertical;
            min-height: 120px;
            transition: border-color 0.2s ease;
            line-height: 1.6;
        }

        .text-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .text-input::placeholder {
            color: var(--gray-500);
        }

        /* Already answered banner */
        .answered-banner {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success-color);
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--success-color);
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        /* ── Nav footer ── */
        .q-footer {
            padding: 1rem 2rem;
            border-top: 1px solid var(--gray-700);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--gray-800);
        }

        .nav-btn {
            background: var(--gray-700);
            color: var(--gray-300);
            border: 1px solid var(--gray-600);
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .nav-btn:hover {
            background: var(--gray-600);
            color: var(--gray-100);
            border-color: var(--gray-500);
        }

        .nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: var(--gray-800);
            color: var(--gray-500);
        }

        .save-btn {
            background: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
            padding: 0.75rem 2rem;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow);
        }

        .save-btn:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .save-btn.saving {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* ── Right: Code editor / output ── */
        .editor-panel {
            background: var(--gray-900);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .editor-topbar {
            background: var(--gray-800);
            border-bottom: 1px solid var(--gray-700);
            padding: 0 1.25rem;
            height: 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .editor-tabs {
            display: flex;
            gap: 0;
        }

        .editor-tab {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--gray-400);
            padding: 0 1rem;
            height: 3rem;
            display: flex;
            align-items: center;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            letter-spacing: 0.05em;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .editor-tab.active {
            color: var(--gray-100);
            border-bottom-color: var(--primary-color);
        }

        .editor-tab:hover {
            color: var(--gray-200);
        }

        .run-btn {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
            padding: 0.375rem 1rem;
            border-radius: var(--border-radius);
            font-size: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 500;
            cursor: pointer;
            letter-spacing: 0.05em;
            transition: all 0.2s ease;
        }

        .run-btn:hover {
            background: var(--success-color);
            color: white;
        }

        /* CodeMirror overrides */
        .CodeMirror {
            height: 100%;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.875rem;
            line-height: 1.6;
            background: var(--gray-900) !important;
        }

        .CodeMirror-scroll {
            padding-bottom: 1.25rem;
        }

        .CodeMirror-cursor {
            border-left: 2px solid var(--primary-color) !important;
        }

        .CodeMirror-gutters {
            background: var(--gray-800) !important;
            border-right: 1px solid var(--gray-700) !important;
        }

        .CodeMirror-linenumber {
            color: var(--gray-500) !important;
        }

        .editor-cm-wrap {
            flex: 1;
            overflow: hidden;
            position: relative;
        }

        /* Output pane */
        .output-pane {
            flex: 1;
            background: var(--gray-900);
            display: none;
            flex-direction: column;
        }

        .output-pane.visible {
            display: flex;
        }

        .output-header {
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid var(--gray-700);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--gray-400);
            letter-spacing: 0.05em;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 500;
        }

        .output-clear {
            background: none;
            border: none;
            color: var(--gray-500);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            cursor: pointer;
            letter-spacing: 0.05em;
            transition: color 0.2s ease;
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius);
        }

        .output-clear:hover {
            color: var(--gray-300);
            background: var(--gray-800);
        }

        #output-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.25rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.875rem;
            line-height: 1.6;
            color: var(--gray-200);
            scrollbar-width: thin;
            scrollbar-color: var(--gray-600) var(--gray-900);
            white-space: pre-wrap;
        }

        #output-content::-webkit-scrollbar {
            width: 6px;
        }

        #output-content::-webkit-scrollbar-track {
            background: var(--gray-900);
        }

        #output-content::-webkit-scrollbar-thumb {
            background: var(--gray-600);
            border-radius: 3px;
        }

        #output-content::-webkit-scrollbar-thumb:hover {
            background: var(--gray-500);
        }

        .output-error {
            color: var(--danger-color);
        }

        /* ── Non-coding answer panel (blank/output) ── */
        .plain-answer-panel {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .answer-label {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: var(--gray-400);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-weight: 500;
        }

        /* Responsive design */
        @media (max-width: 1024px) {
            .exam-main {
                grid-template-columns: 1fr;
                grid-template-rows: 1fr 1fr;
            }

            .q-panel {
                border-right: none;
                border-bottom: 1px solid var(--gray-700);
            }
        }

        @media (max-width: 768px) {
            .exam-topbar {
                padding: 0 1rem;
                flex-direction: column;
                gap: 1rem;
                height: auto;
                padding-top: 1rem;
                padding-bottom: 1rem;
            }

            .exam-topic {
                max-width: none;
            }

            .q-pills {
                order: 3;
                width: 100%;
                justify-content: flex-start;
                overflow-x: auto;
            }

            .q-panel-header,
            .q-body,
            .q-footer {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .editor-topbar {
                padding: 0 1rem;
            }

            .plain-answer-panel {
                padding: 1rem;
            }
        }
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
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;color:#444;margin-top:4px;">
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
                <div style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#444;line-height:1.8;">
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