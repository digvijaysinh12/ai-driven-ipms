<x-app-layout>

<x-slot name="header">
    <div>
        <h2 class="text-xl font-semibold text-slate-900">Create Task</h2>
        <p class="text-sm text-slate-500">Create task with structured inputs</p>
    </div>
</x-slot>

<div class="max-w-3xl mx-auto">

{{-- ✅ ERROR DISPLAY --}}
@if ($errors->any())
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <ul>
            @foreach ($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('user.mentor.tasks.store') }}" class="bg-white p-6 rounded-xl shadow space-y-6">
@csrf

<!-- TITLE -->
<div>
    <label class="block mb-2 text-sm font-medium text-slate-700">Title</label>
    <input type="text" name="title"
           class="w-full border border-slate-300 p-3 rounded-lg text-sm"
           placeholder="Enter task title" value="{{ old('title') }}">
</div>

<!-- DESCRIPTION -->
<div>
    <label class="block mb-2 text-sm font-medium text-slate-700">Description</label>
    <textarea name="description"
              class="w-full border border-slate-300 p-3 rounded-lg text-sm"
              rows="4"
              placeholder="Enter task description">{{ old('description') }}</textarea>
</div>

<!-- TASK TYPE -->
<div>
    <h3 class="text-sm font-medium text-slate-700 mb-3">Task Type</h3>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">

        @foreach($taskTypes as $type)
            <label class="task-card border border-slate-300 p-4 rounded-lg cursor-pointer transition"
                   data-slug="{{ $type->slug }}">

                <input type="radio" name="task_type_id"
                       value="{{ $type->id }}"
                       class="hidden"
                       {{ old('task_type_id') == $type->id ? 'checked' : '' }}>

                <div class="text-sm font-medium text-slate-800">
                    {{ $type->name }}
                </div>

                <p class="text-xs text-slate-400 mt-1">
                    {{ $type->description }}
                </p>
            </label>
        @endforeach

    </div>
</div>

<!-- DIFFICULTY -->
<div>
    <label class="block mb-2 text-sm font-medium text-slate-700">Difficulty</label>
    <select name="difficulty" class="w-full border border-slate-300 p-3 rounded-lg text-sm">
        <option value="easy">Easy</option>
        <option value="medium">Medium</option>
        <option value="hard">Hard</option>
    </select>
</div>

<!-- DYNAMIC -->
<div id="dynamicFields" class="hidden"></div>

<!-- AI -->
<div class="bg-slate-50 border rounded-lg p-4">
    <div class="flex justify-between items-center">
        <span class="text-sm text-slate-700">AI Question Generation</span>
        <input type="checkbox" id="useAI" name="use_ai" value="1">
    </div>

    <div id="aiBox" class="hidden mt-3">
        <input type="number" name="question_count" id="questionCount"
               min="1" max="40"
               class="w-full border border-slate-300 p-3 rounded-lg text-sm"
               placeholder="Number of Questions (1–40)">
    </div>
</div>

<!-- BUTTON -->
<button type="submit"
        class="w-full bg-slate-900 hover:bg-slate-800 text-white py-3 rounded-lg text-sm font-medium">
    Create Task
</button>

</form>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

let selectedType = null;

// SELECT TASK TYPE
$('.task-card').click(function(){

    $('.task-card').removeClass('border-slate-900 bg-slate-50');
    $(this).addClass('border-slate-900 bg-slate-50');

    selectedType = $(this).data('slug');

    $('input[name="task_type_id"]').prop('checked', false);
    $(this).find('input[name="task_type_id"]').prop('checked', true);

    loadDynamicFields(selectedType);
});


// DYNAMIC FIELDS
function loadDynamicFields(type){

    let html = '';

    if(type === 'file'){
        html = `
            <label class="block mb-2 text-sm">Allowed File Type</label>
            <input type="text" name="file_type" class="w-full border p-3 rounded-lg" placeholder="pdf, zip">
        `;
    }

    if(type === 'github'){
        html = `<p class="text-sm text-gray-500">Intern will submit GitHub repository link</p>`;
    }

    if(type === 'mcq' || type === 'true_false' || type === 'blank'){
        html = `
            <label class="block mb-2 text-sm">Question Count</label>
            <input type="number" name="dynamic_question_count" min="1" max="40" class="w-full border p-3 rounded-lg">
        `;
    }

    if(html){
        $('#dynamicFields').html(html).removeClass('hidden');
    } else {
        $('#dynamicFields').addClass('hidden');
    }
}


// AI TOGGLE
$('#useAI').change(function(){
    $('#aiBox').slideToggle();
});

</script>

</x-app-layout>