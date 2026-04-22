@php
    $qid = $question->id;
@endphp

<div class="space-y-4" data-answer-type="coding" data-qid="{{ $qid }}">
    <div class="flex items-center justify-between">
        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Editor</div>
        <button type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-slate-100 text-slate-800 text-xs font-black hover:bg-slate-200 transition-colors" data-run-code data-qid="{{ $qid }}">
            <i data-lucide="play" class="w-4 h-4"></i>
            Run code
        </button>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-slate-950 overflow-hidden shadow-sm">
        <div class="flex items-center justify-between px-4 py-2 bg-slate-900 border-b border-slate-800">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Code</div>
            <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest">UI only</div>
        </div>
        <textarea
            name="answers[{{ $qid }}]"
            rows="14"
            class="w-full bg-slate-950 text-slate-100 font-mono text-xs leading-relaxed p-4 outline-none resize-y"
            placeholder="// Write your code here…"
            data-answer-input
            data-qid="{{ $qid }}"
            spellcheck="false"
        ></textarea>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
        <div class="flex items-center justify-between px-4 py-2 bg-slate-50 border-b border-slate-100">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Console</div>
            <button type="button" class="text-[10px] font-black text-slate-500 hover:text-slate-700 uppercase tracking-widest" data-clear-console data-qid="{{ $qid }}">Clear</button>
        </div>
        <pre class="p-4 text-xs text-slate-700 whitespace-pre-wrap" data-console data-qid="{{ $qid }}">Output will appear here.</pre>
    </div>
</div>
