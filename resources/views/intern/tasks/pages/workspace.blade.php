<x-app-layout>
@php
    $taskId = $task->id;
    $questions = $task->questions ?? collect();
    $taskType = $task->type?->slug ?? null;
    $submitUrl = route('user.intern.tasks.submit', $taskId);
    $resultsUrl = route('user.intern.tasks.results', $taskId);
@endphp

<div class="max-w-6xl mx-auto px-4 py-6">

    {{-- Header --}}
    @include('intern.tasks.partials.header', ['task' => $task])

    @if($questions->count() === 0)
        <div class="text-center py-16 text-slate-500">
            No questions available.
        </div>
    @else

    <div class="grid lg:grid-cols-[240px_minmax(0,1fr)] gap-6 mt-6">

        {{-- Sidebar (Simplified) --}}
        <aside class="sticky top-6 h-fit">
            <div class="bg-white border rounded-xl p-4 space-y-2">
                @foreach($questions as $i => $q)
                    <button
                        class="ws-nav-item w-full text-left px-3 py-2 rounded-lg hover:bg-slate-100 text-sm"
                        data-qid="{{ $q->id }}"
                    >
                        Q{{ $i + 1 }}
                    </button>
                @endforeach
            </div>
        </aside>

        {{-- Main Content --}}
        <main>

            {{-- Question + Answer --}}
            <div class="bg-white border rounded-xl p-6">

                @foreach($questions as $i => $q)
                    @php
                        $qid = $q->id;
                        $type = $q->type ?? $taskType;
                    @endphp

                    <section 
                        class="ws-question-content space-y-6"
                        data-qid="{{ $qid }}"
                        style="display: {{ $i === 0 ? 'block' : 'none' }}"
                    >

                        {{-- Question --}}
                        <div>
                            <div class="text-xs text-slate-400 font-bold uppercase">
                                Question {{ $i + 1 }} / {{ $questions->count() }}
                            </div>

                            <h2 class="text-xl font-semibold text-slate-900 mt-1">
                                {{ $q->question }}
                            </h2>
                        </div>

                        {{-- Extra Info (Coding / File / GitHub) --}}
                        @if($type === 'coding')
                            <div class="space-y-3 text-sm text-slate-600">
                                <div>
                                    <strong>Description:</strong><br>
                                    {{ $q->description ?? '—' }}
                                </div>
                                <div>
                                    <strong>Input:</strong><br>
                                    {{ $q->input_format ?? '—' }}
                                </div>
                                <div>
                                    <strong>Output:</strong><br>
                                    {{ $q->output_format ?? '—' }}
                                </div>
                            </div>
                        @endif

                        {{-- Answer --}}
                        <div>
                            @if($type === 'mcq')
                                @include('intern.tasks.components.mcq', ['question' => $q])
                            @elseif($type === 'true_false')
                                @include('intern.tasks.components.true_false', ['question' => $q])
                            @elseif($type === 'blank')
                                @include('intern.tasks.components.blank', ['question' => $q])
                            @elseif($type === 'descriptive')
                                @include('intern.tasks.components.descriptive', ['question' => $q])
                            @elseif($type === 'coding')
                                @include('intern.tasks.components.coding', ['question' => $q])
                            @elseif($type === 'file')
                                @include('intern.tasks.components.file', ['question' => $q])
                            @elseif($type === 'github')
                                @include('intern.tasks.components.github', ['question' => $q])
                            @endif
                        </div>

                    </section>
                @endforeach

            </div>

            {{-- Bottom Actions --}}
            <div class="mt-6 flex justify-between items-center">
                <button id="wsOpenReviewBottom"
                    class="px-4 py-2 rounded-lg bg-slate-100 text-sm font-medium">
                    Review
                </button>

                <button id="wsOpenSubmitBottom"
                    class="px-5 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium">
                    Submit
                </button>
            </div>

        </main>
    </div>

    {{-- Modals --}}
    @include('intern.tasks.modals.review')
    @include('intern.tasks.modals.submit-confirm')

    @endif
</div>

{{-- JS CONFIG (UNCHANGED) --}}
@if($questions->count() > 0)
<script>
window.INTERN_TASK_WORKSPACE = {
    version: 1,
    taskId: @json($taskId),
    taskTitle: @json($task->title),
    taskType: @json($taskType),
    submitUrl: @json($submitUrl),
    resultsUrl: @json($resultsUrl),
    questions: @json($questions->values()),
};
</script>

<script src="/js/autosave.js"></script>
<script src="/js/navigation.js"></script>
<script src="/js/submit.js"></script>
<script src="/js/workspace.js"></script>
@endif

</x-app-layout>