<x-app-layout>
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Mentor Workspace</h1>
                <p class="text-slate-500 mt-1 font-medium">Overview of your assigned interns and pending evaluations.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('user.mentor.tasks.create') }}" class="btn btn-accent px-6 py-2.5 shadow-lg shadow-indigo-200 gap-2 active:scale-95 transition-all">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    Launch New Task
                </a>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @php
                $stats = [
                    ['label' => 'Mentees', 'value' => $assignedInternsCount, 'icon' => 'users-2', 'trend' => 'Assigned', 'color' => 'indigo'],
                    ['label' => 'Draft Tasks', 'value' => $draftTasksCount, 'icon' => 'edit-3', 'trend' => 'In Prep', 'color' => 'slate'],
                    ['label' => 'Ready Tasks', 'value' => $readyTasksCount, 'icon' => 'check-circle', 'trend' => 'Deployable', 'color' => 'blue'],
                    ['label' => 'Pending Reviews', 'value' => $pendingSubmissionsCount, 'icon' => 'alert-circle', 'trend' => 'Action Needed', 'color' => 'amber'],
                ];
            @endphp

            @foreach($stats as $stat)
                <div class="card p-6 group hover:border-{{ $stat['color'] }}-200 transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div class="p-3 bg-{{ $stat['color'] }}-50 rounded-xl text-{{ $stat['color'] }}-600 transition-transform group-hover:scale-110">
                            <i data-lucide="{{ $stat['icon'] }}" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ $stat['trend'] }}</span>
                    </div>
                    <div class="mt-6">
                        <div class="text-4xl font-black text-slate-900">{{ $stat['value'] ?? 0 }}</div>
                        <div class="text-sm font-semibold text-slate-500 mt-1">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Submissions -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="activity" class="w-5 h-5 text-indigo-500"></i>
                        Recent Submissions
                    </h2>
                    <a href="{{ route('user.mentor.submissions.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">View all</a>
                </div>

                <div class="card bg-white overflow-hidden">
                    <div class="divide-y divide-slate-50">
                        @forelse($recentSubmissions as $submission)
                            <div class="p-5 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center font-black text-slate-400">
                                        {{ substr($submission->intern->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-900">{{ $submission->task->title ?? 'Untitled Task' }}</h3>
                                        <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mt-0.5">
                                            <span class="text-indigo-600">@ {{ $submission->intern->name ?? 'Unknown' }}</span>
                                            <span class="text-slate-300">•</span>
                                            <span>{{ $submission->submitted_at?->diffForHumans() ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($submission->status?->slug === 'ai_evaluated')
                                        <span class="badge bg-emerald-50 text-emerald-700 border-emerald-100 py-1 px-3">AI Evaluated</span>
                                    @endif
                                    <a href="{{ route('user.mentor.submissions.show', $submission->id) }}" class="btn btn-secondary py-1.5 px-4 text-xs font-bold border-slate-200">
                                        Review
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-20 text-center">
                                <i data-lucide="inbox" class="w-12 h-12 text-slate-200 mx-auto mb-4"></i>
                                <p class="text-slate-400 font-medium">No pending submissions discovered yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Access / Interns -->
            <div class="space-y-6">
                <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="users" class="w-5 h-5 text-indigo-500"></i>
                    My Interns
                </h2>
                
                <div class="card p-2 space-y-1">
                    @foreach($interns ?? [] as $intern)
                        <a href="{{ route('user.mentor.interns.progress', $intern->id) }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                    {{ substr($intern->name, 0, 1) }}
                                </div>
                                <span class="text-sm font-semibold text-slate-700">{{ $intern->name }}</span>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
                        </a>
                    @endforeach
                    
                    <a href="{{ route('user.mentor.interns') }}" class="block text-center p-3 text-xs font-bold text-slate-400 hover:text-indigo-600 transition-colors border-t border-slate-50 mt-2">
                        View all intern metrics
                    </a>
                </div>

                <!-- AI Token Usage / Card Placeholder -->
                <div class="card p-6 bg-slate-900 border-none shadow-xl shadow-slate-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 text-indigo-400 text-xs font-bold uppercase tracking-widest mb-4">
                            <i data-lucide="zap" class="w-4 h-4 fill-current"></i>
                            AI Power
                        </div>
                        <h4 class="text-white font-bold leading-tight">Your evaluation assistant is active.</h4>
                        <p class="text-slate-400 text-xs mt-2">AI automatically pre-grades common MCQ and Theory submissions to save your time.</p>
                    </div>
                    <!-- Decorative element -->
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>