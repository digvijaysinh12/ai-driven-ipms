<x-app-layout>
    @php
        $task = $submission->task;
        $result = $submission->result;
        $statusName = $submission->status_name;
        $answers = $submission->answers ?? collect();
        $questions = $task->questions ?? collect();
        $answersByQ = $answers->keyBy('task_question_id');
        $aiFeedback = $submission->final_feedback ?? $submission->ai_feedback;
        $percent = $result !== null ? round($result, 1) : null;
    @endphp

    <div class="max-w-5xl mx-auto px-6 py-8 space-y-6 pb-12">
        <div class="flex items-start justify-between gap-6">
            <div class="space-y-2">
                <a href="{{ route('user.intern.tasks.show', $task->id) }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-700">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Back to task
                </a>
                <div class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Results</div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $task->title }}</h1>
            </div>
            <div class="flex flex-col items-end gap-2">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-2xl bg-slate-100 text-slate-700 text-xs font-black">
                    <i data-lucide="badge-check" class="w-4 h-4 text-slate-500"></i>
                    {{ $statusName }}
                </span>
                @if($percent !== null)
                    <span class="text-3xl font-black text-slate-900">{{ $percent }}%</span>
                @else
                    <span class="text-sm font-bold text-slate-500">Processing…</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white border border-slate-100 shadow-sm rounded-3xl p-6">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Overall</div>
                    <div class="mt-2 flex items-end gap-2">
                        <div class="text-4xl font-black text-slate-900">{{ $percent !== null ? $percent : '—' }}</div>
                        <div class="text-sm font-black text-slate-400 pb-1">%</div>
                    </div>
                    <div class="mt-4 h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full bg-indigo-600" style="width: {{ $percent !== null ? min(max($percent, 0), 100) : 0 }}%"></div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Items</div>
                            <div class="mt-1 text-lg font-black text-slate-900">{{ $questions->count() }}</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Answered</div>
                            <div class="mt-1 text-lg font-black text-slate-900">
                                {{ $answers->filter(fn ($a) => !($a->isEmpty()))->count() }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-100 shadow-sm rounded-3xl p-6">
                    <div class="flex items-center justify-between">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">AI Feedback</div>
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-2xl bg-indigo-50 text-indigo-700 text-xs font-black border border-indigo-100">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            Summary
                        </span>
                    </div>
                    <div class="mt-3 text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                        {{ $aiFeedback ?: 'No feedback available yet. Check back after evaluation completes.' }}
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8 space-y-4">
                <div class="bg-white border border-slate-100 shadow-sm rounded-3xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="list-checks" class="w-4 h-4 text-slate-400"></i>
                            <div class="text-sm font-bold text-slate-800">Item feedback</div>
                        </div>
                        <div class="text-xs font-bold text-slate-400">{{ $questions->count() }} items</div>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse($questions as $i => $q)
                            @php
                                $a = $answersByQ->get($q->id);
                                $answerText = $a?->answer_text;
                                $github = $a?->github_link;
                                $file = $a?->file_path;
                                $itemFeedback = $a?->ai_feedback;
                                $hasAnswer = $a && ! $a->isEmpty();
                            @endphp

                            <div class="p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Item {{ $i + 1 }}</div>
                                        <div class="mt-1 text-sm font-black text-slate-900">{{ $q->question }}</div>
                                    </div>
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-2xl text-xs font-black border {{ $hasAnswer ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-slate-50 text-slate-600 border-slate-100' }}">
                                        <i data-lucide="{{ $hasAnswer ? 'check-circle-2' : 'circle' }}" class="w-4 h-4"></i>
                                        {{ $hasAnswer ? 'Answered' : 'No answer' }}
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Your answer</div>
                                        <div class="mt-2 text-sm text-slate-700 whitespace-pre-line break-words">
                                            @if($file)
                                                <span class="inline-flex items-center gap-2 text-slate-700 font-bold">
                                                    <i data-lucide="paperclip" class="w-4 h-4 text-slate-500"></i>
                                                    File submitted
                                                </span>
                                            @elseif($github)
                                                <a href="{{ $github }}" class="text-indigo-600 hover:text-indigo-700 font-bold break-all" target="_blank" rel="noreferrer">View link</a>
                                            @elseif(!empty($answerText))
                                                {{ $answerText }}
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="rounded-2xl bg-indigo-50 border border-indigo-100 p-4">
                                        <div class="flex items-center justify-between">
                                            <div class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Feedback</div>
                                            <i data-lucide="sparkles" class="w-4 h-4 text-indigo-500"></i>
                                        </div>
                                        <div class="mt-2 text-sm text-indigo-900/80 leading-relaxed whitespace-pre-line">
                                            {{ $itemFeedback ?: '—' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-10 text-center text-sm text-slate-500">No questions found for this task.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
