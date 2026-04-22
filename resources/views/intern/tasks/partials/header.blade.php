@php
    $taskType = $task->type?->name ?? 'Task';
    $taskSlug = $task->type?->slug ?? null;
@endphp

<div class="bg-white border border-slate-100 shadow-sm rounded-3xl px-5 py-4">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ $backUrl ?? route('user.intern.tasks.show', $task->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-slate-100 text-slate-700 text-xs font-black hover:bg-slate-200 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Back
                </a>
                <span class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest">
                    <i data-lucide="{{ $taskSlug === 'coding' ? 'code-2' : 'list-checks' }}" class="w-4 h-4"></i>
                    {{ $taskType }}
                </span>
                <span id="wsSessionPill" class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-slate-50 border border-slate-100 text-xs font-black text-slate-600">
                    <i data-lucide="cloud" class="w-4 h-4 text-slate-400"></i>
                    <span class="ws-session-text">Ready</span>
                </span>
            </div>

            <div class="mt-3 min-w-0">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight truncate">{{ $task->title }}</h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">
                    Solve in any order • Drafts autosave locally • Submit when you're confident
                </p>
            </div>
        </div>

        <div class="hidden md:flex items-center gap-2">
            <button type="button" id="wsOpenReview" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-slate-100 text-slate-800 text-xs font-black hover:bg-slate-200 transition-colors">
                <i data-lucide="scan" class="w-4 h-4"></i>
                Review
            </button>
            <button type="button" id="wsOpenSubmit" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-indigo-600 text-white text-xs font-black shadow-sm shadow-indigo-100 hover:bg-indigo-700 transition-colors">
                <i data-lucide="send" class="w-4 h-4"></i>
                Submit
            </button>
        </div>
    </div>
</div>
