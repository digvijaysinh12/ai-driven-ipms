@php
    $questions = $task->questions ?? collect();
@endphp

<div class="bg-white border border-slate-100 shadow-sm rounded-3xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="navigation" class="w-4 h-4 text-slate-400"></i>
            <div class="text-sm font-bold text-slate-800">Navigator</div>
        </div>
        <div class="text-xs font-bold text-slate-400">{{ $questions->count() }}</div>
    </div>

    <div class="p-4 space-y-3">
        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-3">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</div>
            <div class="mt-2 grid grid-cols-3 gap-2 text-[11px] font-black">
                <div class="flex items-center gap-2 text-slate-600">
                    <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                    New
                </div>
                <div class="flex items-center gap-2 text-amber-700">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    Started
                </div>
                <div class="flex items-center gap-2 text-emerald-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Done
                </div>
            </div>
        </div>

        <div class="space-y-2 max-h-[calc(100vh-340px)] overflow-auto pr-1">
            @foreach($questions as $i => $q)
                <button
                    type="button"
                    class="ws-nav-item w-full text-left rounded-2xl px-3 py-3 border border-slate-100 bg-white hover:bg-slate-50 transition-colors flex items-center justify-between gap-3"
                    data-qid="{{ $q->id }}"
                >
                    <div class="min-w-0">
                        <div class="text-xs font-black text-slate-800 truncate">Item {{ $i + 1 }}</div>
                        <div class="text-[11px] text-slate-500 font-medium truncate">{{ $q->question }}</div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="ws-nav-dot w-2.5 h-2.5 rounded-full bg-slate-300" data-qid="{{ $q->id }}"></span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
                    </div>
                </button>
            @endforeach
        </div>
    </div>
</div>
