<div class="space-y-8">

    <!-- SUB-HEADER FOR QUESTIONS -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-xl border border-slate-200 flex items-center justify-center text-slate-400">
                <i data-lucide="help-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest leading-none mb-1">Curated Questions</h3>
                <p class="text-[11px] font-bold text-slate-400 leading-none">Review and refine the challenge requirements.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" id="saveAllBtn" 
                    class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-black text-xs shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Save All Changes
            </button>
        </div>
    </div>

    <!-- QUESTIONS FORM -->
    <form id="bulkUpdateForm" class="space-y-6">
        @forelse($questions as $index => $q)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden group hover:border-slate-300 transition-all relative">
                
                <!-- QUESTION HEADER / TYPE TAG -->
                <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Question #{{ $index + 1 }}</span>
                        <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase tracking-widest border border-indigo-100">
                            {{ strtoupper($q->type) }}
                        </span>
                    </div>
                    
                    <button type="button" class="text-slate-400 hover:text-rose-500 transition-colors removeQuestion p-1">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <!-- HIDDEN ID -->
                    <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $q->id }}">

                    <!-- QUESTION CONTENT -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Question Text</label>
                        <textarea name="questions[{{ $index }}][question]" 
                                  rows="2" 
                                  class="w-full bg-slate-50 border border-slate-100 rounded-xl p-4 text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
                                  placeholder="Enter the main question text...">{{ $q->question }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 gap-6 pt-2">
                        
                        {{-- MCQ RENDERING --}}
                        @if($q->type === 'mcq')
                            <div class="space-y-4">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Options & Correct Answer</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @php $options = is_array($q->options) ? $q->options : json_decode($q->options ?? '[]', true); @endphp
                                    @foreach($options as $optIndex => $opt)
                                        <div class="relative group/opt">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-300">{{ chr(65 + $optIndex) }}</span>
                                            <input type="text" 
                                                   name="questions[{{ $index }}][options][]" 
                                                   value="{{ is_array($opt) ? json_encode($opt) : $opt }}" 
                                                   class="w-full bg-slate-50 border border-slate-100 pl-10 pr-4 py-3 rounded-xl text-sm font-semibold text-slate-600 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
                                                   placeholder="Option {{ $optIndex + 1 }}">
                                        </div>
                                    @endforeach
                                </div>
                                <div class="relative group/correct">
                                    <i data-lucide="check-circle" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-emerald-500"></i>
                                    <input type="text" 
                                           name="questions[{{ $index }}][correct_answer]" 
                                           value="{{ $q->correct_answer }}" 
                                           class="w-full bg-emerald-50/30 border border-emerald-100 pl-11 pr-4 py-3 rounded-xl text-sm font-bold text-emerald-700 placeholder:text-emerald-300 focus:bg-white focus:ring-2 focus:ring-emerald-500 transition-all outline-none"
                                           placeholder="Enter Correct Option Index (0, 1, 2...)">
                                </div>
                            </div>
                        @endif

                        {{-- TRUE/FALSE RENDERING --}}
                        @if($q->type === 'true_false')
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Correct Orientation</label>
                                <select name="questions[{{ $index }}][correct_answer]" 
                                        class="w-full bg-slate-50 border border-slate-100 px-4 py-3 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all outline-none appearance-none">
                                    <option value="True" @selected($q->correct_answer == 'True')>True</option>
                                    <option value="False" @selected($q->correct_answer == 'False')>False</option>
                                </select>
                            </div>
                        @endif

                        {{-- BLANK RENDERING --}}
                        @if($q->type === 'blank')
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Expected Phrase</label>
                                <div class="relative">
                                    <i data-lucide="edit-3" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300"></i>
                                    <input type="text" 
                                           name="questions[{{ $index }}][correct_answer]" 
                                           value="{{ $q->correct_answer }}" 
                                           class="w-full bg-slate-50 border border-slate-100 pl-11 pr-4 py-3 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
                                           placeholder="Enter the phrase that fills the blank...">
                                </div>
                            </div>
                        @endif

                        {{-- DESCRIPTIVE --}}
                        @if($q->type === 'descriptive')
                            <div class="p-6 rounded-2xl bg-amber-50 border border-amber-100 flex gap-4">
                                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center shrink-0">
                                    <i data-lucide="user-check" class="w-5 h-5 text-amber-600"></i>
                                </div>
                                <div>
                                    <p class="text-[11px] font-black text-amber-700 uppercase tracking-widest mb-1">Human Evaluation Required</p>
                                    <p class="text-[11px] text-amber-600 leading-relaxed italic">Descriptive answers will be evaluated based on the intern's input depth. AI will provide a preliminary score, but mentor review is finalized manually.</p>
                                </div>
                            </div>
                        @endif

                        {{-- CODING RENDERING --}}
                        @if($q->type === 'coding')
                            <div class="space-y-6">
                                <!-- Coding Detail Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Input Specification</label>
                                        <textarea name="questions[{{ $index }}][input_format]" rows="3" class="w-full bg-slate-50 border border-slate-100 rounded-xl p-4 text-xs font-mono text-slate-600 focus:bg-white outline-none">{{ $q->input_format }}</textarea>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Output Specification</label>
                                        <textarea name="questions[{{ $index }}][output_format]" rows="3" class="w-full bg-slate-50 border border-slate-100 rounded-xl p-4 text-xs font-mono text-slate-600 focus:bg-white outline-none">{{ $q->output_format }}</textarea>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Technical Constraints</label>
                                    <textarea name="questions[{{ $index }}][constraints]" rows="2" class="w-full bg-slate-50 border border-slate-100 rounded-xl p-4 text-xs font-mono text-slate-600 focus:bg-white outline-none" placeholder="Time: 2s, Memory: 256MB...">{{ $q->constraints }}</textarea>
                                </div>

                                <div class="space-y-4">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Test Scenarios (JSON)</label>
                                    @php $testCases = is_array($q->test_cases) ? $q->test_cases : json_decode($q->test_cases ?? '[]', true); @endphp
                                    <div class="grid gap-3">
                                        @foreach($testCases as $caseIndex => $case)
                                            <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div class="space-y-1">
                                                    <span class="text-[9px] font-black text-slate-300 uppercase">Input {{ $caseIndex + 1 }}</span>
                                                    <input type="text" name="questions[{{ $index }}][test_cases][{{ $caseIndex }}][input]" value="{{ is_array($case['input'] ?? null) ? json_encode($case['input']) : ($case['input'] ?? '') }}" class="w-full bg-white border border-slate-100 p-2 rounded-lg text-xs font-mono">
                                                </div>
                                                <div class="space-y-1">
                                                    <span class="text-[9px] font-black text-slate-300 uppercase">Expected Output</span>
                                                    <input type="text" name="questions[{{ $index }}][test_cases][{{ $caseIndex }}][output]" value="{{ is_array($case['output'] ?? null) ? json_encode($case['output']) : ($case['output'] ?? '') }}" class="w-full bg-white border border-slate-100 p-2 rounded-lg text-xs font-mono text-emerald-600">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        @empty
            <div class="py-20 text-center bg-white rounded-3xl border border-dashed border-slate-200">
                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="inbox" class="w-8 h-8 text-slate-200"></i>
                </div>
                <h4 class="text-slate-900 font-black tracking-tight text-xl">The workshop is empty</h4>
                <p class="text-slate-400 text-sm max-w-[300px] mx-auto mt-2 font-medium">Use the "Generate Questions" button to populate the task via AI or add them manually.</p>
            </div>
        @endforelse
    </form>

    <!-- SUBMISSIONS SECTION -->
    <div class="pt-12 space-y-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-xl border border-slate-200 flex items-center justify-center text-slate-400">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest leading-none mb-1">Recent Submissions</h3>
                    <p class="text-[11px] font-bold text-slate-400 leading-none">Track intern progress and review their results.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Intern</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Score</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Submitted</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest px-8">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($submissions as $sub)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center font-bold text-xs text-indigo-600">
                                        {{ substr($sub->intern->name, 0, 1) }}
                                    </div>
                                    <span class="font-bold text-slate-900">{{ $sub->intern->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($sub->result !== null)
                                    <div class="flex items-center gap-2">
                                        <div class="w-12 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $sub->result }}%"></div>
                                        </div>
                                        <span class="text-xs font-black text-slate-700">{{ $sub->result }}%</span>
                                    </div>
                                @else
                                    <span class="text-xs font-bold text-slate-300 italic uppercase">Not evaluated</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $slug = $sub->status?->slug;
                                    $badgeClass = match($slug) {
                                        'completed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'submitted', 'ai_evaluated' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        default => 'bg-slate-50 text-slate-500 border-slate-100'
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest border {{ $badgeClass }}">
                                    {{ $sub->status?->name ?? 'Pending' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold text-slate-500">{{ $sub->submitted_at?->diffForHumans() ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right px-8">
                                <a href="{{ route('user.mentor.submissions.show', $sub) }}" 
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-600 hover:bg-slate-100 transition-all active:scale-95">
                                    Review <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <p class="text-[10px] font-black text-slate-300 uppercase italic">No submissions documented for this task yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($submissions->hasPages())
                <div class="p-6 bg-slate-50 border-t border-slate-100">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
<script>
    if(window.lucide) { window.lucide.createIcons(); }
</script>