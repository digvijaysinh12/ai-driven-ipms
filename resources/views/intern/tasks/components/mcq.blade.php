@php
    $qid = $question->id;
    $options = $question->options ?? [];
@endphp

<div class="space-y-3" data-answer-type="mcq" data-qid="{{ $qid }}">
    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Choose one</div>

    @forelse($options as $i => $opt)
        <label class="flex items-start gap-3 p-3 rounded-2xl border border-slate-100 bg-white hover:bg-slate-50 transition-colors cursor-pointer">
            <input
                type="radio"
                name="answers[{{ $qid }}]"
                value="{{ $i }}"
                class="mt-1 w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-200"
                data-answer-input
                data-qid="{{ $qid }}"
            />
            <div class="min-w-0">
                <div class="text-xs font-black text-slate-700">{{ chr(65 + $i) }}</div>
                <div class="text-sm text-slate-800 font-medium break-words">{{ $opt }}</div>
            </div>
        </label>
    @empty
        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4 text-sm text-slate-600">
            No options provided.
        </div>
    @endforelse
</div>
