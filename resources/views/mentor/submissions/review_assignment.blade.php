@extends('layouts.app')
@section('title', 'Holistic Review')

@section('content')
<div class="page-shell-header">
    <div class="page-shell-copy">
        <div class="page-shell-eyebrow">Intern Topic Assignment</div>
        <h1 class="page-shell-title">Holistic Evaluation</h1>
        <p class="page-shell-subtitle">Review the AI's assessment for {{ $assignment->intern->name }} on "{{ $assignment->topic->title }}". You can override the automated scoring and feedback here.</p>
    </div>
    <div class="page-shell-actions">
        <x-ui.button :href="route('user.mentor.submissions.index')" variant="secondary">Back to List</x-ui.button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
    <div class="lg:col-span-2 space-y-8">
        {{-- Submission Summary --}}
        <div class="card p-0 overflow-hidden bg-white border border-slate-200">
            <div class="p-6 border-bottom border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Question Breakdown</h3>
                <span class="text-xs font-medium text-slate-500">{{ $assignment->topic->questions->count() }} Questions Reviewed by AI</span>
            </div>
            
            <div class="divide-y divide-slate-100">
                @foreach($assignment->topic->questions as $index => $question)
                    @php 
                        $submission = $question->submissions->first(); 
                    @endphp
                    <div class="p-6 hover:bg-slate-50 transition-colors">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-6 h-6 rounded-md bg-slate-900 text-white text-[10px] font-bold flex items-center justify-center">{{ $index + 1 }}</span>
                                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">{{ str_replace('_', ' ', $question->type) }}</span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 leading-snug">{{ $question->problem_statement }}</h4>
                            </div>
                            <div class="text-right">
                                @if($submission)
                                    <span class="text-[10px] font-bold uppercase py-1 px-2 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">Answered</span>
                                @else
                                    <span class="text-[10px] font-bold uppercase py-1 px-2 rounded-full bg-rose-50 text-rose-600 border border-rose-100">Unanswered</span>
                                @endif
                            </div>
                        </div>

                        @if($submission)
                            <div class="bg-slate-950 rounded-lg p-5 text-zinc-300 font-mono text-xs overflow-x-auto mb-4 border border-slate-800">
                                @if($question->type === 'mcq')
                                    <div class="text-emerald-400 font-bold mb-1">// Selected Option</div>
                                    <div class="text-white text-base">{{ $submission->submitted_code }}</div>
                                @elseif($question->type === 'description')
                                    <div class="text-blue-400 font-bold mb-1">// GitHub Repository</div>
                                    <a href="{{ $submission->github_link }}" target="_blank" class="text-white underline hover:text-blue-300 text-sm">{{ $submission->github_link ?? 'N/A' }}</a>
                                    @if($submission->file_path)
                                        <div class="mt-3">
                                            <a href="{{ Storage::url($submission->file_path) }}" target="_blank" class="px-3 py-1.5 bg-zinc-800 text-zinc-300 rounded-md hover:bg-zinc-700 transition-colors inline-flex items-center gap-2">
                                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download Attachment
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-slate-500 mb-2">// User Implementation</div>
                                    <pre class="leading-relaxed">{{ $submission->submitted_code }}</pre>
                                @endif
                            </div>
                        @else
                            <div class="py-10 text-center border-2 border-dashed border-slate-100 rounded-lg">
                                <p class="text-xs text-slate-400 italic">This section was skipped by the intern.</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <aside class="space-y-8">
        {{-- Override Form --}}
        <div class="card p-8 bg-zinc-900 border-none shadow-2xl relative overflow-hidden text-white">
            <div class="absolute -right-4 -top-4 opacity-10">
                <i data-lucide="award" class="w-32 h-32"></i>
            </div>

            <form action="{{ route('user.mentor.submissions.review', $assignment->id) }}" method="POST" class="relative z-10">
                @csrf
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="px-2 py-0.5 bg-blue-500 text-[10px] font-bold uppercase rounded text-white">AI Evaluation Summary</span>
                        <span class="text-xs text-zinc-500">Generated Holistic Assessment</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-zinc-500 mb-2">Final Score</label>
                            <input type="number" name="score" value="{{ $assignment->score }}" class="w-full bg-zinc-800 border-zinc-700 rounded-lg text-white font-bold p-3 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-zinc-500 mb-2">Final Grade</label>
                            <input type="text" name="grade" value="{{ $assignment->grade }}" class="w-full bg-zinc-800 border-zinc-700 rounded-lg text-white font-bold p-3 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-[10px] font-bold uppercase text-zinc-500 mb-2">Evaluation Tone</label>
                        <select name="tone" class="w-full bg-zinc-800 border-zinc-700 rounded-lg text-white font-bold p-3 focus:ring-blue-500 outline-none">
                            <option value="constructive" {{ $assignment->tone === 'constructive' ? 'selected' : '' }}>Constructive</option>
                            <option value="encouraging" {{ $assignment->tone === 'encouraging' ? 'selected' : '' }}>Encouraging</option>
                            <option value="formal" {{ $assignment->tone === 'formal' ? 'selected' : '' }}>Formal</option>
                            <option value="critical" {{ $assignment->tone === 'critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                    </div>

                    <div class="mb-8">
                        <label class="block text-[10px] font-bold uppercase text-zinc-500 mb-2">Feedback Summary</label>
                        <textarea name="feedback" rows="8" class="w-full bg-zinc-800 border-zinc-700 rounded-lg text-zinc-300 text-sm p-4 focus:ring-blue-500 outline-none leading-relaxed">{{ $assignment->feedback }}</textarea>
                    </div>

                    <x-ui.button type="submit" variant="primary" class="w-full py-4 font-black shadow-lg shadow-blue-900">
                        Finalize & Save Review
                    </x-ui.button>
                </div>
            </form>
        </div>

        {{-- Insights Box --}}
        <div class="card p-6 bg-slate-50 border-slate-200">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-widest mb-4">Mentor's Note</h4>
            <div class="text-xs text-slate-500 space-y-3 leading-relaxed">
                <p>The AI evaluated this entire task based on code structure, adherence to requirements, and task performance.</p>
                <p>You are reviewing the **Holistic Synthesis**. Any changes you make here will be immediately reflected on the intern's dashboard.</p>
            </div>
        </div>
    </aside>
</div>

<script>
    lucide.createIcons();
</script>
@endsection
