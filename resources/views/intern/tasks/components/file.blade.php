@php
    $qid = $question->id;
@endphp

<div class="space-y-3" data-answer-type="file" data-qid="{{ $qid }}">
    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Upload</div>
    <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4 space-y-3">
        <input
            type="file"
            name="answers[{{ $qid }}]"
            class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-slate-900 file:text-white file:font-black file:text-xs hover:file:bg-slate-800"
            data-file-input
            data-qid="{{ $qid }}"
        />
        <div class="text-xs text-slate-500 font-medium">
            File selections cannot be autosaved or restored after refresh.
        </div>
        <div class="text-xs font-bold text-slate-600" data-file-label data-qid="{{ $qid }}"></div>
    </div>
</div>
