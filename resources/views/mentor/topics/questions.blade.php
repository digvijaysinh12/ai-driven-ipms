@extends('layouts.app')
@section('title', ucfirst(str_replace('_', ' ', $type)) . ' Questions')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">{{ $topic->title }}</div>
        <h1 class="page-shell-title" style="text-transform: capitalize;">{{ str_replace('_', ' ', $type) }} Questions</h1>
        <p class="page-shell-subtitle">Review and manage the specific questions generated for this module.</p>
    </div>
    <div class="page-shell-actions">
        <x-ui.button :href="route('user.mentor.tasks.show', $topic->id)" variant="secondary">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Topic
        </x-ui.button>
    </div>
</div>

@if($questions->isEmpty())
    <div class="card p-20 text-center border-dashed bg-slate-50/50">
        <div class="w-16 h-16 bg-slate-100 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="help-circle" class="w-8 h-8"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest">No questions found</h3>
        <p class="text-xs text-slate-400 mt-2">AI has not generated questions for this type yet.</p>
    </div>
@else
    <div class="mt-8 space-y-6">
        @foreach($questions as $i => $q)
            <div id="question-card-{{ $q->id }}" class="card p-0 overflow-hidden bg-white border border-slate-200 hover:shadow-xl transition-all duration-300 group">
                <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded bg-slate-900 text-white text-xs font-bold flex items-center justify-center">Q{{ $i + 1 }}</span>
                        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">{{ str_replace('_', ' ', $type) }}</span>
                    </div>
                </div>

                <div class="p-8">
                    <div class="text-base font-bold text-slate-900 leading-relaxed mb-6 whitespace-pre-line">
                        {{ $q->problem_statement }}
                    </div>

                    @if($q->code)
                        <div class="bg-slate-950 rounded-lg p-6 mb-6 font-mono text-xs text-zinc-300 border border-slate-800 overflow-x-auto">
                            <div class="text-slate-500 mb-2">// Code Snippet</div>
                            <pre class="leading-relaxed">{{ $q->code }}</pre>
                        </div>
                    @endif

                    @if($type === 'mcq')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                            @foreach(['A', 'B', 'C', 'D'] as $key)
                                @php 
                                    $optProp = 'option_' . strtolower($key);
                                    $val = $q->$optProp;
                                    $isCorrect = $q->correct_answer === $key;
                                @endphp
                                @if($val)
                                    <div class="flex items-start gap-4 p-4 rounded-xl border {{ $isCorrect ? 'bg-emerald-50/50 border-emerald-200 ring-2 ring-emerald-500/10' : 'bg-slate-50/50 border-slate-100 opacity-80' }} transition-all">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black {{ $isCorrect ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500' }}">
                                            {{ $key }}
                                        </div>
                                        <div class="text-sm {{ $isCorrect ? 'text-emerald-900 font-bold' : 'text-slate-600' }}">{{ $val }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if(in_array($type, ['true_false', 'blank', 'output']))
                        <div class="mt-6 flex items-center gap-3">
                            <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Expected Result</span>
                            <div class="px-4 py-2 bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-100 text-sm font-bold flex items-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                {{ $q->correct_answer }}
                            </div>
                        </div>
                    @endif

                    @if($type === 'coding' && $q->referenceSolution?->solution_code)
                        <div class="mt-8 border-t border-slate-100 pt-8">
                            <div class="flex items-center gap-2 mb-4">
                                <i data-lucide="code-2" class="w-4 h-4 text-blue-500"></i>
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Reference Solution</span>
                            </div>
                            <div class="bg-zinc-900 rounded-xl p-6 font-mono text-xs text-blue-400 border border-zinc-800 overflow-x-auto shadow-inner">
                                <pre class="leading-loose">{{ $q->referenceSolution->solution_code }}</pre>
                            </div>
                            @if($q->referenceSolution->explanation)
                                <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
                                    <div class="text-[9px] font-black uppercase text-blue-500 mb-1">Logic Explanation</div>
                                    <p class="text-xs text-blue-900 leading-relaxed">{{ $q->referenceSolution->explanation }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

<script>
    lucide.createIcons();
</script>
@endsection
