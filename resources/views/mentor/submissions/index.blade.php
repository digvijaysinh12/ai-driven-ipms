<x-app-layout>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Submissions</h1>
                <p class="text-slate-500 mt-1 font-medium">Review and evaluate intern progress on assigned tasks.</p>
            </div>
        </div>

        <!-- Submissions Table -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Intern</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Task</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Submitted</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-widest px-8">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($submissions as $submission)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                            {{ substr($submission->intern->name ?? 'U', 0, 1) }}
                                        </div>
                                        <span class="font-bold text-slate-900">{{ $submission->intern->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700">{{ $submission->task->title ?? 'Untitled Task' }}</span>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $submission->task->type->name ?? 'General' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $slug = $submission->status?->slug;
                                        $colorClass = match($slug) {
                                            'completed' => 'bg-emerald-100 text-emerald-700',
                                            'submitted' => 'bg-indigo-100 text-indigo-700',
                                            'ai_evaluated' => 'bg-purple-100 text-purple-700',
                                            'ai_evaluating' => 'bg-amber-100 text-amber-700',
                                            default => 'bg-slate-100 text-slate-600'
                                        };
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $colorClass }}">
                                        {{ $submission->status?->name ?? 'Pending' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-slate-600">{{ $submission->submitted_at?->diffForHumans() ?? '—' }}</span>
                                        <span class="text-[10px] font-bold text-slate-400">{{ $submission->submitted_at?->format('M d, Y') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right px-8">
                                    <a href="{{ route('user.mentor.submissions.show', $submission) }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 px-4 py-2 rounded-xl text-xs font-black text-slate-600 hover:bg-slate-50 transition-all active:scale-95">
                                        Review <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-4">
                                            <i data-lucide="inbox" class="w-8 h-8 text-slate-200"></i>
                                        </div>
                                        <p class="text-slate-400 font-bold tracking-tight text-lg">No submissions to review</p>
                                        <p class="text-slate-400 text-sm">Recently submitted tasks will appear here for evaluation.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
