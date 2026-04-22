@php
    $answers = $submission->answers->keyBy('task_question_id');
@endphp

<x-app-layout>
    <div class="h-screen flex flex-col bg-slate-50 overflow-hidden">
        
        <!-- TOP NAV / HEADER -->
        <header class="h-[65px] bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0 z-50">
            <div class="flex items-center gap-4">
                <a href="{{ route('user.mentor.submissions.index') }}" class="w-10 h-10 rounded-xl flex items-center justify-center hover:bg-slate-100 transition-colors">
                    <i data-lucide="arrow-left" class="w-5 h-5 text-slate-600"></i>
                </a>
                <div>
                    <h1 class="text-sm font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Submission Review</h1>
                    <h2 class="text-lg font-black text-slate-900 leading-none truncate max-w-[400px]">
                        {{ $submission->task->title }}
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3 pr-6 border-r border-slate-200">
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-400 uppercase">Intern</p>
                        <p class="text-sm font-bold text-slate-900">{{ $submission->intern->name }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center font-black text-indigo-600">
                        {{ substr($submission->intern->name, 0, 1) }}
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider
                        {{ $submission->status?->slug === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $submission->status?->name }}
                    </span>
                </div>
            </div>
        </header>

        <!-- MAIN LAYOUT -->
        <div class="flex-1 flex overflow-hidden">
            
            <!-- LEFT CONTENT: ANSWERS LIST -->
            <main class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <div class="max-w-[850px] mx-auto space-y-8 pb-12">
                    
                    <!-- SUMMARY INFO -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">AI Score</span>
                            <span class="text-3xl font-black text-indigo-600">{{ number_format($submission->percentage, 1) }}%</span>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Mentor Score</span>
                            <span class="text-3xl font-black {{ $submission->final_percentage ? 'text-emerald-600' : 'text-slate-300' }}">
                                {{ $submission->final_percentage ? number_format($submission->final_percentage, 1).'%' : '---' }}
                            </span>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Status</span>
                            <span class="text-sm font-bold text-slate-600 mt-2">{{ $submission->status?->name }}</span>
                        </div>
                    </div>

                    <!-- QUESTION CARDS -->
                    @foreach($submission->task->questions as $index => $question)
                        @php
                            $ans = $answers->get($question->id);
                            $isCorrect = (string)($ans->answer_text ?? '') === (string)($question->correct_answer ?? '');
                            $type = $submission->task->type->slug;
                        @endphp
                        
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all hover:border-slate-300">
                            <!-- Card Header -->
                            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                                <span class="bg-white px-3 py-1 rounded-lg border border-slate-200 text-xs font-black text-slate-500">
                                    QUESTION {{ $index + 1 }}
                                </span>
                                @if(isset($ans->ai_score))
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-16 bg-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $ans->ai_score }}%"></div>
                                        </div>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">AI: {{ $ans->ai_score }}%</span>
                                    </div>
                                @endif
                            </div>

                            <div class="p-6 space-y-6">
                                <!-- Question Text -->
                                <div>
                                    <h3 class="text-lg font-bold text-slate-800 leading-relaxed">{{ $question->question }}</h3>
                                    @if($question->source)
                                        <div class="mt-4 p-4 bg-slate-900 rounded-xl">
                                            <pre class="text-sm text-indigo-300 font-mono overflow-x-auto"><code>{{ $question->source }}</code></pre>
                                        </div>
                                    @endif
                                </div>

                                <!-- User Answer Section -->
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest">User Answer</h4>
                                        @if($type === 'mcq' || $type === 'true_false')
                                            <div class="flex items-center gap-1.5 {{ $isCorrect ? 'text-emerald-500' : 'text-rose-500' }}">
                                                <i data-lucide="{{ $isCorrect ? 'check-circle' : 'x-circle' }}" class="w-4 h-4 fill-current opacity-20"></i>
                                                <span class="text-[10px] font-black uppercase">{{ $isCorrect ? 'Correct' : 'Incorrect' }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    @if($type === 'mcq')
                                        <div class="grid gap-2">
                                            @foreach($question->options as $oIdx => $option)
                                                @php
                                                    $isUserSelected = (string)($ans->answer_text ?? '') === (string)$oIdx;
                                                    $isCorrectOption = (string)($question->correct_answer ?? '') === (string)$oIdx;
                                                @endphp
                                                <div class="p-4 rounded-xl border flex items-center justify-between transition-all
                                                    @if($isCorrectOption) bg-emerald-50 border-emerald-200 text-emerald-900
                                                    @elseif($isUserSelected) bg-rose-50 border-rose-200 text-rose-900
                                                    @else bg-white border-slate-100 text-slate-600 @endif">
                                                    <span class="text-sm font-semibold truncate">{{ $option }}</span>
                                                    @if($isCorrectOption)
                                                        <i data-lucide="check" class="w-4 h-4"></i>
                                                    @elseif($isUserSelected)
                                                        <i data-lucide="x" class="w-4 h-4"></i>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($type === 'true_false')
                                        <div class="flex gap-4">
                                            <div class="flex-1 p-4 rounded-xl border text-center font-bold text-sm
                                                {{ (string)($ans->answer_text ?? '') === 'True' ? 'bg-indigo-600 text-white' : 'bg-slate-50 text-slate-400' }}">
                                                True
                                            </div>
                                            <div class="flex-1 p-4 rounded-xl border text-center font-bold text-sm
                                                {{ (string)($ans->answer_text ?? '') === 'False' ? 'bg-indigo-600 text-white' : 'bg-slate-50 text-slate-400' }}">
                                                False
                                            </div>
                                        </div>
                                    @elseif($type === 'coding')
                                        <div class="bg-slate-900 rounded-xl p-6 relative group border border-slate-800">
                                            <div class="absolute right-4 top-4 text-[10px] font-bold text-slate-600 hidden group-hover:block transition-all">LANGUAGE: {{ $submission->task->language ?? 'JS' }}</div>
                                            <pre class="text-sm text-emerald-400 font-mono leading-relaxed overflow-x-auto whitespace-pre-wrap"><code>{{ $ans->answer_text ?? '// No code provided' }}</code></pre>
                                        </div>
                                    @elseif($type === 'file')
                                        <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                                            <div class="w-12 h-12 bg-white rounded-lg border border-slate-200 flex items-center justify-center text-slate-400">
                                                <i data-lucide="file" class="w-6 h-6"></i>
                                            </div>
                                            <div class="flex-1 overflow-hidden">
                                                <p class="text-sm font-bold text-slate-800 truncate">{{ $ans->file_path ?? 'No file' }}</p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Document Upload</p>
                                            </div>
                                            @if($ans->file_path)
                                                <a href="{{ Storage::url($ans->file_path) }}" target="_blank" 
                                                   class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all flex items-center gap-2">
                                                    <i data-lucide="download" class="w-3.5 h-3.5"></i> Download
                                                </a>
                                            @endif
                                        </div>
                                    @elseif($type === 'github')
                                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <i data-lucide="github" class="w-5 h-5 text-slate-900"></i>
                                                <span class="text-sm font-bold text-slate-800 truncate max-w-[400px]">{{ $ans->github_link ?? 'No link provided' }}</span>
                                            </div>
                                            @if($ans->github_link)
                                                <a href="{{ $ans->github_link }}" target="_blank" 
                                                   class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition-all flex items-center gap-2">
                                                    Visit Repository <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 text-slate-700 text-sm leading-relaxed whitespace-pre-wrap">
                                            {{ $ans->answer_text ?? 'No response provided' }}
                                        </div>
                                    @endif
                                </div>

                                <!-- AI Insight Section -->
                                @if($ans->ai_feedback ?? false)
                                    <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50 flex gap-4">
                                        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center shrink-0">
                                            <i data-lucide="bot" class="w-5 h-5 text-indigo-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">AI Evaluator Insight</p>
                                            <p class="text-sm text-indigo-900/80 leading-relaxed italic">{{ $ans->ai_feedback }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </main>

            <!-- RIGHT SIDEBAR: REVIEW PANEL -->
            <aside class="w-[380px] bg-white border-l border-slate-200 flex flex-col shrink-0 z-40 shadow-[-4px_0_10px_rgba(0,0,0,0.02)]">
                <div class="p-8 flex-1 overflow-y-auto">
                    <h3 class="text-xl font-black text-slate-900 mb-6">Final Evaluation</h3>
                    
                    <form action="{{ route('user.mentor.submissions.review', $submission) }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <!-- SCORE RANGE -->
                        <div class="space-y-4">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">Performance Score (%)</label>
                            <div class="flex items-center gap-4">
                                <input type="range" name="score_range" id="score_range" min="0" max="100" 
                                       value="{{ $submission->final_percentage ?? $submission->percentage }}" 
                                       class="flex-1 accent-indigo-600 h-1.5 bg-slate-100 rounded-lg cursor-pointer appearance-none">
                                <input type="number" name="score" id="score_value" min="0" max="100" 
                                       value="{{ $submission->final_percentage ?? $submission->percentage }}" 
                                       class="w-20 p-2 text-center font-black text-lg text-indigo-600 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        </div>

                        <!-- FEEDBACK AREA -->
                        <div class="space-y-4">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">Mentor Feedback</label>
                            <textarea name="feedback" id="feedback" rows="8" 
                                      placeholder="Provide detailed feedback on the intern's work..."
                                      class="w-full p-4 border border-slate-200 rounded-2xl text-sm leading-relaxed focus:ring-2 focus:ring-indigo-500 transition-all outline-none bg-slate-50/50">{{ $submission->final_feedback }}</textarea>
                        </div>

                        <!-- ACTION BUTTONS -->
                        <div class="space-y-3 pt-4">
                            <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all flex items-center justify-center gap-2 active:scale-95">
                                <i data-lucide="save" class="w-5 h-5"></i> Approve Review
                            </button>
                            
                            <button type="button" onclick="confirmEvaluateNow()" 
                                    class="w-full bg-white border border-slate-200 text-slate-600 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Re-Run AI Check
                            </button>
                        </div>
                    </form>

                    <!-- SYSTEM INFO -->
                    <div class="mt-12 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">
                            <i data-lucide="info" class="w-3.5 h-3.5"></i> Evaluation Policy
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed italic">Mentor review overrides AI evaluation. Once processed, the intern will receive notification and access to their result and feedback.</p>
                    </div>
                </div>
            </aside>

        </div>

    </div>

    <!-- HIDDEN AI TRIGGER FORM -->
    <form id="ai-re-evaluate-form" action="{{ route('user.mentor.submissions.aiEvaluate', $submission) }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script>
        // Sync Range and Number Input
        const range = document.getElementById('score_range');
        const num = document.getElementById('score_value');
        
        range.addEventListener('input', (e) => num.value = e.target.value);
        num.addEventListener('input', (e) => range.value = e.target.value);

        function confirmEvaluateNow() {
            if(confirm('Are you sure you want to re-run AI evaluation? This will use extra credits and might change the initial AI score.')) {
                document.getElementById('ai-re-evaluate-form').submit();
            }
        }

        // Initialize Lucide Icons
        if (window.lucide) {
            window.lucide.createIcons();
        }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
</x-app-layout>