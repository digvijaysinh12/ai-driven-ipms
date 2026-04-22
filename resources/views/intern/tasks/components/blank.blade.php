@php
    $qid = $question->id;
@endphp

<div class="space-y-3" data-answer-type="blank" data-qid="{{ $qid }}">
    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Your answer</div>
    <input
        type="text"
        name="answers[{{ $qid }}]"
        class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-200 outline-none text-sm"
        placeholder="Type your answer…"
        data-answer-input
        data-qid="{{ $qid }}"
    />
    <div class="text-xs text-slate-500 font-medium">Tip: Keep it concise and specific.</div>
</div>
