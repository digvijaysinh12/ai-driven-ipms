<?php

namespace App\Services;

use App\Models\MentorAssignment;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use App\Services\AI\AIService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function listForMentor(User $mentor, Request $request)
    {
        $query = Task::query()
            ->where('created_by', $mentor->id)
            ->with(['type:id,name,slug'])
            ->withCount(['questions', 'submissions']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->latest()->paginate(10)->withQueryString();
    }
    
    public function getTaskStats(User $mentor): array
        {
            return [
                'total' => Task::where('created_by', $mentor->id)->count(),
                'draft' => Task::where('created_by', $mentor->id)->where('status', 'draft')->count(),
                'ready' => Task::where('created_by', $mentor->id)->where('status', 'ready')->count(),
                'assigned' => Task::where('created_by', $mentor->id)->where('status', 'assigned')->count(),
            ];
        }

    public function availableInternsForMentor(User $mentor): Collection
    {
        return User::query()
            ->whereHas('role', fn ($query) => $query->where('name', 'intern'))
            ->whereHas('currentMentorAssignment', fn ($query) => $query->where('mentor_id', $mentor->id))
            ->where('status', 'approved')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function createTask(User $mentor, array $attributes): Task
    {
        return DB::transaction(function () use ($mentor, $attributes): Task {
            return Task::query()->create([
                'title' => $attributes['title'],
                'description' => $attributes['description'],
                'task_type_id' => $attributes['task_type_id'],
                'created_by' => $mentor->id,
                'difficulty' => $attributes['difficulty'] ?? 'easy',
                'language' => $attributes['language'] ?? null,
                'status' => Task::STATUS_DRAFT,
            ]);
        });
    }

    public function updateTask(Task $task, array $attributes): Task
    {
        $task->update([
            'title' => $attributes['title'] ?? $task->title,
            'description' => $attributes['description'] ?? $task->description,
            'task_type_id' => $attributes['task_type_id'] ?? $task->task_type_id,
            'difficulty' => $attributes['difficulty'] ?? $task->difficulty,
        ]);

        return $task->refresh();
    }

    public function assignTaskToInterns(Task $task, User $mentor, array $internIds, ?string $dueAt = null): void
    {
        foreach ($internIds as $internId) {
            $this->assertMentorOwnsIntern($mentor, (int) $internId);
        }

        $syncData = [];
        foreach ($internIds as $id) {
            $syncData[$id] = [
                'assigned_at' => now(),
                'due_at' => $dueAt,
            ];
        }

        $task->users()->syncWithoutDetaching($syncData);
    }

public function generateQuestionsNow(Task $task, int $count = 5): void
{
    $taskTypeSlug = $task->type?->slug;

    $supported = ['mcq', 'descriptive', 'true_false', 'blank', 'coding'];

    if (!in_array($taskTypeSlug, $supported, true)) {
        throw ValidationException::withMessages([
            'task' => "AI not supported for {$taskTypeSlug}"
        ]);
    }

    \Log::channel('ai')->info('TaskService Start', [
        'task_id' => $task->id,
        'type' => $taskTypeSlug
    ]);

    $aiService = app(AIService::class);

    $questions = $aiService->generateQuestions($task, $count);

    if (empty($questions)) {
        throw new \Exception('AI returned no questions.');
    }

    $this->storeGeneratedQuestions($task, $questions);

    $task->update([
        'status' => Task::STATUS_READY
    ]);

    \Log::channel('ai')->info('Task marked ready');
}

public function storeGeneratedQuestions(Task $task, array $questions): void
{
    $normalizedQuestions = collect($questions)
        ->map(function (array $question) {

            $text = trim(
                $question['question']
                ?? $question['question_text']
                ?? $question['description']
                ?? ''
            );

            return [
                'question' => $text,

                // objective
                'options' => $question['options'] ?? null,
                'correct_answer' => $question['correct_answer'] ?? null,

                // coding / descriptive
                'description' => $question['description'] ?? null,
                'input_format' => $question['input_format'] ?? null,
                'output_format' => $question['output_format'] ?? null,
                'constraints' => $question['constraints'] ?? null,
                'test_cases' => $question['test_cases'] ?? null,

                'source' => 'ai',
            ];
        })
        ->filter(fn ($q) => !empty($q['question']))
        ->values()
        ->all();

    if (empty($normalizedQuestions)) {
        throw new \Exception('No valid questions to store.');
    }

    DB::transaction(function () use ($task, $normalizedQuestions) {

        // delete old AI questions
        $task->questions()
            ->where('source', 'ai')
            ->delete();

        // insert new
        $task->questions()->createMany($normalizedQuestions);
    });

    \Log::channel('ai')->info('Questions stored', [
        'count' => count($normalizedQuestions)
    ]);
}
    private function assertMentorOwnsIntern(User $mentor, int $internId): void
    {
        $ownsIntern = MentorAssignment::query()
            ->where('mentor_id', $mentor->id)
            ->where('intern_id', $internId)
            ->where('is_active', true)
            ->exists();

        if (!$ownsIntern) {
            throw ValidationException::withMessages([
                'intern_ids' => "Intern ID {$internId} is not assigned to you.",
            ]);
        }
    }

    private function resolveTaskTypeSlug(Task $task): ?string
    {
        return TaskType::query()
            ->whereKey($task->task_type_id)
            ->value('slug');
    }
}
