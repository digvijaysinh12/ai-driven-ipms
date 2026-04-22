<?php

namespace App\Services\AI;

use App\Exceptions\AIServiceException;
use App\Models\Task;
use App\Models\TaskType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private const MODEL = 'llama-3.1-8b-instant';

    /**
     * Generate Questions
     */
    public function generateQuestions(Task $task, int $count = 5): array
    {
        $type = $this->resolveTaskTypeSlug($task);

        Log::channel('ai')->info('AI Start', [
            'task_id' => $task->id,
            'type' => $type,
            'difficulty' => $task->difficulty,
            'language' => $task->language,
            'count' => $count
        ]);

        $apiKey = config('services.groq.api_key');

        if (!$apiKey) {
            throw new AIServiceException('GROQ_API_KEY missing');
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => self::MODEL,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Return ONLY valid JSON.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->buildQuestionPrompt($task, $type, $count)
                        ]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.3,
                ]);

        } catch (\Throwable $e) {
            Log::channel('ai')->error('HTTP Error', [
                'error' => $e->getMessage()
            ]);

            throw new AIServiceException('AI request failed');
        }

        if (!$response->successful()) {
            Log::channel('ai')->error('API Error', [
                'body' => $response->body()
            ]);

            throw new AIServiceException('AI API failed');
        }

        $raw = $response->json('choices.0.message.content');

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            Log::channel('ai')->error('Invalid JSON', ['raw' => $raw]);
            throw new AIServiceException('Invalid AI response');
        }

        $questions = Arr::get($decoded, 'questions', []);

        if (!is_array($questions)) {
            throw new AIServiceException('Invalid questions format');
        }

        $result = collect($questions)
            ->map(fn ($q) => $this->formatQuestion($q, $type))
            ->filter(fn ($q) => !empty($q['question']))
            ->values()
            ->all();

        if (empty($result)) {
            throw new AIServiceException('No valid questions generated');
        }

        Log::channel('ai')->info('AI Success', [
            'count' => count($result)
        ]);

        return $result;
    }

    /**
     * Format Question
     */
    private function formatQuestion(array $q, string $type): array
    {
        return match ($type) {

            'coding' => [
                'question' => trim($q['question'] ?? ''),
                'description' => $q['description'] ?? null,
                'input_format' => $q['input_format'] ?? null,
                'output_format' => $q['output_format'] ?? null,
                'constraints' => $q['constraints'] ?? null,
                'test_cases' => $q['test_cases'] ?? [],
                'options' => null,
                'correct_answer' => null,
            ],

            default => [
                'question' => trim($q['question'] ?? ''),
                'options' => match ($type) {
                    'mcq' => array_values(array_filter((array) ($q['options'] ?? []))),
                    'true_false' => ['True', 'False'],
                    default => null,
                },
                'correct_answer' => $q['correct_answer'] ?? null,
            ],
        };
    }

    /**
     * Build Prompt (GLOBAL CONTROL)
     */
    private function buildQuestionPrompt(Task $task, string $type, int $count): string
    {
        $base = "
Task: {$task->title}
Description: {$task->description}

Difficulty: {$task->difficulty}
Programming Language: {$task->language}

Rules:
- Follow difficulty strictly (easy / medium / hard)
- Avoid duplicate or generic questions
- Keep output clean and structured
";

        $instruction = match ($type) {

            'mcq' => "{$base}

Generate {$count} MCQs.

Rules:
- 4 options
- 1 correct answer

Return JSON:
{
  \"questions\": [
    {
      \"question\": \"...\",
      \"options\": [\"A\",\"B\",\"C\",\"D\"],
      \"correct_answer\": \"...\"
    }
  ]
}",

            'true_false' => "{$base}

Generate {$count} True/False questions.

Return JSON:
{
  \"questions\": [
    {
      \"question\": \"...\",
      \"correct_answer\": \"True/False\"
    }
  ]
}",

            'blank' => "{$base}

Generate {$count} fill in the blank questions.

Return JSON:
{
  \"questions\": [
    {
      \"question\": \"...\",
      \"correct_answer\": \"...\"
    }
  ]
}",

            'descriptive' => "{$base}

Generate {$count} descriptive questions.

Return JSON:
{
  \"questions\": [
    {
      \"question\": \"...\"
    }
  ]
}",

            'coding' => "{$base}

Generate {$count} advanced coding problems.

Rules:
- Avoid common problems
- Use real-world or tricky logic
- Include:
  question, description, input_format, output_format, constraints, test_cases

Test Case Rules:
- No JSON input
- Use plain text:
  input: abc
  output: xyz

Return JSON:
{
  \"questions\": [
    {
      \"question\": \"...\",
      \"description\": \"...\",
      \"input_format\": \"...\",
      \"output_format\": \"...\",
      \"constraints\": \"...\",
      \"test_cases\": [
        { \"input\": \"...\", \"output\": \"...\" },
        { \"input\": \"...\", \"output\": \"...\" }
      ]
    }
  ]
}",
        };

        return $instruction;
    }

    /**
     * Resolve type
     */
    private function resolveTaskTypeSlug(Task $task): ?string
    {
        return TaskType::whereKey($task->task_type_id)->value('slug');
    }

    public function evaluateSubmissionData(array $answersData): array
    {
        $apiKey = config('services.groq.api_key');

        if (!$apiKey) {
            throw new AIServiceException('GROQ_API_KEY missing');
        }

        /*
        |--------------------------------------------------------------------------
        | Build Prompt (IMPORTANT FIXED VERSION)
        |--------------------------------------------------------------------------
        */

        $prompt = "
        You are an expert evaluator.

        Evaluate the student's submission based on:
        - correctness
        - understanding
        - logic
        - completeness

        Rules:
        - Be strict but fair
        - If answer is empty → treat as wrong
        - For coding → focus on logic (ignore syntax errors if minor)
        - For descriptive → check explanation quality

        Return ONLY valid JSON (no text outside JSON):

        {
            \"percentage\": number (0-100),
            \"feedback\": \"clear short summary (2-3 lines)\",
            \"breakdown\": [
                {
                    \"question_id\": number,
                    \"score\": number (0-100),
                    \"feedback\": \"short explanation\"
                }
            ]
        }

        Submission Data:
        " . json_encode($answersData, JSON_PRETTY_PRINT);

        /*
        |--------------------------------------------------------------------------
        | API CALL
        |--------------------------------------------------------------------------
        */

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => self::MODEL,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Return ONLY valid JSON.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.2,
                ]);
        } catch (\Throwable $e) {
            Log::channel('ai')->error('Evaluation HTTP Error', [
                'error' => $e->getMessage()
            ]);

            throw new AIServiceException('AI evaluation failed');
        }

        if (!$response->successful()) {
            Log::channel('ai')->error('Evaluation API Error', [
                'body' => $response->body()
            ]);

            throw new AIServiceException('AI evaluation API failed');
        }

        /*
        |--------------------------------------------------------------------------
        | Parse Response
        |--------------------------------------------------------------------------
        */

        $raw = $response->json('choices.0.message.content');

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            Log::channel('ai')->error('Invalid Evaluation JSON', [
                'raw' => $raw
            ]);

            throw new AIServiceException('Invalid AI evaluation response');
        }

        return [
            'percentage' => $decoded['percentage'] ?? 0,
            'feedback' => $decoded['feedback'] ?? 'No feedback',
            'breakdown' => $decoded['breakdown'] ?? []
        ];
    }
}
