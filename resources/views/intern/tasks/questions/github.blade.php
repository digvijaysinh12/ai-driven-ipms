<!-- resources/views/intern/tasks/questions/github.blade.php -->
<div class="space-y-6">
    <div class="relative group">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <i data-lucide="github" class="w-5 h-5 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
        </div>
        <input 
            type="url" 
            :value="getAnswer(currentQuestion.id)"
            @input.debounce.500ms="saveProgress(currentQuestion.id, $event.target.value)"
            placeholder="https://github.com/username/repository"
            class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-50 focus:border-blue-600 outline-none text-slate-700 font-bold shadow-sm transition-all"
        >
    </div>
    <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-6 flex gap-4">
        <div class="shrink-0 w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
            <i data-lucide="info" class="w-6 h-6"></i>
        </div>
        <div class="space-y-1">
            <p class="text-sm font-bold text-blue-900">Make sure your repository is public</p>
            <p class="text-[13px] text-blue-700 font-medium leading-relaxed">Our automated engine will clone your repository for initial analysis. Ensure the README file contains setup instructions.</p>
        </div>
    </div>
</div>
