<?php

namespace App\Services;

use App\Models\TaskSubmission;
use App\Models\SubmissionAnswer;
use App\Models\SubmissionStatus;
use App\Services\AI\AIService;
use Illuminate\Support\Facades\Log;

class EvaluationService
{
    public function __construct(protected AIService $ai)
    {
    }

    /**
     * Evaluate a full submission
     */
    public function evaluateSubmission(TaskSubmission $submission): array
    {
        $submission->load('answers.question');

        /*
        |--------------------------------------------------------------------------
        | 1. Build Evaluation Data
        |--------------------------------------------------------------------------
        */
        $answersData = [];

        foreach ($submission->answers as $answer) {
            $q = $answer->question;

            $answersData[] = [
                'question_id' => $q ? $q->id : null,
                'question' => $q ? $q->question : $submission->task->title,
                'type' => $q ? $q->type : $submission->task->type?->slug,
                'answer' => $answer->answer_text 
                    ?? $answer->github_link 
                    ?? $answer->file_path,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Call AI Service for Evaluation
        |--------------------------------------------------------------------------
        */
        try {
            $result = $this->ai->evaluateSubmissionData($answersData);

            // 3. Save Per-Question Breakdown
            if (isset($result['breakdown']) && is_array($result['breakdown'])) {
                foreach ($result['breakdown'] as $item) {
                    SubmissionAnswer::where('task_submission_id', $submission->id)
                        ->where('task_question_id', $item['question_id'])
                        ->update([
                            'ai_score' => $item['score'] ?? 0,
                            'ai_feedback' => $item['feedback'] ?? null,
                        ]);
                }
            }

            // 4. Update Main Submission
            $evaluatedStatus = SubmissionStatus::where('slug', 'ai_evaluated')->value('id')
                ?? SubmissionStatus::where('slug', 'evaluated')->value('id');

            $submission->update([
                'percentage' => $result['percentage'],
                'ai_feedback' => $result['feedback'],
                'status_id' => $evaluatedStatus,
            ]);

            return $result;

        } catch (\Throwable $e) {
            Log::error('Evaluation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Evaluate a single answer (Optional for live feedback)
     */
    public function evaluateAnswer(SubmissionAnswer $answer): array
    {
        // Placeholder for future per-question evaluation logic
        return [
            'status' => 'success',
            'message' => 'Answer recorded'
        ];
    }
}
