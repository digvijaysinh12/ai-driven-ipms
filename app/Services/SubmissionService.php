<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\SubmissionAnswer;
use App\Models\SubmissionStatus;
use App\Services\EvaluationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SubmissionService
{
    public function __construct(protected EvaluationService $evaluationService)
    {
    }

    /*
    |--------------------------------------------------------------------------
    | Get or Create In-Progress Submission
    |--------------------------------------------------------------------------
    */
    public function getOrCreateSubmission(Task $task, User $user): TaskSubmission
    {
        // 1. Find existing submission that is NOT completed
        $submission = TaskSubmission::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->whereHas('status', function ($q) {
                $q->where('slug', '!=', 'completed');
            })
            ->latest()
            ->first();

        if ($submission) {
            return $submission;
        }

        // 2. Else create new in_progress submission
        $statusId = SubmissionStatus::where('slug', 'in_progress')->value('id')
            ?? SubmissionStatus::where('slug', 'pending')->value('id');

        return TaskSubmission::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'status_id' => $statusId,
        ]);
    }

    /**
     * @deprecated Use getOrCreateSubmission
     */
    public function getOrCreateInProgressSubmission(Task $task, User $user): TaskSubmission
    {
        return $this->getOrCreateSubmission($task, $user);
    }

    /*
    |--------------------------------------------------------------------------
    | Save Individual Answer (Auto-save)
    |--------------------------------------------------------------------------
    */
    public function saveAnswer(TaskSubmission $submission, ?int $questionId, array $data): SubmissionAnswer
    {
        return SubmissionAnswer::updateOrCreate(
            [
                'task_submission_id' => $submission->id,
                'task_question_id' => $questionId,
            ],
            [
                'answer_text'      => $data['answer'] ?? null,
                'execution_output' => $data['execution_output'] ?? null,
                'error_message'    => $data['error_message'] ?? null,
                'file_path'        => $data['file_path'] ?? null,
                'github_link'      => $data['github_link'] ?? null,
                'ai_feedback'      => $data['ai_feedback'] ?? null,
                'ai_score'         => $data['ai_score'] ?? null,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Final Submit Task
    |--------------------------------------------------------------------------
    */
    public function submit(TaskSubmission $submission)
    {
        return DB::transaction(function () use ($submission) {

            // 1. Update status to submitted
            $statusId = SubmissionStatus::where('slug', 'submitted')->value('id');
            
            $submission->update([
                'status_id' => $statusId,
                'submitted_at' => now(),
            ]);

            // 2. Trigger Evaluation
            try {
                return $this->evaluationService->evaluateSubmission($submission);
            } catch (\Throwable $e) {
                // Evaluation fails but submission is saved
                return $submission;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Mentor Dashboard: List Submissions
    |--------------------------------------------------------------------------
    */
    public function listForMentor(User $mentor)
    {
        return TaskSubmission::whereHas('task', function ($query) use ($mentor) {
                $query->where('created_by', $mentor->id);
            })
            ->whereHas('status', function ($query) {
                $query->whereIn('slug', ['submitted', 'ai_evaluated', 'completed']); 
            })
            ->with(['intern', 'task', 'status', 'reviewer'])
            ->latest('submitted_at')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Mentor Finish Review
    |--------------------------------------------------------------------------
    */
    public function review(TaskSubmission $submission, User $mentor, float $score, ?string $feedback)
    {
        $statusId = SubmissionStatus::where('slug', 'completed')->value('id');

        return $submission->update([
            'final_percentage' => $score,
            'final_feedback' => $feedback,
            'reviewed_by' => $mentor->id,
            'reviewed_at' => now(),
            'status_id' => $statusId,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Manual evaluation trigger
    |--------------------------------------------------------------------------
    */
    public function evaluateNow(TaskSubmission $submission)
    {
        return $this->evaluationService->evaluateSubmission($submission);
    }

    /**
     * Legacy support (if still used elsewhere)
     * @deprecated
     */
    public function submitLegacy(Task $task, array $answers)
    {
        $user = Auth::user();
        
        $submission = $this->getOrCreateInProgressSubmission($task, $user);

        foreach ($answers as $qId => $ans) {
            $this->saveAnswer($submission, $qId, ['answer' => $ans]);
        }

        return $this->submit($submission);
    }
}