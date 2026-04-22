<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-900">My Submissions</h2>
        <p class="text-sm text-slate-500">Track what you have submitted and reviews from your mentor.</p>
    </x-slot>

    <div class="grid gap-4">
        @forelse($submissions as $submission)
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Task</p>
                        <h3 class="text-lg font-semibold text-slate-900">{{ optional($submission->task)->title ?? 'Untitled Task' }}</h3>
                        <p class="text-sm text-slate-500 mt-1">Submitted {{ optional($submission->submitted_at)->diffForHumans() ?? '—' }}</p>
                        @if($submission->review)
                            <div class="mt-2 text-sm text-emerald-600 font-semibold">Reviewed: {{ $submission->review->grade }}</div>
                        @else
                            <div class="mt-2 text-sm text-amber-600 font-semibold">Pending review</div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ ucfirst($submission->status ?? 'pending') }}</span>
                        <x-ui.button :href="route('user.intern.submissions.show', $submission)" size="sm" variant="secondary">Details</x-ui.button>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-dashed border-slate-200 rounded-xl p-8 text-center text-slate-500">
                No submissions yet. Complete a task to see it here.
            </div>
        @endforelse
    </div>
</x-app-layout>
