@php
    $qid = $question->id;
@endphp

<div class="space-y-3" data-answer-type="true_false" data-qid="{{ $qid }}">
    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Choose one</div>

    @foreach(['True', 'False'] as $opt)
        <label class="flex items-center gap-3 p-3 rounded-2xl border border-slate-100 bg-white hover:bg-slate-50 transition-colors cursor-pointer">
            <input
                type="radio"
                name="answers[{{ $qid }}]"
                value="{{ $opt }}"
                class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-200"
                data-answer-input
                data-qid="{{ $qid }}"
            />
            <div class="text-sm font-bold text-slate-800">{{ $opt }}</div>
        </label>
    @endforeach
</div>
