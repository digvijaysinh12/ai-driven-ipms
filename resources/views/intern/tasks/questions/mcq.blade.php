<!-- resources/views/intern/tasks/questions/mcq.blade.php -->
<div class="grid gap-3">
    <template x-for="(option, oIdx) in currentQuestion.options" :key="oIdx">
        <label 
            class="group relative flex items-center p-4 bg-white border rounded-xl cursor-pointer transition-all duration-200 hover:border-blue-400"
            :class="getAnswer(currentQuestion.id) === option ? 'border-blue-600 bg-blue-50/50 ring-1 ring-blue-600 shadow-sm' : 'border-slate-200 shadow-[0_1px_2px_rgba(0,0,0,0.02)]'"
        >
            <input type="radio" class="sr-only" :name="'question_' + currentQuestion.id" :value="option" 
                @change="saveProgress(currentQuestion.id, option)"
                :checked="getAnswer(currentQuestion.id) === option"
            >
            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center mr-4 transition-colors"
                :class="getAnswer(currentQuestion.id) === option ? 'border-blue-600 bg-blue-600' : 'border-slate-300 group-hover:border-blue-400'"
            >
                <div class="w-2 h-2 rounded-full bg-white transition-opacity" :class="getAnswer(currentQuestion.id) === option ? 'opacity-100' : 'opacity-0'"></div>
            </div>
            <span class="text-sm font-bold text-slate-700" x-text="option"></span>
        </label>
    </template>
</div>
