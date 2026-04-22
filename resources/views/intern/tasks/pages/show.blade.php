<x-app-layout>
    <div class="max-w-4xl mx-auto px-6 py-8 space-y-8 pb-12">
        <div class="flex items-center gap-3">
            <a href="{{ route('user.intern.tasks') }}" class="p-2 rounded-xl hover:bg-slate-100 transition-colors text-slate-500">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Task Workspace</div>
        </div>

        <div class="bg-white border border-slate-100 shadow-sm rounded-3xl p-8 sm:p-10 relative overflow-hidden">
            <div class="absolute -right-24 -top-24 w-72 h-72 rounded-full bg-indigo-50 blur-3xl opacity-70"></div>

            <div class="relative space-y-8">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-[11px] font-black uppercase tracking-widest">
                        <i data-lucide="{{ ($task->type?->slug ?? 'task') === 'coding' ? 'code-2' : 'list-checks' }}" class="w-4 h-4"></i>
                        {{ $task->type?->name ?? 'Task' }}
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-[11px] font-black uppercase tracking-widest">
                        <i data-lucide="layers" class="w-4 h-4 text-slate-500"></i>
                        {{ $task->questions_count ?? ($task->questions?->count() ?? 0) }} items
                    </span>
                    @if($submission)
                        @if($submission->isCompleted())
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-[11px] font-black uppercase tracking-widest border border-emerald-100">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                Completed
                            </span>
                        @elseif($submission->status?->slug === 'submitted')
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 text-[11px] font-black uppercase tracking-widest border border-indigo-100">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Submitted
                            </span>
                        @else
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 text-[11px] font-black uppercase tracking-widest border border-amber-100">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                In Progress
                            </span>
                        @endif
                    @else
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-500 text-[11px] font-black uppercase tracking-widest border border-slate-200">
                            <i data-lucide="circle" class="w-4 h-4"></i>
                            Not started
                        </span>
                    @endif
                </div>

                <div class="space-y-3">
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight">{{ $task->title }}</h1>
                    @if(!empty($task->description))
                        <p class="text-slate-600 font-medium leading-relaxed">{{ $task->description }}</p>
                    @else
                        <p class="text-slate-500 text-sm font-medium">Open the workspace to solve. Your work autosaves locally.</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-2xl bg-slate-50 p-5 border border-slate-100">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Navigation</div>
                        <div class="mt-2 text-sm font-bold text-slate-800">Solve in any order</div>
                        <div class="mt-1 text-xs text-slate-500 font-medium">No forced flow — jump between items anytime.</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-5 border border-slate-100">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Autosave</div>
                        <div class="mt-2 text-sm font-bold text-slate-800">Local session restore</div>
                        <div class="mt-1 text-xs text-slate-500 font-medium">Refresh-safe — your draft comes back instantly.</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-5 border border-slate-100">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Submit</div>
                        <div class="mt-2 text-sm font-bold text-slate-800">Submit when ready</div>
                        <div class="mt-1 text-xs text-slate-500 font-medium">Review unanswered items before final submit.</div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between pt-4">
                    @if($submission && $submission->isActive())
                        <div class="flex-1 rounded-2xl bg-indigo-50 border border-indigo-100 p-5 flex items-start gap-3">
                            <i data-lucide="info" class="w-5 h-5 text-indigo-600 mt-0.5"></i>
                            <div>
                                <div class="text-sm font-bold text-indigo-900">
                                    {{ $submission->status?->slug === 'submitted' ? 'Task submitted but reopenable' : 'Continue where you left off' }}
                                </div>
                                <div class="text-xs font-medium text-indigo-800/70">
                                    {{ $submission->status?->slug === 'submitted' ? 'You can still edit your answers until the mentor starts the final review.' : 'Your progress is automatically saved.' }}
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('user.intern.tasks.execute', $task->id) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl bg-indigo-600 text-white font-black text-sm shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-colors">
                            {{ $submission->status?->slug === 'submitted' ? 'Reopen workspace' : 'Resume workspace' }}
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @elseif($submission && $submission->isCompleted())
                        <div class="flex-1 rounded-2xl bg-emerald-50 border border-emerald-100 p-5 flex items-start gap-3">
                            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 mt-0.5"></i>
                            <div>
                                <div class="text-sm font-bold text-emerald-900">Task completed</div>
                                <div class="text-xs font-medium text-emerald-800/70">Mentor has reviewed this attempt. You can start a new attempt if needed.</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('user.intern.tasks.results', $task->id) }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-slate-900 text-white font-black text-sm hover:bg-slate-800 transition-colors">
                                Results
                            </a>
                            <a href="{{ route('user.intern.tasks.execute', $task->id) }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white border border-slate-200 text-slate-700 font-bold text-sm hover:bg-slate-50 transition-colors">
                                New attempt
                            </a>
                        </div>
                    @else
                        <a href="{{ route('user.intern.tasks.execute', $task->id) }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-indigo-600 text-white font-black text-sm shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all hover:scale-[1.02] active:scale-[0.98]">
                            Start solving
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <div class="text-xs font-medium text-slate-500">
                            Time starts when you open the workspace.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
