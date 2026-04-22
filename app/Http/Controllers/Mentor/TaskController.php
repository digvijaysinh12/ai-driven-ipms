<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService) {}

    public function index(Request $request)
    {
        $tasks = $this->taskService->listForMentor($request->user(), $request);
        $stats = $this->taskService->getTaskStats($request->user());

        // AJAX response
        if ($request->ajax()) {
            return view('mentor.tasks.partials.table', compact('tasks'))->render();
        }

        return view('mentor.tasks.index', compact('tasks', 'stats'));
    }

    public function create(Request $request): View
    {
        $interns = $this->taskService->availableInternsForMentor($request->user());
        $taskTypes = TaskType::listForDropdown();

        return view('mentor.tasks.create', compact('interns', 'taskTypes'));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $task = $this->taskService->createTask($request->user(), $validated);

        $internIds = array_values(array_unique(array_filter(Arr::wrap($validated['intern_ids'] ?? $validated['assigned_to'] ?? null))));

        if ($internIds !== []) {
            $this->taskService->assignTaskToInterns(
                $task,
                $request->user(),
                $internIds
            );
        }

        if ($request->boolean('use_ai')) {
            $this->taskService->generateQuestionsNow(
                $task,
                (int) $request->integer('question_count', 5)
            );
        }

        return redirect()
            ->route('user.mentor.tasks.show', $task)
            ->with('success', $request->boolean('use_ai')
                ? 'Task created successfully. AI question generation has been queued.'
                : 'Task created successfully.');
    }

    public function show(Request $request, Task $task)
    {
        if ($request->user()->role->name !== 'mentor') {
        abort(403, 'Unauthorized');
    }

        $task->load([
            'type:id,name,slug',
            'users:id,name,email',
        ]);

        $questions = $task->questions()->latest()->get();
        $submissions = $task->submissions()->with(['intern:id,name,email', 'status'])->latest()->paginate(5);
        $interns = User::whereIn('id', function ($query) {
            $query->select('intern_id')
                ->from('mentor_assignments')
                ->where('mentor_id', auth()->id())
                ->where('is_active', true);
        })->get();

        // ADD THIS
        $stats = $this->taskService->getTaskStats($request->user());

        if ($request->ajax()) {
            return view(
                'mentor.tasks.partials.show-content',
                compact('task', 'questions', 'submissions')
            )->render();
        }

        return view(
            'mentor.tasks.show',
            compact('task', 'questions', 'submissions', 'stats','interns')
        );
    }

    public function generateQuestions(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $count = (int) ($request->input('count', 5));

        try {
            $this->taskService->generateQuestionsNow($task, $count);

            return response()->json([
                'status' => true,
                'message' => 'AI question generation completed.',
            ]);

        } catch (\Throwable $e) {

            \Log::channel('ai')->error('Controller Error', [
                'task_id' => $task->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function markReady(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        if (! in_array($task->type->slug, ['file', 'github'])) {
            if ($task->questions()->count() === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please add questions first',
                ], 422);
            }
        }

        $task->update([
            'status' => Task::STATUS_READY,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Task marked as ready',
            'new_status' => 'ready',
        ]);
    }

public function bulkUpdateQuestions(Request $request, Task $task)
{
    abort_if(auth()->user()?->role?->name !== 'mentor', 403, 'Unauthorized');

    $data = $request->validate([
        'questions' => 'required|array',

        'questions.*.id' => [
            'required',
            Rule::exists('task_questions', 'id')->where('task_id', $task->id),
        ],

        'questions.*.question' => 'required|string',

        // 🔥 ADD THESE
        'questions.*.options' => 'nullable|array',
        'questions.*.correct_answer' => 'nullable|string',

        'questions.*.description' => 'nullable|string',
        'questions.*.input_format' => 'nullable|string',
        'questions.*.output_format' => 'nullable|string',
        'questions.*.constraints' => 'nullable|string',

        'questions.*.test_cases' => 'nullable|array',
    ]);

    foreach ($data['questions'] as $qData) {

        $task->questions()
            ->whereKey($qData['id'])
            ->update([

                // COMMON
                'question' => $qData['question'],

                // MCQ / TF / BLANK
                'options' => $qData['options'] ?? null,
                'correct_answer' => $qData['correct_answer'] ?? null,

                // CODING
                'description' => $qData['description'] ?? null,
                'input_format' => $qData['input_format'] ?? null,
                'output_format' => $qData['output_format'] ?? null,
                'constraints' => $qData['constraints'] ?? null,

                // JSON FIELD
                'test_cases' => isset($qData['test_cases'])
                    ? json_encode($qData['test_cases'])
                    : null,
            ]);
    }

    return response()->json([
        'message' => 'All questions updated successfully',
    ]);
}

public function assign(AssignTaskRequest $request, Task $task)
{
    abort_if(auth()->user()?->role?->name !== 'mentor', 403, 'Unauthorized');

    $validated = $request->validated();

    $this->taskService->assignTaskToInterns(
        $task,
        $request->user(),
        $validated['intern_ids'],
        $validated['due_at'] ?? null
    );

    return response()->json([
        'message' => 'Task assigned successfully'
    ]);
}
}
