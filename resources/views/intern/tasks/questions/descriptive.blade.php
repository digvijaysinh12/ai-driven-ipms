<!-- resources/views/intern/tasks/questions/descriptive.blade.php -->
<div class="space-y-4">
    <textarea 
        :value="getAnswer(currentQuestion.id)"
        @input.debounce.500ms="saveProgress(currentQuestion.id, $event.target.value)"
        rows="12"
        class="w-full bg-white border border-slate-200 rounded-2xl p-6 focus:ring-4 focus:ring-blue-50 focus:border-blue-600 outline-none text-slate-700 font-medium placeholder:text-slate-400 resize-none shadow-sm transition-all"
        placeholder="Write your answer here..."
    ></textarea>
    <div class="flex items-center gap-6 px-6 py-4 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-500">
        <div class="flex items-center gap-2 uppercase tracking-widest">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            Word Count: <span class="text-slate-900" x-text="getAnswer(currentQuestion.id) ? getAnswer(currentQuestion.id).trim().split(/\s+/).length : 0">0</span>
        </div>
        <div class="w-px h-4 bg-slate-100"></div>
        <div class="uppercase tracking-widest">
            Character Count: <span class="text-slate-900" x-text="getAnswer(currentQuestion.id) ? getAnswer(currentQuestion.id).length : 0">0</span>
        </div>
    </div>
</div>
