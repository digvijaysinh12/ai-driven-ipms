<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->assignedTasks()
            ->with([
                'type:id,name,slug',
                'submissions' => function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                    ->with('status')
                    ->latest();
                }
            ])
            ->withCount('questions')
            ->latest('task_user.assigned_at');

        // 🔥 FILTER: Pending
        if ($request->status === 'pending') {
            $query->whereDoesntHave('submissions', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                ->whereHas('status', function ($s) {
                    $s->whereIn('slug', ['completed', 'ai_evaluated']);
                });
            });
        }

        // 🔥 FILTER: Completed
        if ($request->status === 'completed') {
            $query->whereHas('submissions', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                ->whereHas('status', function ($s) {
                    $s->whereIn('slug', ['completed', 'ai_evaluated']);
                });
            });
        }

        // 🔥 SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->get();

        // 🔥 AJAX RESPONSE
        if ($request->ajax()) {
            return view('intern.tasks.partials.list', compact('tasks'))->render();
        }

        return view('intern.tasks.index', compact('tasks'));
    }

    public function show(Task $task): View
    {
        // Check if task is assigned to this user
        if (!$task->users()->where('user_id', Auth::id())->exists()) {
            abort(403, 'This task is not assigned to you.');
        }

        $task->load(['type:id,name,slug', 'questions:id,task_id,question,options,correct_answer,source']);
        
        // Load submission status if exists
        $submission = $task->submissions()
            ->where('user_id', Auth::id())
            ->with(['status'])
            ->latest()
            ->get()
            ->first(fn($s) => $s->isActive()) 
            ?? $task->submissions()->where('user_id', Auth::id())->latest()->first();

        return view('intern.tasks.show', compact('task', 'submission'));
    }

    public function __construct(protected \App\Services\SubmissionService $submissionService)
    {
    }

    /**
     * Start/Execute task workspace
     */
    public function execute(Task $task)
    {
        $user = auth()->user();

        // 🔒 Prevent accessing task if not assigned
        if (!$user->assignedTasks()->where('tasks.id', $task->id)->exists()) {
            abort(403, 'Unauthorized access to this task');
        }

        // 🛡️ Get or Create Submission (Task workflow pattern)
        $submission = $this->submissionService->getOrCreateSubmission($task, $user);

        // ✅ Load all required data
        $task->load([
            'type:id,name,slug',
            'questions' => function ($q) {
                $q->orderBy('id');
            }
        ]);

        // 📦 Prepare Answers for State
        $answers = $submission->answers->pluck('answer_text', 'task_question_id');

        return view('intern.tasks.workspace', compact('task', 'submission', 'answers'));
    }
}
