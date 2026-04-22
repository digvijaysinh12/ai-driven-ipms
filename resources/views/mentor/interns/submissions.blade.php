<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <a href="{{ route('user.mentor.interns') }}" class="text-[11px] font-bold uppercase tracking-widest text-slate-400 hover:text-indigo-600 transition-colors flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-3 h-3"></i>
                Back to Interns
            </a>
        </div>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $intern->name }}'s Progress</h2>
        <p class="text-sm text-slate-500 font-medium">Review code evaluated by AI for this specific intern.</p>
    </x-slot>

    <x-slot name="actions">
        <div class="flex items-center gap-6 bg-white px-6 py-3 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-right">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Avg Score</div>
                <div class="text-xl font-bold text-slate-900">{{ $avgScore ?? '--' }}<span class="text-sm text-slate-400 font-medium ml-0.5">/100</span></div>
            </div>
            <div class="w-px h-8 bg-slate-100"></div>
            <div class="text-right">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Evaluated</div>
                <div class="text-xl font-bold text-slate-900">{{ $evaluatedCount }}<span class="text-sm text-slate-400 font-medium mx-1">/</span>{{ $totalSubmissions }}</div>
            </div>
        </div>
    </x-slot>

    <!-- Submissions Table -->
    <x-card title="Intern Submissions" subtitle="All completed tasks and their current review status" icon="layers" :padding="false" class="mt-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 font-bold text-slate-400 uppercase tracking-widest text-[10px]">Topic & Task Type</th>
                        <th class="px-6 py-4 font-bold text-slate-400 uppercase tracking-widest text-[10px]">Status</th>
                        <th class="px-6 py-4 font-bold text-slate-400 uppercase tracking-widest text-[10px]">AI Score</th>
                        <th class="px-6 py-4 font-bold text-slate-400 uppercase tracking-widest text-[10px]">Submitted Date</th>
                        <th class="px-6 py-4 font-bold text-slate-400 uppercase tracking-widest text-[10px] text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($submissions as $submission)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $submission->question->topic->title ?? 'Unknown Topic' }}</div>
                                @if($submission->question->type)
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">
                                        <i data-lucide="code-2" class="w-3 h-3 inline-block mr-1"></i>
                                        {{ str_replace('_', ' ', $submission->question->type) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <x-badge :status="$submission->status" />
                            </td>
                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $submission->ai_total_score ? 'bg-slate-100 text-slate-900 border border-slate-200' : 'bg-slate-50 text-slate-400' }}">
                                    <span class="text-xs font-bold tabular-nums">
                                        {{ $submission->ai_total_score ?? '--' }}
                                    </span>
                                    <span class="text-[10px] font-medium text-slate-400">/ 100</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-600">
                                {{ $submission->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('user.mentor.submissions.show', $submission->id) }}" class="btn btn-ghost px-4 py-2 text-[11px] font-bold uppercase tracking-widest text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                    Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-32 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-200 border border-slate-100">
                                        <i data-lucide="inbox" class="w-8 h-8"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-slate-900 font-bold">No submissions yet</p>
                                        <p class="text-slate-400 font-medium text-xs">This intern hasn't submitted any tasks.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>
