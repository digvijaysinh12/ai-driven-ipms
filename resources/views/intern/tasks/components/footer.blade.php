<!-- resources/views/intern/tasks/components/footer.blade.php -->
<footer class="h-[70px] bg-white border-t border-slate-200 px-6 flex items-center justify-between shrink-0 relative z-[60]">
    <div class="flex flex-col gap-2 min-w-[300px]">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 italic">
                <i data-lucide="cloud-check" class="w-4 h-4 text-emerald-500" x-show="!isSaving"></i>
                <i data-lucide="loader-2" class="w-4 h-4 text-blue-500 animate-spin" x-show="isSaving"></i>
                <span x-text="isSaving ? 'Saving changes...' : 'All changes saved'">All changes saved</span>
            </div>
            <span class="text-[11px] font-black text-slate-800 uppercase" x-text="Math.round(progress) + '% completed'">0% completed</span>
        </div>
        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full bg-blue-600 rounded-full transition-all duration-700 ease-out" :style="'width: ' + progress + '%'"></div>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button 
            @click="submitTask()"
            :disabled="isSubmitting"
            class="bg-blue-600 text-white px-8 py-3 rounded-xl font-black text-sm hover:bg-blue-700 transition-all shadow-xl shadow-blue-500/20 active:scale-95 disabled:opacity-50"
        >
            <span x-show="!isSubmitting">Submit Task</span>
            <span x-show="isSubmitting" class="flex items-center gap-2">
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                Submitting...
            </span>
        </button>
    </div>
    
    <!-- Floating Question Mark -->
    <button class="absolute -top-12 right-6 w-10 h-10 bg-slate-800 text-white rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-transform">
        <i data-lucide="help-circle" class="w-5 h-5"></i>
    </button>
</footer>
