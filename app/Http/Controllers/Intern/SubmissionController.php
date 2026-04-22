<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Services\SubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    public function __construct(private readonly SubmissionService $submissionService)
    {
    }

    /**
     * Handle the submission of a task.
     * Supports both JSON (API) and Web Form redirects.
     */
    /**
     * Auto-save a single answer
     */
    public function saveAnswer(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'question_id' => 'nullable|exists:task_questions,id',
            'answer' => 'nullable|string',
            'github_link' => 'nullable|url',
            'file' => 'nullable|file|max:5120', // 5MB limit
        ]);

        try {
            $user = Auth::user();
            $submission = $this->submissionService->getOrCreateSubmission($task, $user);

            $data = $request->only(['answer', 'github_link', 'execution_output', 'error_message']);
            
            // Handle file upload if present
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $data['file_path'] = $file->store('submissions/' . $submission->id, 'public');
            }

            $this->submissionService->saveAnswer($submission, $request->question_id, $data);

            return response()->json([
                'status' => 'success',
                'message' => 'Answer saved'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Handle the final submission of a task.
     */
    public function submit(Request $request, Task $task): JsonResponse
    {
        try {
            $user = Auth::user();
            $submission = $this->submissionService->getOrCreateSubmission($task, $user);

            $this->submissionService->submit($submission);

            return response()->json([
                'status' => 'success',
                'message' => 'Task submitted successfully.',
                'data' => $submission->load('status')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }



public function showResults(Task $task)
{
    $user = Auth::user();

    $submission = TaskSubmission::with([
        'task.type',
        'task.questions',
        'answers',
        'status'
    ])
    ->where('task_id', $task->id)
    ->where('user_id', $user->id)
    ->latest()
    ->firstOrFail();

    return view('intern.tasks.results', compact('submission'));
}
}
