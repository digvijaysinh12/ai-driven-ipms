<x-app-layout>
    <div class="space-y-8 pb-12">
        
        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-black text-indigo-500 uppercase tracking-widest">Mentorship</span>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Assigned Interns</h1>
                <p class="text-slate-500 mt-1 font-medium">Track progress and provide guidance to your direct mentees.</p>
            </div>
        </div>

        <!-- GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($interns as $intern)
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden group hover:border-indigo-200 transition-all active:scale-[0.98]">
                    
                    <!-- Top Info -->
                    <div class="p-6 pb-2 flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center font-black text-xl text-indigo-600 border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                {{ substr($intern->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 leading-tight">{{ $intern->name }}</h3>
                                <p class="text-xs font-bold text-slate-400 truncate max-w-[150px]">{{ $intern->email }}</p>
                            </div>
                        </div>
                        
                        @if($intern->pending_reviews > 0)
                            <div class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-[10px] font-black uppercase tracking-wider animate-pulse">
                                {{ $intern->pending_reviews }} PENDING
                            </div>
                        @endif
                    </div>

                    <!-- Stats -->
                    <div class="p-6 pt-4 space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                                <span class="text-[9px] font-black text-slate-400 uppercase block mb-1">Submissions</span>
                                <span class="text-lg font-black text-slate-900">{{ $intern->total_submissions }}</span>
                            </div>
                            <div class="p-3 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                                <span class="text-[9px] font-black text-indigo-400 uppercase block mb-1">Since</span>
                                <span class="text-xs font-black text-indigo-900">{{ \Carbon\Carbon::parse($intern->assigned_at)->format('M Y') }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 pt-2">
                            <a href="{{ route('user.mentor.interns.progress', $intern->id) }}" 
                               class="flex-1 bg-slate-900 text-white py-3 rounded-xl font-black text-xs uppercase tracking-widest text-center shadow-lg shadow-slate-100 hover:bg-slate-800 transition-all">
                                Full Progress
                            </a>
                            <a href="mailto:{{ $intern->email }}" class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-50 transition-all">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </a>
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed border-slate-200">
                    <div class="w-20 h-20 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="user-x" class="w-10 h-10 text-slate-200"></i>
                    </div>
                    <h3 class="text-slate-900 font-black tracking-tight text-2xl">No Interns Assigned</h3>
                    <p class="text-slate-400 mt-2 max-w-sm mx-auto font-medium">You currently don't have any interns assigned to your mentorship track. Contact HR if this is an error.</p>
                </div>
            @endforelse
        </div>

    </div>
</x-app-layout>