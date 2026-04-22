<!-- resources/views/intern/tasks/questions/blank.blade.php -->
<div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm flex flex-col gap-4">
    <div class="text-lg font-bold text-slate-800 leading-relaxed" x-html="parseBlankQuestion(currentQuestion.question)"></div>
    <div class="relative">
        <input 
            type="text" 
            :value="getAnswer(currentQuestion.id)"
            @input.debounce.500ms="saveProgress(currentQuestion.id, $event.target.value)"
            class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl focus:border-blue-600 focus:ring-4 focus:ring-blue-50 px-5 py-4 text-blue-600 font-bold placeholder:text-slate-300 transition-all"
            placeholder="Type your answer here..."
        >
    </div>
</div>
