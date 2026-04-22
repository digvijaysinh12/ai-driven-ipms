<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden flex flex-col">

    {{-- Editor Header --}}
    <div class="flex items-center justify-between px-4 py-3 bg-slate-900 text-white">
        <div class="text-xs font-black uppercase tracking-widest">
            Code Editor
        </div>

        <div class="flex items-center gap-2">

            {{-- Language --}}
            <select 
                id="language_{{ $qid }}"
                class="text-xs bg-slate-800 border border-slate-700 rounded px-2 py-1"
            >
                <option value="javascript">JavaScript</option>
                <option value="python">Python</option>
                <option value="cpp">C++</option>
            </select>

            {{-- Run Button --}}
            <button 
                type="button"
                onclick="runCode_{{ $qid }}()"
                class="text-xs px-3 py-1 bg-emerald-500 rounded hover:bg-emerald-600 font-bold"
            >
                Run
            </button>

        </div>
    </div>

    {{-- Monaco Editor --}}
    <div id="editor_{{ $qid }}" style="height:350px;"></div>

    {{-- Hidden textarea (for form submit) --}}
    <textarea 
        name="answers[{{ $qid }}]" 
        id="hidden_code_{{ $qid }}" 
        class="hidden"
    ></textarea>

    {{-- Console --}}
    <div class="border-t border-slate-200">

        <div class="flex items-center justify-between px-4 py-2 bg-slate-50">
            <div class="text-xs font-bold text-slate-500 uppercase">
                Console
            </div>
        </div>

        <pre 
            id="output_{{ $qid }}"
            class="p-4 text-xs text-slate-700 whitespace-pre-wrap bg-white"
        >
Output will appear here...
        </pre>

    </div>

</div>
<script src="https://unpkg.com/monaco-editor@latest/min/vs/loader.js"></script>

<script>
require.config({ paths: { vs: 'https://unpkg.com/monaco-editor@latest/min/vs' }});

let editor_{{ $qid }};

require(['vs/editor/editor.main'], function () {

    editor_{{ $qid }} = monaco.editor.create(document.getElementById('editor_{{ $qid }}'), {
        value: '// Write your code here...',
        language: 'javascript',
        theme: 'vs-dark',
        automaticLayout: true
    });

    // Sync editor → hidden textarea
    editor_{{ $qid }}.onDidChangeModelContent(() => {
        document.getElementById('hidden_code_{{ $qid }}').value = editor_{{ $qid }}.getValue();
    });

});

// Run Code
function runCode_{{ $qid }}() {

    let code = editor_{{ $qid }}.getValue();
    let language = document.getElementById('language_{{ $qid }}').value;

    document.getElementById('output_{{ $qid }}').innerText = "Running...";

    fetch('/run-code', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            code: code,
            language: language
        })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('output_{{ $qid }}').innerText = data.output || data.error;
    })
    .catch(() => {
        document.getElementById('output_{{ $qid }}').innerText = "Error running code";
    });
}
</script>