<!-- resources/views/intern/tasks/questions/file.blade.php -->
<div class="bg-white border-2 border-dashed border-slate-300 rounded-3xl p-16 flex flex-col items-center justify-center gap-6 group hover:border-blue-400 hover:bg-blue-50/20 transition-all cursor-pointer relative"
    @click="$refs.fileInput.click()"
>
    <input 
        type="file" 
        x-ref="fileInput" 
        class="hidden" 
        @change="handleFileUpload($event, currentQuestion.id)"
    >
    <div class="w-20 h-20 bg-slate-50 rounded-2xl flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
        <i data-lucide="upload-cloud" class="w-10 h-10 stroke-[2]"></i>
    </div>
    <div class="text-center">
        <h4 class="text-lg font-black text-slate-800 mb-1" x-text="uploadedFileName(currentQuestion.id) || 'Drag and drop your file here'"></h4>
        <p class="text-sm text-slate-400 font-medium">Supported formats: .zip, .pdf, .txt (Max 10MB)</p>
    </div>
    <template x-if="getAnswer(currentQuestion.id)">
        <div class="flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-xs font-bold border border-emerald-100">
            <i data-lucide="check-circle" class="w-3 h-3"></i>
            <span>File uploaded successfully</span>
        </div>
    </template>
    <button class="px-6 py-3 bg-slate-900 text-white text-sm font-black rounded-xl hover:bg-blue-600 transition-all shadow-md active:scale-95">Browse Files</button>
</div>
