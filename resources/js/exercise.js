/**
 * exercise.js
 * Handles: Monaco editor init, copy-paste block, MCQ sync, auto-save draft
 */
document.addEventListener('DOMContentLoaded', function () {

    const csrfToken    = document.querySelector('meta[name="csrf-token"]').content;
    const questionType = document.getElementById('question-type')?.value ?? '';
    const assignmentId = document.getElementById('assignment-id')?.value ?? '';
    const questionId   = document.getElementById('question-id')?.value ?? '';

    // ── Monaco Editor (coding / output questions) ──
    if (document.getElementById('monaco-container') && window.monaco) {
        const editor = monaco.editor.create(document.getElementById('monaco-container'), {
            value: '',
            language: 'php',
            theme: 'vs',
            fontSize: 13,
            fontFamily: 'DM Mono, monospace',
            minimap: { enabled: false },
            lineNumbers: 'on',
            scrollBeyondLastLine: false,
            automaticLayout: true,
        });

        // Sync Monaco value to hidden input on submit
        document.getElementById('submit-form')?.addEventListener('submit', function () {
            document.getElementById('submitted_code').value = editor.getValue();
        });

        // Block copy-paste context menu
        editor.onContextMenu(() => false);
        document.getElementById('monaco-container').addEventListener('paste', e => e.preventDefault());

        // Auto-save draft every 60 seconds
        if (assignmentId && questionId) {
            setInterval(async () => {
                const code = editor.getValue();
                if (!code.trim()) return;
                await fetch(`/intern/exercise/${assignmentId}/draft`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ question_id: questionId, code })
                });
            }, 60000);
        }
    }

    // ── Plain textarea (non-coding questions) ──
    const plainEditor = document.getElementById('code-editor');
    if (plainEditor) {
        plainEditor.addEventListener('paste', e => e.preventDefault());
        plainEditor.addEventListener('copy',  e => e.preventDefault());
        plainEditor.addEventListener('cut',   e => e.preventDefault());
    }

    // ── MCQ: sync radio selection to hidden textarea ──
    const radios     = document.querySelectorAll('input[name="mcq_answer"]');
    const textAnswer = document.getElementById('text-answer');

    if (radios.length && textAnswer) {
        radios.forEach(r => {
            r.addEventListener('change', () => {
                textAnswer.value = r.value;
            });
        });
    }

    // ── Deadline countdown timer ──
    const deadlineEl = document.getElementById('deadline-countdown');
    if (deadlineEl && deadlineEl.dataset.deadline) {
        const deadline = new Date(deadlineEl.dataset.deadline);
        function updateCountdown() {
            const diff = deadline - Date.now();
            if (diff <= 0) { deadlineEl.textContent = 'Deadline passed'; return; }
            const h = Math.floor(diff / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            deadlineEl.textContent = `${h}h ${m}m remaining`;
        }
        updateCountdown();
        setInterval(updateCountdown, 60000);
    }
});