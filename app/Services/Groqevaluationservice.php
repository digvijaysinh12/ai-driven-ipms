<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\InternTopicAssignment;
use App\Models\Submission;
use App\Models\Question;
<<<<<<< HEAD
=======
use App\Models\ReferenceSolution;
use App\Models\Topic;
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26

class GroqEvaluationService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
<<<<<<< HEAD
        $this->apiKey  = config('services.groq.api_key', '');
        $this->baseUrl = 'https://api.groq.com/openai/v1/chat/completions';
    }

    /**
     * Evaluate the ENTIRE exercise for one assignment.
     * Called once when intern clicks "Submit Exercise".
     * Returns: grade (A/B/C/D/E) + overall feedback.
     */
=======
        $this->apiKey  = config('services.groq.api_key');
        $this->baseUrl = 'https://api.groq.com/openai/v1/chat/completions';
    }

    // ─────────────────────────────────────────────────────────────
    // Evaluate the ENTIRE exercise for one assignment
    // Called once when intern clicks "Submit Exercise"
    // Returns: grade (A/B/C/D/E) + overall feedback
    // ─────────────────────────────────────────────────────────────
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    public function evaluateExercise(InternTopicAssignment $assignment): array
    {
        $topic       = $assignment->topic;
        $internId    = $assignment->intern_id;
        $questionIds = Question::where('topic_id', $topic->id)->pluck('id');

        // Load all submissions for this assignment
        $submissions = Submission::where('intern_id', $internId)
            ->whereIn('question_id', $questionIds)
            ->with('question.referenceSolution')
            ->get()
            ->keyBy('question_id');

        // Load all questions with reference solutions
        $questions = Question::whereIn('id', $questionIds)
            ->with('referenceSolution')
            ->get();

        // Build the combined prompt
        $prompt = $this->buildExercisePrompt($topic->title, $questions, $submissions);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(120)->post($this->baseUrl, [
<<<<<<< HEAD
            'model'    => 'llama-3.1-8b-instant',
            'messages' => [
=======
            'model'           => 'llama-3.1-8b-instant',
            'messages'        => [
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
                [
                    'role'    => 'system',
                    'content' => 'You are a PHP internship exercise evaluator. '
                               . 'Evaluate all answers holistically and return ONLY valid JSON. '
                               . 'No markdown, no extra text, no code blocks.',
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.2,
            'max_tokens'      => 1000,
        ]);

<<<<<<< HEAD
        if (! $response->successful()) {
=======
        if (!$response->successful()) {
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
            throw new \Exception('Groq API Error: ' . $response->body());
        }

        $text = $response->json('choices.0.message.content');

<<<<<<< HEAD
        if (! $text) {
=======
        if (!$text) {
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
            throw new \Exception('Empty response from Groq.');
        }

        $result = json_decode($text, true);

<<<<<<< HEAD
        if (! isset($result['grade'])) {
            throw new \Exception('Invalid AI response structure — missing grade.');
        }

        // Validate grade A-E
        $grade = strtoupper(trim($result['grade']));
        if (! in_array($grade, ['A', 'B', 'C', 'D', 'E'])) {
=======
        if (!isset($result['grade'])) {
            throw new \Exception('Invalid AI response structure — missing grade.');
        }

        // Validate grade is A-E
        $grade = strtoupper(trim($result['grade']));
        if (!in_array($grade, ['A', 'B', 'C', 'D', 'E'])) {
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
            $grade = 'E';
        }

        // Save grade + feedback back to the assignment
        $assignment->update([
            'status'       => 'evaluated',
            'grade'        => $grade,
            'feedback'     => $result['overall_feedback'] ?? null,
            'submitted_at' => $assignment->submitted_at ?? now(),
        ]);

        // Mark all submissions as ai_evaluated
        Submission::where('intern_id', $internId)
            ->whereIn('question_id', $questionIds)
            ->update(['status' => 'ai_evaluated']);

        return [
            'grade'    => $grade,
            'feedback' => $result['overall_feedback'] ?? '',
<<<<<<< HEAD
            'summary'  => $result['summary'] ?? '',
        ];
    }

    /**
     * Build the combined prompt — all questions + answers in one go.
     */
    private function buildExercisePrompt(string $topicTitle, $questions, $submissions): string
    {
        $total    = $questions->count();
        $answered = $submissions->count();

        $lines   = [];
        $lines[] = "TOPIC: {$topicTitle}";
        $lines[] = "TOTAL QUESTIONS: {$total}";
        $lines[] = "ANSWERED: {$answered}";
        $lines[] = '';
        $lines[] = "INTERN'S ANSWERS:";
        $lines[] = str_repeat('-', 60);

        foreach ($questions as $i => $q) {
            $num       = $i + 1;
            $type      = strtoupper(str_replace('_', ' ', $q->type));
            $reference = $q->referenceSolution;
            $correct   = $q->correct_answer
                      ?? $reference?->solution_code
                      ?? 'N/A';

            $submission = $submissions->get($q->id);
            $answer     = $submission
                ? trim($submission->submitted_code)
                : '[NOT ANSWERED]';

            $lines[] = '';
=======
            'summary'  => $result['summary']          ?? '',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Build the combined prompt — all questions + answers in one go
    // ─────────────────────────────────────────────────────────────
    private function buildExercisePrompt(
        string $topicTitle,
        $questions,
        $submissions
    ): string {

        $total    = $questions->count();
        $answered = $submissions->count();

        $lines = [];
        $lines[] = "TOPIC: {$topicTitle}";
        $lines[] = "TOTAL QUESTIONS: {$total}";
        $lines[] = "ANSWERED: {$answered}";
        $lines[] = "";
        $lines[] = "INTERN'S ANSWERS:";
        $lines[] = str_repeat("-", 60);

        foreach ($questions as $i => $q) {
            $num        = $i + 1;
            $type       = strtoupper(str_replace('_', ' ', $q->type));
            $reference  = $q->referenceSolution;
            $correct    = $q->correct_answer
                       ?? $reference?->solution_code
                       ?? 'N/A';

            $submission = $submissions->get($q->id);
            $answer     = $submission ? trim($submission->submitted_code) : '[NOT ANSWERED]';

            $lines[] = "";
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
            $lines[] = "Q{$num} [{$type}]";
            $lines[] = "Question: {$q->problem_statement}";

            if ($q->code) {
                $lines[] = "Code:\n{$q->code}";
            }

            if ($q->type === 'mcq') {
                $lines[] = "Options: A={$q->option_a} | B={$q->option_b} | C={$q->option_c} | D={$q->option_d}";
                $lines[] = "Correct Answer: {$correct}";
            } elseif (in_array($q->type, ['true_false', 'blank', 'output'])) {
                $lines[] = "Correct Answer: {$correct}";
            } else {
                $lines[] = "Reference Solution:\n{$correct}";
            }

            $lines[] = "Intern's Answer: {$answer}";
        }

<<<<<<< HEAD
        $lines[] = '';
        $lines[] = str_repeat('-', 60);
        $lines[] = '';
        $lines[] = 'GRADING RULES:';
        $lines[] = 'Evaluate all answers together as a complete exercise.';
        $lines[] = 'Calculate the percentage of correct/good answers:';
        $lines[] = '  A = 90-100% correct (Excellent)';
        $lines[] = '  B = 75-89%  correct (Good)';
        $lines[] = '  C = 60-74%  correct (Average)';
        $lines[] = '  D = 40-59%  correct (Below Average)';
        $lines[] = '  E = 0-39%   correct (Poor / Needs Improvement)';
        $lines[] = '';
        $lines[] = 'For objective questions (mcq, true_false, blank, output): mark correct or incorrect.';
        $lines[] = 'For coding questions: judge by correctness of logic and working solution.';
        $lines[] = 'Unanswered questions count as wrong.';
        $lines[] = '';
        $lines[] = 'Return ONLY this JSON (no markdown, no backticks):';
        $lines[] = '{';
        $lines[] = '  "grade": "B",';
        $lines[] = '  "summary": "15 out of 20 questions answered correctly.",';
        $lines[] = '  "overall_feedback": "Good understanding shown. Focus on output tracing."';
=======
        $lines[] = "";
        $lines[] = str_repeat("-", 60);
        $lines[] = "";
        $lines[] = "GRADING RULES:";
        $lines[] = "Evaluate all answers together as a complete exercise.";
        $lines[] = "Calculate the percentage of correct/good answers:";
        $lines[] = "  A = 90-100% correct (Excellent)";
        $lines[] = "  B = 75-89%  correct (Good)";
        $lines[] = "  C = 60-74%  correct (Average)";
        $lines[] = "  D = 40-59%  correct (Below Average)";
        $lines[] = "  E = 0-39%   correct (Poor / Needs Improvement)";
        $lines[] = "";
        $lines[] = "For objective questions (mcq, true_false, blank, output): mark correct or incorrect.";
        $lines[] = "For coding questions: judge by correctness of logic and working solution.";
        $lines[] = "Unanswered questions count as wrong.";
        $lines[] = "";
        $lines[] = "Return ONLY this JSON (no markdown, no backticks):";
        $lines[] = '{';
        $lines[] = '  "grade": "B",';
        $lines[] = '  "summary": "15 out of 20 questions answered correctly.",';
        $lines[] = '  "overall_feedback": "Good understanding of PHP arrays. Struggled with output prediction questions. Focus on tracing code execution step by step."';
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        $lines[] = '}';

        return implode("\n", $lines);
    }
}