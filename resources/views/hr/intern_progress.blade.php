<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Progress Tracking</h2>
        <p class="text-sm text-slate-500 font-medium">Detailed assignment status and performance monitoring for all interns.</p>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.intern.mentor.list') }}" class="btn btn-secondary px-4 py-2 text-xs font-bold uppercase tracking-widest gap-2">
            <i data-lucide="map" class="w-4 h-4"></i>
            View Assignment Map
        </a>
    </x-slot>

    <x-card title="Accessing Detailed Reports" icon="sparkles">
        <div class="flex items-start gap-8">
            <div class="flex-1 space-y-6">
                <p class="text-sm text-slate-600 leading-relaxed font-medium">
                    Individual progress reports are linked directly to active mentorship pairings. To view a detailed status report for an intern, please follow these steps:
                </p>
                
                <div class="space-y-4">
                    @foreach([
                        ['icon' => 'map-pin', 'text' => 'Navigate to the <a href="'.route('admin.intern.mentor.list').'" class="font-bold text-indigo-600 hover:underline transition-all">Assignment Map</a>.'],
                        ['icon' => 'search', 'text' => 'Locate the intern in the management table (use search if needed).'],
                        ['icon' => 'external-link', 'text' => 'Click the <strong>View Progress</strong> action button to open their dashboard.']
                    ] as $index => $step)
                        <div class="flex items-center gap-4 group">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 group-hover:bg-indigo-900 group-hover:text-white flex items-center justify-center font-bold text-sm transition-all shadow-sm border border-slate-100">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1 text-sm text-slate-600 font-medium">
                                {!! $step['text'] !!}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 pt-8 border-t border-slate-100 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed max-w-md">
                        The system centralizes submissions, evaluations, and mentor feedback for efficient monitoring.
                    </p>
                </div>
            </div>

            <div class="hidden lg:block w-72 h-72 bg-slate-50 rounded-3xl border border-slate-200 border-dashed flex-shrink-0 flex items-center justify-center">
                <i data-lucide="line-chart" class="w-24 h-24 text-slate-200"></i>
            </div>
        </div>
    </x-card>
</x-app-layout>
