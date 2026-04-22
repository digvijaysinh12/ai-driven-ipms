<!-- resources/views/intern/tasks/components/question-renderer.blade.php -->
<div class="animate-fadeIn" x-show="currentQuestion">
    <!-- Question Header -->
    <div class="mb-8">
        <template x-if="totalQuestions > 0">
            <div class="text-sm font-semibold text-slate-400 mb-2 tracking-wide uppercase" x-text="'Question ' + (currentIndex + 1) + ' of ' + totalQuestions"></div>
        </template>
        <template x-if="totalQuestions === 0">
            <div class="text-sm font-semibold text-indigo-500 mb-2 tracking-wide uppercase">Direct submission task</div>
        </template>
        <h2 class="text-2xl font-black text-slate-900 mb-2 leading-tight" x-text="currentQuestion.question"></h2>
        <p class="text-sm text-slate-500 font-medium" x-text="getInstruction(currentQuestion.type)"></p>
    </div>

    <!-- Dynamic Type Content -->
    <div x-show="currentQuestion.type === 'mcq'">
        @include('intern.tasks.questions.mcq')
    </div>
    <div x-show="currentQuestion.type === 'true_false'">
        @include('intern.tasks.questions.true_false')
    </div>
    <div x-show="currentQuestion.type === 'blank'">
        @include('intern.tasks.questions.blank')
    </div>
    <div x-show="currentQuestion.type === 'descriptive'">
        @include('intern.tasks.questions.descriptive')
    </div>
    <div x-show="currentQuestion.type === 'coding'">
        @include('intern.tasks.questions.coding')
    </div>
    <div x-show="currentQuestion.type === 'file'">
        @include('intern.tasks.questions.file')
    </div>
    <div x-show="currentQuestion.type === 'github'">
        @include('intern.tasks.questions.github')
    </div>

    <!-- Navigation Buttons -->
    <template x-if="totalQuestions > 0">
        <div class="mt-12 flex items-center gap-3 pb-10">
            <button 
                @click="prev()"
                :disabled="currentIndex === 0"
                class="bg-white border border-slate-200 text-slate-400 font-black px-6 py-2.5 rounded-lg disabled:opacity-30 flex items-center gap-2 transition-all"
                :class="currentIndex !== 0 ? 'hover:bg-slate-50 hover:text-slate-900 border-slate-300' : ''"
            >
                Previous
            </button>
            <button 
                @click="next()"
                :disabled="currentIndex === totalQuestions - 1"
                class="bg-white border border-slate-200 text-slate-900 font-black px-8 py-2.5 rounded-lg disabled:opacity-30 hover:bg-slate-50 border-slate-300 transition-all font-bold"
            >
                Next
            </button>
        </div>
    </template>
</div>
