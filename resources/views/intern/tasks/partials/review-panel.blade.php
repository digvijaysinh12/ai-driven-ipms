@php
    $total = $task->questions?->count() ?? 0;
@endphp

<div class="bg-white border border-slate-100 shadow-sm rounded-3xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="pie-chart" class="w-4 h-4 text-slate-400"></i>
            <div class="text-sm font-bold text-slate-800">Overview</div>
        </div>
        <div class="text-xs font-bold text-slate-400">{{ $total }}</div>
    </div>

    <div class="p-5 space-y-4">
        <div class="grid grid-cols-3 gap-3">
            <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Done</div>
                <div class="mt-1 text-lg font-black text-slate-900" id="wsDoneCount">0</div>
            </div>
            <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Started</div>
                <div class="mt-1 text-lg font-black text-slate-900" id="wsStartedCount">0</div>
            </div>
            <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">New</div>
                <div class="mt-1 text-lg font-black text-slate-900" id="wsNewCount">0</div>
            </div>
        </div>

        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
            <div class="flex items-center justify-between">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Completion</div>
                <div class="text-xs font-black text-slate-500" id="wsCompletionPct">0%</div>
            </div>
            <div class="mt-3 h-2 rounded-full bg-slate-200 overflow-hidden">
                <div id="wsCompletionBar" class="h-full bg-emerald-500 transition-all" style="width: 0%"></div>
            </div>
        </div>

        <button type="button" id="wsOpenReviewSide" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-slate-900 text-white text-xs font-black hover:bg-slate-800 transition-colors">
            <i data-lucide="scan" class="w-4 h-4"></i>
            Open review panel
        </button>
    </div>
</div>
