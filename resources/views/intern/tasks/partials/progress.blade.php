@php
    $total = $task->questions?->count() ?? 0;
@endphp

<div class="bg-white border border-slate-100 shadow-sm rounded-3xl px-5 py-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-500">
                <i data-lucide="gauge" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-sm font-black text-slate-900">Progress</div>
                <div class="text-xs font-medium text-slate-500">Soft indicator — not a timer.</div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-xs font-black text-slate-500">
                <span id="wsProgressText">0 / {{ $total }}</span>
            </div>
            <div class="w-44 sm:w-56 h-2 rounded-full bg-slate-100 overflow-hidden">
                <div id="wsProgressBar" class="h-full bg-indigo-600 transition-all" style="width: 0%"></div>
            </div>
            <div class="text-xs font-black text-slate-500" id="wsProgressPct">0%</div>
        </div>
    </div>
</div>
