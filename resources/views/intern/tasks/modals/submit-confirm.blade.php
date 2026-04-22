<div
    id="wsSubmitModal"
    class="fixed inset-0 z-50 hidden"
    aria-hidden="true"
>
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" data-modal-close="wsSubmitModal"></div>

    <div class="relative h-full w-full flex items-end sm:items-center justify-center p-4">
        <div class="w-full max-w-3xl bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-sm font-black text-slate-900">Confirm submission</div>
                        <div class="text-xs font-medium text-slate-500">You're about to submit this task for evaluation.</div>
                    </div>
                </div>
                <button type="button" class="p-2 rounded-2xl hover:bg-slate-100 transition-colors text-slate-500" data-modal-close="wsSubmitModal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 space-y-5">
                <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Summary</div>
                        <div class="text-xs font-black text-slate-500" id="wsSubmitSummary">—</div>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-slate-200 overflow-hidden">
                        <div id="wsSubmitBar" class="h-full bg-emerald-500 transition-all" style="width: 0%"></div>
                    </div>
                    <div class="mt-3 text-xs text-slate-600 font-medium" id="wsSubmitHint">
                        You can submit with unanswered items — they'll be recorded as empty.
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-slate-100 bg-white p-4">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Unanswered</div>
                        <div class="mt-2 text-sm text-slate-700" id="wsSubmitUnanswered">—</div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white p-4">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ready</div>
                        <div class="mt-2 text-sm text-slate-700" id="wsSubmitAnswered">—</div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2 sm:justify-end">
                    <button type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-slate-100 text-slate-800 text-xs font-black hover:bg-slate-200 transition-colors" data-modal-close="wsSubmitModal">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Keep working
                    </button>
                    <button type="button" id="wsConfirmSubmit" class="inline-flex items-center justify-center gap-2 px-5 py-2 rounded-2xl bg-indigo-600 text-white text-xs font-black shadow-sm shadow-indigo-100 hover:bg-indigo-700 transition-colors">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Submit now
                    </button>
                </div>

                <div id="wsSubmitError" class="hidden rounded-2xl bg-rose-50 border border-rose-100 p-4 text-sm text-rose-800"></div>
            </div>
        </div>
    </div>
</div>
