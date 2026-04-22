<x-app-layout>
    <div class="h-screen flex flex-col bg-slate-50 overflow-hidden">
        
        <!-- HEADER -->
        <header class="h-[65px] bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0 z-50">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.mentor.tasks.index') }}" class="w-10 h-10 rounded-xl flex items-center justify-center hover:bg-slate-100 transition-colors border border-slate-200">
                    <i data-lucide="arrow-left" class="w-5 h-5 text-slate-600"></i>
                </a>
                <div>
                    <h1 class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Task Management</h1>
                    <h2 class="text-lg font-black text-slate-900 leading-none truncate max-w-[400px]">
                        {{ $task->title }}
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 pr-6 border-r border-slate-200 h-8">
                    <span class="px-3 py-1 rounded-lg border border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-500 bg-slate-50">
                        {{ $task->type->name ?? 'General' }}
                    </span>
                    <span class="px-3 py-1 rounded-lg border border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-500 bg-slate-50">
                        {{ strtoupper($task->difficulty) }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    @php $supported = ['mcq','descriptive','true_false','blank','coding']; @endphp

                    @if($task->isDraft())
                        @if(in_array($task->type->slug, $supported))
                            <button id="generateBtn" class="bg-indigo-600 text-white px-4 py-2 rounded-xl font-bold text-xs hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Generate Questions
                            </button>
                        @endif

                        <button id="markReadyBtn" class="bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-800 transition-all flex items-center gap-2">
                             Mark as Ready <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                        </button>
                    @endif

                    @if($task->isReady())
                        <button onclick="openAssignModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-xl font-bold text-xs hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
                            Assign to Interns <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        </button>
                    @endif
                </div>
            </div>
        </header>

        <!-- MAIN LAYOUT -->
        <div class="flex-1 flex overflow-hidden">
            
            <!-- LEFT PANEL: DETAILS & QUESTIONS -->
            <main class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <div class="max-w-[1000px] mx-auto space-y-8 pb-20">
                    
                    <!-- CONTEXT CARD -->
                    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group">
                        <div class="relative z-10">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Mission Briefing
                            </h3>
                            <p class="text-slate-600 leading-relaxed text-sm whitespace-pre-wrap">{{ $task->description }}</p>
                        </div>
                        <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-slate-50 rounded-full blur-2xl group-hover:bg-indigo-50 transition-all duration-500"></div>
                    </div>

                    <!-- DYNAMIC CONTENT AREA -->
                    <div id="taskContent" class="space-y-6">
                        @include('mentor.tasks.partials.show-content', [
                            'task' => $task,
                            'questions' => $questions,
                            'submissions' => $submissions
                        ])
                    </div>
                </div>
            </main>

            <!-- RIGHT PANEL: TASK STATUS & QUICK STATS -->
            <aside class="w-[340px] bg-white border-l border-slate-200 flex flex-col shrink-0 z-40 p-8 space-y-8 shadow-[-4px_0_10px_rgba(0,0,0,0.01)]">
                <div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Task Analytics</h3>
                    
                    <div class="space-y-4">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase">Questions</span>
                            <span class="text-lg font-black text-slate-900">{{ $questions->count() }}</span>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase">Submissions</span>
                            <span class="text-lg font-black text-slate-900">{{ $submissions->total() }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Execution Log</h3>
                    @forelse($submissions->take(3) as $sub)
                        <div class="flex gap-3 mb-6 last:mb-0">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center font-bold text-xs text-indigo-600 shrink-0">
                                {{ substr($sub->intern->name, 0, 1) }}
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-[11px] font-bold text-slate-700 truncate capitalize">{{ $sub->intern->name }} submitted</p>
                                <p class="text-[10px] text-slate-400 font-medium">{{ $sub->submitted_at?->diffForHumans() ?? 'In Progress' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-[11px] text-slate-400 italic">No activity recorded yet.</p>
                    @endforelse
                </div>

                <div class="mt-auto p-6 bg-slate-900 rounded-2xl">
                    <div class="flex items-center gap-2 text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i> Mentor Tip
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed font-medium">Click "Mark as Ready" to lock the questions and enable assignment to interns.</p>
                </div>
            </aside>
        </div>

    </div>

    <!-- MODALS -->
    @include('mentor.tasks.partials.assign-modal', ['interns' => $interns])

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

        // Generate Questions
        $(document).on('click', '#generateBtn', function () {
            let btn = $(this);
            const original = btn.html();
            btn.html('<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i> Processing...').prop('disabled', true);
            if(window.lucide) { window.lucide.createIcons(); }

            $.ajax({
                url: "{{ route('user.mentor.tasks.generateQuestions', $task) }}",
                type: "POST",
                success: function (res) {
                    showToast(res.message);
                    setTimeout(() => location.reload(), 800);
                },
                error: function (xhr) {
                    showToast(xhr.responseJSON?.message || 'Generation failed', 'error');
                    btn.html(original).prop('disabled', false);
                    if(window.lucide) { window.lucide.createIcons(); }
                }
            });
        });

        // Save All Changes
        $(document).on('click', '#saveAllBtn', function () {
            let btn = $(this);
            const original = btn.html();
            btn.html('<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i> Saving...').prop('disabled', true);
            if(window.lucide) { window.lucide.createIcons(); }

            $.ajax({
                url: "{{ route('user.mentor.tasks.questions.bulk-update', $task) }}",
                type: "PUT",
                data: $('#bulkUpdateForm').serialize(),
                success: function () {
                    showToast('Changes saved successfully');
                    btn.html(original).prop('disabled', false);
                    if(window.lucide) { window.lucide.createIcons(); }
                },
                error: function (xhr) {
                    showToast(xhr.responseJSON?.message || 'Failed to save', 'error');
                    btn.html(original).prop('disabled', false);
                    if(window.lucide) { window.lucide.createIcons(); }
                }
            });
        });

        // Mark Ready
        $(document).on('click', '#markReadyBtn', function () {
            if(!confirm('Marking this task as ready will lock its structure. Continue?')) return;
            let btn = $(this);
            btn.text('Processing...').prop('disabled', true);

            $.ajax({
                url: "{{ route('user.mentor.tasks.markReady', $task) }}",
                type: "POST",
                success: function (res) {
                    showToast(res.message);
                    setTimeout(() => location.reload(), 800);
                },
                error: function (xhr) {
                    showToast(xhr.responseJSON?.message || 'Error occurred', 'error');
                    btn.text('Mark as Ready').prop('disabled', false);
                }
            });
        });

        function openAssignModal() { $('#assignModal').removeClass('hidden').addClass('flex'); }
        function closeAssignModal() { $('#assignModal').addClass('hidden').removeClass('flex'); }

        // Toast Helper
        function showToast(message, type = 'success') {
            const bg = type === 'error' ? 'bg-rose-500' : 'bg-emerald-500';
            const toast = $(`<div class="fixed top-8 right-8 ${bg} text-white px-6 py-3 rounded-2xl font-black text-xs shadow-2xl z-[100] animate-fadeIn">${message}</div>`);
            $('body').append(toast);
            setTimeout(() => toast.fadeOut(300, () => toast.remove()), 3000);
        }

        if (window.lucide) { window.lucide.createIcons(); }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
        .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</x-app-layout>