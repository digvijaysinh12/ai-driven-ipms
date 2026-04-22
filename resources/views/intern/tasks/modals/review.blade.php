<div
    id="wsReviewModal"
    class="fixed inset-0 z-50 hidden"
    aria-hidden="true"
>
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" data-modal-close="wsReviewModal"></div>

    <div class="relative h-full w-full flex items-end sm:items-center justify-center p-4">
        <div class="w-full max-w-3xl bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-500">
                        <i data-lucide="scan" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-sm font-black text-slate-900">Review</div>
                        <div class="text-xs font-medium text-slate-500">Answered vs unanswered overview</div>
                    </div>
                </div>
                <button type="button" class="p-2 rounded-2xl hover:bg-slate-100 transition-colors text-slate-500" data-modal-close="wsReviewModal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Done</div>
                        <div class="mt-1 text-xl font-black text-slate-900" id="wsReviewDone">0</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Started</div>
                        <div class="mt-1 text-xl font-black text-slate-900" id="wsReviewStarted">0</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">New</div>
                        <div class="mt-1 text-xl font-black text-slate-900" id="wsReviewNew">0</div>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Completion</div>
                        <div class="text-xs font-black text-slate-500" id="wsReviewPct">0%</div>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-slate-200 overflow-hidden">
                        <div id="wsReviewBar" class="h-full bg-indigo-600 transition-all" style="width: 0%"></div>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Items</div>
                        <button type="button" class="text-xs font-black text-slate-600 hover:text-slate-800" data-modal-close="wsReviewModal">Close</button>
                    </div>
                    <div id="wsReviewList" class="grid grid-cols-1 sm:grid-cols-2 gap-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>
