@php
    $qid = $question->id;
@endphp

<div class="space-y-3" data-answer-type="descriptive" data-qid="{{ $qid }}">
    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Your response</div>
    <textarea
        name="answers[{{ $qid }}]"
        rows="10"
        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-200 outline-none text-sm resize-y"
        placeholder="Write your answer…"
        data-answer-input
        data-qid="{{ $qid }}"
    ></textarea>
    <div class="text-xs text-slate-500 font-medium">Drafts autosave locally while you type.</div>
</div>
