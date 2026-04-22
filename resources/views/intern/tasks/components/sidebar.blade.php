<!-- resources/views/intern/tasks/components/sidebar.blade.php -->
<aside class="w-[250px] bg-white border-r border-slate-200 flex flex-col shrink-0">
    <div class="p-5 flex-1 overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-base font-bold text-slate-900 uppercase tracking-tight" x-text="totalQuestions > 0 ? 'Questions' : 'Task Overview'">Questions</h2>
            <template x-if="totalQuestions > 0">
                <span class="text-sm font-medium text-slate-400" x-text="answeredCount + '/' + totalQuestions">0/0</span>
            </template>
        </div>

        <!-- Question Grid -->
        <template x-if="totalQuestions > 0">
            <div class="grid grid-cols-4 gap-2 mb-8">
                <template x-for="(q, index) in task.questions" :key="q.id">
                    <button 
                        @click="currentIndex = index"
                        :class="{
                            'bg-blue-600 text-white shadow-blue-200 shadow-lg border-blue-600': currentIndex === index,
                            'bg-slate-50 text-slate-400 border-slate-100 hover:bg-slate-100': currentIndex !== index && !isAnswered(q.id),
                            'bg-white text-slate-600 border-slate-200 hover:bg-slate-50': currentIndex !== index && isAnswered(q.id)
                        }"
                        class="relative w-full aspect-square text-sm font-bold rounded-lg border flex items-center justify-center transition-all duration-200"
                    >
                        <span x-text="'Q' + (index + 1)"></span>
                        <template x-if="isAnswered(q.id)">
                            <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-500 rounded-full border-2 border-white shadow-sm animate-pulse"></div>
                        </template>
                    </button>
                </template>
            </div>
        </template>
        
        <template x-if="totalQuestions === 0">
            <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                    </div>
                    <div class="text-xs font-black text-indigo-900 uppercase tracking-wider">Submission</div>
                </div>
                <p class="text-xs text-indigo-700 font-medium leading-relaxed">
                    This task requires a direct submission (File or Link) instead of multiple questions.
                </p>
            </div>
        </template>

        <!-- Legend -->
        <div class="space-y-3 pt-6 border-t border-slate-50">
            <div class="flex items-center gap-3 text-xs font-semibold text-slate-500">
                <div class="w-3 h-3 bg-emerald-100 border border-emerald-200 rounded-sm"></div>
                <span>Answered</span>
            </div>
            <div class="flex items-center gap-3 text-xs font-semibold text-slate-500">
                <div class="w-3 h-3 bg-slate-100 border border-slate-200 rounded-sm"></div>
                <span>Not Answered</span>
            </div>
            <div class="flex items-center gap-3 text-xs font-semibold text-slate-500">
                <div class="w-3 h-3 bg-blue-600 rounded-sm shadow-sm"></div>
                <span>Current</span>
            </div>
        </div>
    </div>
</aside>
