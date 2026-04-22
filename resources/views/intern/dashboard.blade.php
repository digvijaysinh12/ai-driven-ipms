<x-app-layout>
    <div class="space-y-8 pb-12">
        <!-- Welcome Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Welcome back, {{ Auth::user()->name }}!</h1>
                <p class="text-slate-500 mt-1 font-medium">You have {{ $pendingTasksCount }} tasks awaiting your attention.</p>
            </div>
            @if($mentor)
                <div class="flex items-center gap-3 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        {{ substr($mentor->name, 0, 1) }}
                    </div>
                    <div class="pr-4">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Mentor</p>
                        <p class="text-xs font-bold text-slate-700">{{ $mentor->name }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @php
                $stats = [
                    ['label' => 'Total Assigned', 'value' => $totalTasksCount, 'icon' => 'layers', 'color' => 'indigo'],
                    ['label' => 'Pending Tasks', 'value' => $pendingTasksCount, 'icon' => 'clock', 'color' => 'amber'],
                    ['label' => 'Completed', 'value' => $completedTasksCount, 'icon' => 'check-circle', 'color' => 'emerald'],
                    ['label' => 'Average Score', 'value' => number_format($averageScore, 1) . '%', 'icon' => 'trending-up', 'color' => 'indigo'],
                ];
            @endphp

            @foreach($stats as $stat)
                <div class="card p-6 group hover:border-{{ $stat['color'] }}-200 transition-all">
                    <div class="flex items-center justify-between transition-transform group-hover:translate-x-1">
                        <div class="p-2.5 bg-{{ $stat['color'] }}-50 rounded-xl text-{{ $stat['color'] }}-600">
                            <i data-lucide="{{ $stat['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-200"></i>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-black text-slate-900">{{ $stat['value'] }}</div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Active Tasks List -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="play-circle" class="w-5 h-5 text-indigo-500"></i>
                        Action Required
                    </h2>
                    <a href="{{ route('user.intern.tasks') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">View all tasks</a>
                </div>

                <div class="grid gap-4">
                    @forelse($tasks->take(3) as $task)
                        @php
                            $submission = $task->submissions->first();
                        @endphp
                        <div class="card p-5 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                                    <i data-lucide="{{ $task->type?->slug === 'mcq' ? 'list-checks' : 'code-2' }}" class="w-6 h-6 text-slate-400"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900">{{ $task->title }}</h3>
                                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mt-0.5">
                                        <span class="badge bg-slate-100 text-slate-600 border-none">{{ $task->type?->name }}</span>
                                        <span class="text-slate-300">•</span>
                                        <span>{{ $task->questions_count }} Questions</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                @if($submission)
                                    <span class="badge bg-emerald-50 text-emerald-700 border-emerald-100 px-3 py-1">Submitted</span>
                                @else
                                    <a href="{{ route('user.intern.tasks.execute', $task->id) }}" class="btn btn-accent px-5 py-2 text-xs font-bold shadow-lg shadow-indigo-100">
                                        Start Task
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="card p-12 text-center border-dashed border-2">
                            <i data-lucide="coffee" class="w-8 h-8 text-slate-200 mx-auto mb-3"></i>
                            <p class="text-slate-400 text-sm font-medium">All caught up! No pending tasks found.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Feedback Side -->
            <div class="space-y-6">
                <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="message-square" class="w-5 h-5 text-indigo-500"></i>
                    Recent Feedback
                </h2>

                <div class="space-y-4">
                    @forelse($submissions->whereNotNull('feedback')->take(3) as $sub)
                        <div class="card p-5 bg-white border-l-4 border-l-indigo-500">
                            <div class="flex justify-between items-start mb-3">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $sub->task->title }}</span>
                                <span class="text-xs font-black text-indigo-600">{{ $sub->score }}%</span>
                            </div>
                            <p class="text-xs text-slate-600 italic leading-relaxed line-clamp-3">"{{ $sub->feedback }}"</p>
                            <div class="mt-4 flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold">
                                    {{ substr($sub->reviewer->name ?? 'M', 0, 1) }}
                                </div>
                                <span class="text-[10px] font-bold text-slate-400">{{ $sub->reviewer->name ?? 'Mentor' }} • {{ $sub->reviewed_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="card p-10 text-center opacity-50">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No feedback yet</p>
                        </div>
                    @endforelse
                </div>

                <!-- Progress Visual -->
                <div class="card p-6 bg-slate-900 border-none relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-4">Milestone Progress</p>
                        <div class="flex items-end gap-2 mb-2">
                            <span class="text-4xl font-black text-white">{{ $completedTasksCount }}</span>
                            <span class="text-slate-500 font-bold pb-1">/ {{ $totalTasksCount }} Tasks</span>
                        </div>
                        <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500" style="width: {{ $totalTasksCount > 0 ? ($completedTasksCount / $totalTasksCount * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>