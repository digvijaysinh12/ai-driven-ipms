<!-- resources/views/intern/tasks/questions/true_false.blade.php -->
<div class="flex gap-4">
    <template x-for="val in ['True', 'False']" :key="val">
        <button 
            @click="saveProgress(currentQuestion.id, val)"
            class="flex-1 bg-white border rounded-2xl p-8 flex flex-col items-center gap-4 transition-all duration-300 hover:scale-[1.02]"
            :class="getAnswer(currentQuestion.id) === val ? 'border-blue-600 ring-4 ring-blue-50 shadow-lg' : 'border-slate-200 hover:border-slate-300'"
        >
            <div class="w-16 h-16 rounded-full flex items-center justify-center"
                :class="val === 'True' ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-50 text-rose-500'"
            >
                <i :data-lucide="val === 'True' ? 'check' : 'x'" class="w-8 h-8 stroke-[3]"></i>
            </div>
            <span class="text-lg font-black text-slate-800" x-text="val"></span>
        </button>
    </template>
</div>
