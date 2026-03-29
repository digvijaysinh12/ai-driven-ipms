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
<<<<<<< HEAD
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.groq.api_key', '');
=======
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.groq.api_key');
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        $this->baseUrl = 'https://api.groq.com/openai/v1/chat/completions';
    }

    public function generateQuestions(Topic $topic, string $type, int $count): bool
    {
<<<<<<< HEAD
        Log::info("Starting AI question generation", [
            'topic_id' => $topic->id,
            'type'     => $type,
            'count'    => $count,
        ]);
=======
        Log::info("Starting AI question generation for topic {$topic->id}, type {$type}, count {$count}");
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26

        $prompt = $this->buildPrompt($type, $count, $topic->title);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
<<<<<<< HEAD
        ])->timeout(60)->post($this->baseUrl, [
=======
        ])->post($this->baseUrl, [
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
            'model'           => 'llama-3.1-8b-instant',
            'messages'        => [
                [
                    'role'    => 'system',
<<<<<<< HEAD
                    'content' => 'You are a PHP exam question generator. Always return valid JSON only. No extra text.',
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
=======
                    'content' => 'You are a PHP exam question generator. Always return valid JSON only. No extra text.'
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt
                ]
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.3,
        ]);

<<<<<<< HEAD
        if (! $response->successful()) {
=======
        if (!$response->successful()) {
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
            throw new AIServiceException('Groq API Error: ' . $response->body());
        }

        $text = $response->json('choices.0.message.content');

<<<<<<< HEAD
        if (! $text) {
=======
        if (!$text) {
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
            throw new AIServiceException('Empty Groq response.');
        }

        $data = json_decode($text, true);

<<<<<<< HEAD
        if (! isset($data['questions'])) {
            throw new AIServiceException('Invalid AI JSON structure — missing "questions" key.');
=======
        if (!isset($data['questions'])) {
            throw new AIServiceException('Invalid AI JSON structure.');
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        }

        foreach ($data['questions'] as $item) {

<<<<<<< HEAD
=======
            // Build question row
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
            $questionData = [
                'topic_id'          => $topic->id,
                'language'          => 'php',
                'type'              => $type,
<<<<<<< HEAD
                'problem_statement' => $item['question'] ?? '',
=======
                'problem_statement' => $item['question'],
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
                'code'              => isset($item['code']) ? trim($item['code']) : null,
                'correct_answer'    => $item['correct_answer'] ?? null,
            ];

            // MCQ: store 4 options
            if ($type === 'mcq') {
                $opts = $item['options'] ?? [];
<<<<<<< HEAD
                $questionData['option_a']       = $opts[0] ?? null;
                $questionData['option_b']       = $opts[1] ?? null;
                $questionData['option_c']       = $opts[2] ?? null;
                $questionData['option_d']       = $opts[3] ?? null;
=======
                $questionData['option_a'] = $opts[0] ?? null;
                $questionData['option_b'] = $opts[1] ?? null;
                $questionData['option_c'] = $opts[2] ?? null;
                $questionData['option_d'] = $opts[3] ?? null;
                // correct_answer = "A" / "B" / "C" / "D"
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
                $questionData['correct_answer'] = $item['correct_option'] ?? null;
            }

            $question = Question::create($questionData);

<<<<<<< HEAD
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
=======
            // Store reference solution for every question
            $solutionText = $item['reference_solution'] ?? $item['correct_answer'] ?? null;

            if ($solutionText) {
                ReferenceSolution::create([
                    'question_id'  => $question->id,
                    'solution_code'=> $solutionText,
                    'explanation'  => $item['explanation'] ?? null,
                    'created_by'   => 'ai',
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
                ]);
            }
        }

<<<<<<< HEAD
        Log::info("Generated {$count} {$type} questions for topic {$topic->id}");
=======
        Log::info("Successfully generated {$count} {$type} questions for topic {$topic->id}");
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26

        return true;
    }

<<<<<<< HEAD
=======

>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    private function buildPrompt(string $type, int $count, string $topic): string
    {
        return match ($type) {

<<<<<<< HEAD
            'mcq' => <<<PROMPT
=======
            'mcq' => "
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
Generate {$count} PHP MCQ questions about the topic: {$topic}

Rules:
- Each question must have exactly 4 options (A, B, C, D)
- correct_option must be exactly one of: A, B, C, D
<<<<<<< HEAD
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
=======
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
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
- Include a short explanation

Return JSON exactly like this:
{
<<<<<<< HEAD
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
=======
  \"questions\": [
    {
      \"question\": \"PHP is a server-side scripting language.\",
      \"correct_answer\": \"True\",
      \"explanation\": \"PHP runs on the server and generates HTML output.\"
    }
  ]
}",

            'blank' => "
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
Generate {$count} fill-in-the-blank PHP questions about the topic: {$topic}

Rules:
- Use _____ as the blank in the question
- correct_answer is the word/phrase that fills the blank
- Include a short explanation

Return JSON exactly like this:
{
<<<<<<< HEAD
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
=======
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
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26

Return JSON exactly like this:
{
  "questions": [
    {
      "question": "What will be the output of the following PHP code?",
      "code": "$x = 5;\necho $x * 2;",
      "correct_answer": "10",
      "reference_solution": "10",
<<<<<<< HEAD
      "explanation": "5 multiplied by 2 equals 10."
=======
      "explanation": "5 multiplied by 2 is 10, which echo prints."
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    }
  ]
}
PROMPT,

<<<<<<< HEAD
            'coding' => <<<PROMPT
Generate {$count} PHP coding challenge questions about the topic: {$topic}

Rules:
- Each question should ask the intern to write a PHP function or script
- reference_solution must be a complete working PHP solution
- Include a short explanation
=======
            'coding' => "Generate {$count} beginner PHP coding problems about the topic: {$topic}\n\n" . <<<'PROMPT'
Rules:
- Each question is a coding problem the intern must solve by writing PHP code
- reference_solution must be a clean, complete, working PHP solution
- Include a short explanation of the solution approach
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26

Return JSON exactly like this:
{
  "questions": [
    {
<<<<<<< HEAD
      "question": "Write a PHP function that reverses a string without using strrev().",
      "reference_solution": "function reverseString($str) {\n  $result = '';\n  for ($i = strlen($str) - 1; $i >= 0; $i--) {\n    $result .= $str[$i];\n  }\n  return $result;\n}",
      "explanation": "We iterate from the last character to the first and build the reversed string."
=======
      "question": "Write a PHP function that takes an array and returns the sum of all elements.",
      "reference_solution": "<?php\nfunction arraySum($arr) {\n    return array_sum($arr);\n}",
      "explanation": "array_sum() iterates through the array and returns the total of all numeric values."
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    }
  ]
}
PROMPT,

<<<<<<< HEAD
            default => throw new AIServiceException("Unsupported question type: {$type}"),
=======
            default => throw new \Exception("Unknown question type: {$type}")
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        };
    }
}