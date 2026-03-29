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
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.groq.api_key', '');
        $this->baseUrl = 'https://api.groq.com/openai/v1/chat/completions';
    }

    public function generateQuestions(Topic $topic, string $type, int $count): bool
    {
        Log::info("Starting AI question generation", [
            'topic_id' => $topic->id,
            'type'     => $type,
            'count'    => $count,
        ]);

        $prompt = $this->buildPrompt($type, $count, $topic->title);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(60)->post($this->baseUrl, [
            'model'           => 'llama-3.1-8b-instant',
            'messages'        => [
                [
                    'role'    => 'system',
                    'content' => 'You are a PHP exam question generator. Always return valid JSON only. No extra text.',
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.3,
        ]);

        if (! $response->successful()) {
            throw new AIServiceException('Groq API Error: ' . $response->body());
        }

        $text = $response->json('choices.0.message.content');

        if (! $text) {
            throw new AIServiceException('Empty Groq response.');
        }

        $data = json_decode($text, true);

        if (! isset($data['questions'])) {
            throw new AIServiceException('Invalid AI JSON structure — missing "questions" key.');
        }

        foreach ($data['questions'] as $item) {

            $questionData = [
                'topic_id'          => $topic->id,
                'language'          => 'php',
                'type'              => $type,
                'problem_statement' => $item['question'] ?? '',
                'code'              => isset($item['code']) ? trim($item['code']) : null,
                'correct_answer'    => $item['correct_answer'] ?? null,
            ];

            // MCQ: store 4 options
            if ($type === 'mcq') {
                $opts = $item['options'] ?? [];
                $questionData['option_a']       = $opts[0] ?? null;
                $questionData['option_b']       = $opts[1] ?? null;
                $questionData['option_c']       = $opts[2] ?? null;
                $questionData['option_d']       = $opts[3] ?? null;
                $questionData['correct_answer'] = $item['correct_option'] ?? null;
            }

            $question = Question::create($questionData);

            // Store reference solution
            $solutionText = $item['reference_solution']
                ?? $item['correct_answer']
                ?? null;

            if ($solutionText) {
                ReferenceSolution::create([
                    'question_id'   => $question->id,
                    'solution_code' => $solutionText,
                    'explanation'   => $item['explanation'] ?? null,
                    'created_by'    => 'ai',
                ]);
            }
        }

        Log::info("Generated {$count} {$type} questions for topic {$topic->id}");

        return true;
    }

    private function buildPrompt(string $type, int $count, string $topic): string
    {
        return match ($type) {

            'mcq' => <<<PROMPT
Generate {$count} PHP MCQ questions about the topic: {$topic}

Rules:
- Each question must have exactly 4 options (A, B, C, D)
- correct_option must be exactly one of: A, B, C, D
- Include a short explanation

Return JSON exactly like this:
{
  "questions": [
    {
      "question": "What does strlen() return in PHP?",
      "options": ["Length of string", "Array size", "Boolean value", "Object count"],
      "correct_option": "A",
      "explanation": "strlen() returns the number of characters in a string."
    }
  ]
}
PROMPT,

            'true_false' => <<<PROMPT
Generate {$count} True/False PHP questions about the topic: {$topic}

Rules:
- correct_answer must be exactly "True" or "False"
- Include a short explanation

Return JSON exactly like this:
{
  "questions": [
    {
      "question": "PHP is a server-side scripting language.",
      "correct_answer": "True",
      "explanation": "PHP runs on the server and generates HTML output."
    }
  ]
}
PROMPT,

            'blank' => <<<PROMPT
Generate {$count} fill-in-the-blank PHP questions about the topic: {$topic}

Rules:
- Use _____ as the blank in the question
- correct_answer is the word/phrase that fills the blank
- Include a short explanation

Return JSON exactly like this:
{
  "questions": [
    {
      "question": "The PHP function to get the length of a string is _____.",
      "correct_answer": "strlen()",
      "explanation": "strlen() counts the number of characters in a string."
    }
  ]
}
PROMPT,

            'output' => <<<PROMPT
Generate {$count} PHP output prediction questions about the topic: {$topic}

Rules:
- The question text must always be: "What will be the output of the following PHP code?"
- The code must be valid PHP
- correct_answer is the EXACT console output
- reference_solution is the same as correct_answer
- Include a short explanation

Return JSON exactly like this:
{
  "questions": [
    {
      "question": "What will be the output of the following PHP code?",
      "code": "$x = 5;\necho $x * 2;",
      "correct_answer": "10",
      "reference_solution": "10",
      "explanation": "5 multiplied by 2 equals 10."
    }
  ]
}
PROMPT,

            'coding' => <<<PROMPT
Generate {$count} PHP coding challenge questions about the topic: {$topic}

Rules:
- Each question should ask the intern to write a PHP function or script
- reference_solution must be a complete working PHP solution
- Include a short explanation

Return JSON exactly like this:
{
  "questions": [
    {
      "question": "Write a PHP function that reverses a string without using strrev().",
      "reference_solution": "function reverseString($str) {\n  $result = '';\n  for ($i = strlen($str) - 1; $i >= 0; $i--) {\n    $result .= $str[$i];\n  }\n  return $result;\n}",
      "explanation": "We iterate from the last character to the first and build the reversed string."
    }
  ]
}
PROMPT,

            default => throw new AIServiceException("Unsupported question type: {$type}"),
        };
    }
}