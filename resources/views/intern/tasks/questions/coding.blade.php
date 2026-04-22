<!-- resources/views/intern/tasks/questions/coding.blade.php -->
<div class="flex flex-col gap-4">
    <div class="bg-slate-900 rounded-2xl overflow-hidden shadow-2xl border border-slate-800">
        <!-- Editor Toolbar -->
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-800 bg-slate-900/50">
            <div class="flex items-center gap-4">
                <select 
                    @change="switchLanguage(currentQuestion.id, $event.target.value)"
                    class="bg-slate-800 text-slate-300 text-xs font-bold border-slate-700 rounded-lg focus:ring-0"
                >
                    <option value="javascript">JavaScript</option>
                    <option value="python">Python</option>
                    <option value="cpp">C++</option>
                    <option value="c">C</option>
                    <option value="java">Java</option>
                    <option value="php">PHP</option>
                </select>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 font-bold uppercase tracking-widest">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    Live Editor
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    @click="runCode()" 
                    :disabled="isExecuting"
                    class="px-3 py-1.5 border border-emerald-500 text-emerald-500 text-xs font-black rounded-lg hover:bg-emerald-500 hover:text-white transition-all uppercase tracking-tight disabled:opacity-50"
                >
                    <span x-show="!isExecuting">Run Code</span>
                    <span x-show="isExecuting">Running...</span>
                </button>
                <button @click="saveProgress(currentQuestion.id, getAnswer(currentQuestion.id))" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-black rounded-lg hover:bg-blue-700 transition-all shadow-lg shadow-blue-900/20 uppercase tracking-tight">Save Progress</button>
            </div>
        </div>
        <!-- Editor Area -->
        <div 
            :id="'editor_' + currentQuestion.id" 
            class="w-full h-[400px] bg-[#1e1e1e]"
            wire:ignore
        ></div>
        <!-- Console Output -->
        <div class="bg-black/40 border-t border-slate-800 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Output Console</div>
                <button @click="clearConsole()" class="text-[10px] font-black text-slate-400 hover:text-white uppercase tracking-wider transition-colors">Clear Console</button>
            </div>
            <div 
                class="font-mono text-xs p-4 rounded-lg border border-slate-800/50 min-h-[80px] whitespace-pre-wrap" 
                :class="consoleError ? 'text-rose-400 bg-rose-950/20 border-rose-900/30' : 'text-slate-300 bg-slate-900/30 border-slate-800/50'"
                x-text="consoleOutput || '> Output will appear here after execution...'"
            ></div>
        </div>
    </div>
    
    <!-- Test Cases -->
    <template x-if="currentQuestion.test_cases">
        <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
            <div class="px-5 py-3 bg-slate-50 border-b font-bold text-xs uppercase tracking-widest text-slate-500">Test Cases</div>
            <div class="p-4 grid gap-3">
                <template x-for="(testCase, tcIdx) in currentQuestion.test_cases" :key="tcIdx">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-slate-300"></div>
                            <span class="text-xs font-bold text-slate-700" x-text="'Test Case ' + (tcIdx + 1)"></span>
                        </div>
                        <span class="text-[10px] font-black text-slate-400">PENDING</span>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
