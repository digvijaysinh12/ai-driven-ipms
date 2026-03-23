<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Question;
use App\Models\ReferenceSolution;
use App\Models\Topic;
use App\Exceptions\AIServiceException;

class GroqQuestionService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.groq.api_key');
        $this->baseUrl = 'https://api.groq.com/openai/v1/chat/completions';
    }

    public function generateQuestions(Topic $topic, string $type, int $count): bool
    {
        Log::info("Starting AI question generation for topic {$topic->id}, type {$type}, count {$count}");

        $prompt = $this->buildPrompt($type, $count, $topic->title);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post($this->baseUrl, [
            'model'           => 'llama-3.1-8b-instant',
            'messages'        => [
                [
                    'role'    => 'system',
                    'content' => 'You are a PHP exam question generator. Always return valid JSON only. No extra text.'
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt
                ]
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.3,
        ]);

        if (!$response->successful()) {
            throw new AIServiceException('Groq API Error: ' . $response->body());
        }

        $text = $response->json('choices.0.message.content');

        if (!$text) {
            throw new AIServiceException('Empty Groq response.');
        }

        $data = json_decode($text, true);

        if (!isset($data['questions'])) {
            throw new AIServiceException('Invalid AI JSON structure.');
        }

        foreach ($data['questions'] as $item) {

            // Build question row
            $questionData = [
                'topic_id'          => $topic->id,
                'language'          => 'php',
                'type'              => $type,
                'problem_statement' => $item['question'],
                'code'              => isset($item['code']) ? trim($item['code']) : null,
                'correct_answer'    => $item['correct_answer'] ?? null,
            ];

            // MCQ: store 4 options
            if ($type === 'mcq') {
                $opts = $item['options'] ?? [];
                $questionData['option_a'] = $opts[0] ?? null;
                $questionData['option_b'] = $opts[1] ?? null;
                $questionData['option_c'] = $opts[2] ?? null;
                $questionData['option_d'] = $opts[3] ?? null;
                // correct_answer = "A" / "B" / "C" / "D"
                $questionData['correct_answer'] = $item['correct_option'] ?? null;
            }

            $question = Question::create($questionData);

            // Store reference solution for every question
            $solutionText = $item['reference_solution'] ?? $item['correct_answer'] ?? null;

            if ($solutionText) {
                ReferenceSolution::create([
                    'question_id'  => $question->id,
                    'solution_code'=> $solutionText,
                    'explanation'  => $item['explanation'] ?? null,
                    'created_by'   => 'ai',
                ]);
            }
        }

        Log::info("Successfully generated {$count} {$type} questions for topic {$topic->id}");

        return true;
    }


    private function buildPrompt(string $type, int $count, string $topic): string
    {
        return match ($type) {

            'mcq' => "
Generate {$count} PHP MCQ questions about the topic: {$topic}

Rules:
- Each question must have exactly 4 options (A, B, C, D)
- correct_option must be exactly one of: A, B, C, D
- Include a short explanation of why the answer is correct

Return JSON exactly like this:
{
  \"questions\": [
    {
      \"question\": \"What does strlen() return in PHP?\",
      \"options\": [\"Length of string\", \"Array size\", \"Boolean value\", \"Object count\"],
      \"correct_option\": \"A\",
      \"explanation\": \"strlen() returns the number of characters in a string.\"
    }
  ]
}",

            'true_false' => "
Generate {$count} True/False PHP questions about the topic: {$topic}

Rules:
- correct_answer must be exactly \"True\" or \"False\"
- Include a short explanation

Return JSON exactly like this:
{
  \"questions\": [
    {
      \"question\": \"PHP is a server-side scripting language.\",
      \"correct_answer\": \"True\",
      \"explanation\": \"PHP runs on the server and generates HTML output.\"
    }
  ]
}",

            'blank' => "
Generate {$count} fill-in-the-blank PHP questions about the topic: {$topic}

Rules:
- Use _____ as the blank in the question
- correct_answer is the word/phrase that fills the blank
- Include a short explanation

Return JSON exactly like this:
{
  \"questions\": [
    {
      \"question\": \"The PHP function to get the length of a string is _____.\",
      \"correct_answer\": \"strlen()\",
      \"explanation\": \"strlen() counts the number of characters in a string.\"
    }
  ]
}",

            'output' => "Generate {$count} PHP output prediction questions about the topic: {$topic}\n\n" . <<<'PROMPT'
Rules:
- The question text must always be: "What will be the output of the following PHP code?"
- The code must be valid PHP, multiple lines, use \n to separate lines inside the string
- correct_answer is the EXACT console output the code produces
- reference_solution is the same value as correct_answer
- Include a short explanation of why that output is produced

Return JSON exactly like this:
{
  "questions": [
    {
      "question": "What will be the output of the following PHP code?",
      "code": "$x = 5;\necho $x * 2;",
      "correct_answer": "10",
      "reference_solution": "10",
      "explanation": "5 multiplied by 2 is 10, which echo prints."
    }
  ]
}
PROMPT,

            'coding' => "Generate {$count} beginner PHP coding problems about the topic: {$topic}\n\n" . <<<'PROMPT'
Rules:
- Each question is a coding problem the intern must solve by writing PHP code
- reference_solution must be a clean, complete, working PHP solution
- Include a short explanation of the solution approach

Return JSON exactly like this:
{
  "questions": [
    {
      "question": "Write a PHP function that takes an array and returns the sum of all elements.",
      "reference_solution": "<?php\nfunction arraySum($arr) {\n    return array_sum($arr);\n}",
      "explanation": "array_sum() iterates through the array and returns the total of all numeric values."
    }
  ]
}
PROMPT,

            default => throw new \Exception("Unknown question type: {$type}")
        };
    }
}