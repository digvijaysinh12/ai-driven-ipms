<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Task Details</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</th>
                <th class="px-6 py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Submissions</th>
                <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest px-8">Action</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">
        @forelse($tasks as $task)
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-6 py-5">
                    <div class="flex flex-col">
                        <span class="font-bold text-slate-900 leading-tight">{{ $task->title }}</span>
                        <span class="text-[11px] text-slate-400 mt-1 line-clamp-1 max-w-[300px]">{{ $task->description }}</span>
                    </div>
                </td>

                <td class="px-6 py-5">
                    @php
                        $colorClass = match($task->status) {
                            'draft' => 'bg-amber-50 text-amber-600 border-amber-100',
                            'ready' => 'bg-blue-50 text-blue-600 border-blue-100',
                            'assigned' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                            default => 'bg-slate-50 text-slate-600 border-slate-100'
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $colorClass }}">
                        {{ $task->status }}
                    </span>
                </td>

                <td class="px-6 py-5">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-indigo-400"></div>
                        <span class="font-semibold text-slate-700">{{ $task->type->name ?? 'General' }}</span>
                    </div>
                </td>

                <td class="px-6 py-5 text-center">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-50 rounded-lg border border-slate-100">
                        <i data-lucide="users" class="w-3 h-3 text-slate-400"></i>
                        <span class="text-xs font-black text-slate-700">{{ $task->submissions_count ?? 0 }}</span>
                    </div>
                </td>

                <td class="px-6 py-5 text-right px-8">
                    <a href="{{ route('user.mentor.tasks.show', $task->id) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 hover:bg-slate-100 transition-all active:scale-95">
                        Manage <i data-lucide="settings-2" class="w-3.5 h-3.5"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-20 text-center">
                    <div class="flex flex-col items-center">
                        <i data-lucide="layers" class="w-12 h-12 text-slate-200 mb-4"></i>
                        <p class="text-slate-400 font-bold tracking-tight">No tasks found match your criteria</p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<!-- PAGINATION -->
@if($tasks->hasPages())
    <div class="p-6 bg-slate-50 border-t border-slate-100">
        {{ $tasks->links() }}
    </div>
@endif