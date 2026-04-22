@php
    $tasks = $tasks ?? collect();
@endphp

@forelse($tasks as $task)
    @php
        $submissions = $task->submissions->where('user_id', auth()->id());
        $activeSubmission = $submissions->first(fn($s) => $s->isActive());
        $latestSubmission = $submissions->first(); // Already sorted by latest in controller
        
        $statusSlug = $activeSubmission?->status?->slug ?? $latestSubmission?->status?->slug;
        $isCompleted = $latestSubmission && $latestSubmission->isCompleted();
        $isSubmitted = $latestSubmission && $latestSubmission->status?->slug === 'submitted';
        $hasActive = (bool) $activeSubmission;

        $typeSlug = $task->type?->slug;
        $typeName = $task->type?->name ?? 'Task';
        $questionsCount = $task->questions_count ?? ($task->questions?->count() ?? 0);
    @endphp

    <div class="p-5 hover:bg-slate-50/60 transition-colors">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-slate-100 flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $typeSlug === 'coding' ? 'code-2' : 'list-checks' }}" class="w-5 h-5 text-slate-500"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="text-sm font-black text-slate-900 truncate">{{ $task->title }}</div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-600">
                            {{ $typeName }}
                        </span>
                    </div>
                    <div class="mt-1 text-xs font-medium text-slate-500">
                        {{ $questionsCount }} items
                        @if(!empty($task->description))
                            <span class="text-slate-300 px-1">•</span>
                            <span class="line-clamp-1">{{ $task->description }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <span class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-2xl text-xs font-black border {{ $isCompleted ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : ($hasActive ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-slate-50 text-slate-600 border-slate-100') }}">
                    <i data-lucide="{{ $isCompleted ? 'check-circle-2' : ($hasActive ? ($isSubmitted ? 'send' : 'clock') : 'circle') }}" class="w-4 h-4"></i>
                    {{ $isCompleted ? 'Completed' : ($hasActive ? ($isSubmitted ? 'Submitted' : 'In Progress') : 'Not started') }}
                </span>

                @if($hasActive)
                    <a href="{{ route('user.intern.tasks.execute', $task->id) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-indigo-600 text-white text-xs font-black shadow-sm shadow-indigo-100 hover:bg-indigo-700 transition-colors">
                        {{ $isSubmitted ? 'Reopen' : 'Resume' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                @elseif($isCompleted)
                    <div class="flex items-center gap-2">
                        <a href="{{ route('user.intern.tasks.results', $task->id) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-slate-900 text-white text-xs font-black hover:bg-slate-800 transition-colors">
                            Results
                        </a>
                        <a href="{{ route('user.intern.tasks.execute', $task->id) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-slate-100 text-slate-800 text-xs font-black hover:bg-slate-200 transition-colors" title="Start a fresh attempt">
                            New
                        </a>
                    </div>
                @else
                    <a href="{{ route('user.intern.tasks.execute', $task->id) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-indigo-600 text-white text-xs font-black shadow-sm shadow-indigo-100 hover:bg-indigo-700 transition-colors">
                        Start
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="p-10 text-center">
        <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
            <i data-lucide="inbox" class="w-6 h-6"></i>
        </div>
        <div class="text-sm font-bold text-slate-700">No tasks found</div>
        <div class="text-xs text-slate-500 font-medium mt-1">Try clearing filters or searching a different keyword.</div>
    </div>
@endforelse
