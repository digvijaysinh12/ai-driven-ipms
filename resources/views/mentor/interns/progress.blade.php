<x-app-layout>
    <div class="h-screen flex flex-col bg-slate-50 overflow-hidden">
        
        <!-- TOP NAV / HEADER -->
        <header class="h-[65px] bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0 z-50">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.mentor.interns') }}" class="w-10 h-10 rounded-xl flex items-center justify-center hover:bg-slate-100 transition-colors border border-slate-200">
                    <i data-lucide="arrow-left" class="w-5 h-5 text-slate-600"></i>
                </a>
                <div>
                    <h1 class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Intern Performance</h1>
                    <h2 class="text-lg font-black text-slate-900 leading-none truncate max-w-[400px]">
                        {{ $intern->name }}
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3 pr-6 border-r border-slate-200 h-8">
                    <div class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                        {{ $reviewedCount }}/{{ $totalSubmissions }} Reviewed
                    </div>
                </div>
                
                <a href="mailto:{{ $intern->email }}" class="bg-slate-900 text-white px-5 py-2 rounded-xl font-black text-xs hover:bg-slate-800 transition-all shadow-lg shadow-slate-200 flex items-center gap-2 active:scale-95">
                    <i data-lucide="mail" class="w-4 h-4"></i> Send Message
                </a>
            </div>
        </header>

        <!-- MAIN LAYOUT -->
        <div class="flex-1 flex overflow-hidden">
            
            <!-- LEFT PANEL: STATS & TIMELINE -->
            <main class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <div class="max-w-[1000px] mx-auto space-y-8 pb-20">
                    
                    <!-- HIGH-LEVEL STATS -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Assigned Challenges</h4>
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-black text-slate-900">{{ $tasks->count() }}</span>
                                <span class="text-xs font-bold text-slate-400">active items</span>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Total Submissions</h4>
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-black text-indigo-600">{{ $totalSubmissions }}</span>
                                <span class="text-xs font-bold text-slate-400">responses</span>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Avg. Completion</h4>
                            @php 
                                $rate = $totalSubmissions > 0 ? ($reviewedCount / $totalSubmissions) * 100 : 0;
                            @endphp
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-black text-emerald-600">{{ number_format($rate, 0) }}%</span>
                                <span class="text-xs font-bold text-slate-400">review rate</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        <!-- ASSIGNED TASKS MAP -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="target" class="w-4 h-4 text-slate-400"></i>
                                <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest">Active Roadmap</h3>
                            </div>
                            
                            @forelse($tasks as $task)
                                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between group hover:border-indigo-200 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-all border border-slate-100">
                                            <i data-lucide="file-code-2" class="w-5 h-5"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-800 leading-tight">{{ $task->title }}</h4>
                                            <p class="text-[10px] font-black text-slate-400 uppercase mt-1">{{ $task->type->name }} • {{ $task->questions_count }} Steps</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('user.mentor.tasks.show', $task->id) }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 hover:text-indigo-600 hover:bg-indigo-50 transition-all">
                                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                                    </a>
                                </div>
                            @empty
                                <div class="p-10 text-center bg-white rounded-2xl border border-dashed border-slate-200">
                                    <p class="text-[11px] font-black text-slate-400 uppercase italic">No active journey mapped</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- ACTIVITY FEED / SUBMISSIONS -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="zap" class="w-4 h-4 text-indigo-400"></i>
                                <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest">Activity Feed</h3>
                            </div>

                            @forelse($submissions as $submission)
                                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h4 class="text-sm font-black text-slate-900 truncate max-w-[200px]">{{ $submission->task->title ?? 'Challenge Response' }}</h4>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $submission->submitted_at?->diffForHumans() ?? 'In Progress' }}</p>
                                        </div>
                                        @php
                                            $slug = $submission->status?->slug;
                                            $badgeClass = match($slug) {
                                                'completed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                                'submitted', 'ai_evaluated' => 'bg-amber-50 text-amber-600 border-amber-100',
                                                default => 'bg-slate-50 text-slate-500 border-slate-100'
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest border {{ $badgeClass }}">
                                            {{ $submission->status?->name ?? 'Draft' }}
                                        </span>
                                    </div>

                                    @if($submission->isReviewed() || $submission->isEvaluated())
                                        <div class="flex items-center gap-4 mb-4">
                                            <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $submission->result }}%"></div>
                                            </div>
                                            <span class="text-xs font-black text-slate-700">{{ $submission->result }}%</span>
                                        </div>
                                    @endif

                                    <a href="{{ route('user.mentor.submissions.show', $submission) }}" 
                                       class="w-full bg-slate-50 border border-slate-100 text-slate-600 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-all flex items-center justify-center gap-2">
                                        {{ in_array($slug, ['submitted', 'ai_evaluated']) ? 'Evaluate Now' : 'View Breakdown' }}
                                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            @empty
                                <div class="p-10 text-center bg-white rounded-2xl border border-dashed border-slate-200">
                                    <p class="text-[11px] font-black text-slate-400 uppercase italic">Waiting for first strike...</p>
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </main>

            <!-- RIGHT PANEL: INTERN DOSSIER -->
            <aside class="w-[340px] bg-white border-l border-slate-200 flex flex-col shrink-0 z-40 p-8 space-y-8 shadow-[-4px_0_10px_rgba(0,0,0,0.01)] overflow-y-auto custom-scrollbar">
                
                <div class="text-center">
                    <div class="w-24 h-24 bg-indigo-50 border-4 border-white shadow-xl rounded-3xl flex items-center justify-center font-black text-3xl text-indigo-600 mx-auto mb-6">
                        {{ substr($intern->name, 0, 1) }}
                    </div>
                    <h3 class="text-xl font-black text-slate-900">{{ $intern->name }}</h3>
                    <p class="text-xs font-bold text-slate-400 mt-1">{{ $intern->email }}</p>
                </div>

                <div class="space-y-6 pt-8 border-t border-slate-100">
                    <div>
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Mentor Observations</h4>
                        <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                            <p class="text-[11px] text-amber-700 leading-relaxed font-semibold italic">"Intern shows high proficiency in problem-solving but needs to work on documentation consistency."</p>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Quick Links</h4>
                        <div class="grid gap-2">
                            <button class="w-full flex items-center justify-between p-3 bg-slate-50 rounded-xl text-[11px] font-black text-slate-700 hover:bg-slate-100 transition-all group">
                                <span>Recent Feedback Log</span>
                                <i data-lucide="history" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-600"></i>
                            </button>
                            <button class="w-full flex items-center justify-between p-3 bg-slate-50 rounded-xl text-[11px] font-black text-slate-700 hover:bg-slate-100 transition-all group">
                                <span>Export Progress PDF</span>
                                <i data-lucide="download" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-600"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-auto p-6 bg-slate-900 rounded-2xl">
                    <div class="flex items-center gap-2 text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3">
                        <i data-lucide="award" class="w-3.5 h-3.5"></i> Global Rank
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-white">#12</span>
                        <span class="text-[10px] font-bold text-slate-400">in cohort</span>
                    </div>
                </div>
            </aside>
        </div>

    </div>

    <script>
        if (window.lucide) { window.lucide.createIcons(); }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
    </style>
</x-app-layout>