<x-app-layout>
    <div class="space-y-8 pb-12">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.intern.tasks') }}" class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-400">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $task->title }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="badge {{ $submission->status?->slug === 'completed' || $submission->status?->slug === 'ai_evaluated' ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700' }} border-none px-3">
                            {{ $submission->status?->name }}
                        </span>
                        <span class="text-xs font-medium text-slate-400">Submitted {{ $submission->submitted_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
            @if($submission->score)
                <div class="card bg-white p-4 border-2 border-indigo-600 shadow-xl shadow-indigo-100 flex items-center gap-6 px-10">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-2 text-center">Final Score</p>
                        <p class="text-4xl font-black text-slate-900 leading-none">{{ $submission->score }}<span class="text-xl text-slate-300">%</span></p>
                    </div>
                    <div class="h-10 w-px bg-slate-100"></div>
                    <div class="w-12 h-12 rounded-full border-4 border-emerald-500 flex items-center justify-center">
                        <i data-lucide="check" class="w-6 h-6 text-emerald-500"></i>
                    </div>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Detailed Content -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Feedback Section -->
                @if($submission->feedback)
                    <section class="space-y-4">
                        <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Mentor Feedback</h2>
                        <div class="card p-8 bg-indigo-900 text-white relative overflow-hidden group">
                            <i data-lucide="quote" class="absolute -right-4 -bottom-4 w-32 h-32 text-white/5 transition-transform group-hover:scale-110"></i>
                            <div class="relative z-10 space-y-6">
                                <p class="text-lg font-medium leading-relaxed italic">"{{ $submission->feedback }}"</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center font-black text-xs">
                                        {{ substr($submission->reviewer->name ?? 'M', 0, 1) }}
                                    </div>
                                    <span class="text-xs font-bold text-indigo-300">Evaluated by {{ $submission->reviewer->name ?? 'System Mentor' }} • {{ $submission->reviewed_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                <!-- My Responses Section -->
                <section class="space-y-6">
                    <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">My Responses</h2>
                    <div class="space-y-4">
                        @foreach($submission->answers as $index => $answer)
                            <div class="card p-6 bg-white space-y-4 shadow-sm group hover:border-slate-300 transition-all">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Question {{ $index + 1 }}</span>
                                    @if($answer->is_correct)
                                        <span class="badge bg-emerald-50 text-emerald-700 border-none text-[10px] font-bold px-3">Auto-Verified</span>
                                    @endif
                                </div>
                                <h3 class="font-bold text-slate-800">{{ $answer->question->question_text ?? 'Question Details' }}</h3>
                                
                                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-slate-700 text-sm leading-relaxed">
                                    {{ $answer->answer_text }}
                                </div>

                                @if($answer->ai_feedback)
                                    <div class="flex gap-3 p-4 bg-indigo-50/50 rounded-xl border border-indigo-100/50">
                                        <i data-lucide="sparkles" class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5"></i>
                                        <div>
                                            <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-1">AI Critique</p>
                                            <p class="text-xs text-indigo-900/60 font-medium leading-relaxed">{{ $answer->ai_feedback }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <!-- Stats/Summary Sidebar -->
            <div class="space-y-6">
                <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Performance Summary</h2>
                
                <div class="card p-6 bg-white shadow-xl shadow-slate-200/50 space-y-6">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Engagement Metrics</p>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center bg-slate-50 p-3 rounded-xl">
                                <span class="text-xs font-bold text-slate-600">Accuracy</span>
                                <span class="text-xs font-black text-indigo-600">{{ $submission->score }}%</span>
                            </div>
                            <div class="flex justify-between items-center bg-slate-50 p-3 rounded-xl">
                                <span class="text-xs font-bold text-slate-600">Module Status</span>
                                <span class="text-xs font-black text-emerald-600">Completed</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-50">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Module Details</p>
                        <div class="space-y-2">
                            <div class="flex items-center gap-3 text-sm font-bold text-slate-700">
                                <i data-lucide="layers" class="w-4 h-4 text-slate-300"></i>
                                {{ $task->type?->name }}
                            </div>
                            <div class="flex items-center gap-3 text-sm font-bold text-slate-700">
                                <i data-lucide="help-circle" class="w-4 h-4 text-slate-300"></i>
                                {{ $task->questions_count }} Total Questions
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Learning Tip Card -->
                <div class="card p-6 bg-emerald-50 border-emerald-100 space-y-3 relative overflow-hidden">
                    <i data-lucide="lightbulb" class="absolute -right-2 -top-2 w-16 h-16 text-emerald-500/10"></i>
                    <h4 class="text-sm font-black text-emerald-800 uppercase tracking-widest">Next Step</h4>
                    <p class="text-xs text-emerald-900/60 font-medium leading-relaxed">Great job on completing this module. Review your feedback carefully to understand your areas for improvement before starting the next task.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
