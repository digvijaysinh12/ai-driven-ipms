<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Submission</p>
            <h2 class="text-2xl font-bold text-slate-900">{{ optional($submission->task)->title ?? 'Task' }}</h2>
        </div>
    </x-slot>

    <div class="grid gap-6">
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="text-sm text-slate-500">Submitted at {{ optional($submission->submitted_at)->format('d M Y h:i A') ?? 'N/A' }}</div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ ucfirst($submission->status ?? 'pending') }}</span>
            </div>
            <pre class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-sm overflow-x-auto">{{ json_encode($submission->answer ?? [], JSON_PRETTY_PRINT) }}</pre>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900 mb-3">Review</h3>
            @if($submission->review)
                <div class="space-y-2 text-sm text-slate-700">
                    <div class="font-semibold text-emerald-700">Grade: {{ $submission->review->grade }}</div>
                    <div>{{ $submission->review->feedback }}</div>
                </div>
            @else
                <div class="text-slate-500 text-sm">No mentor review yet.</div>
            @endif
        </div>
    </div>
</x-app-layout>
