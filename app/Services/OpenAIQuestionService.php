<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Question;
use App\Models\ReferenceSolution;
use App\Models\Topic;

class OpenAIQuestionService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->baseUrl = "https://api.openai.com/v1/chat/completions";
    }

    public function generateQuestions(Topic $topic)
    {
        $prompt = "
Generate ONE coding question for topic: {$topic->title}
Language: PHP

Return strictly valid JSON in this format:

{
  \"problem_statement\": \"...\",
  \"max_syntax_marks\": 5,
  \"max_logic_marks\": 10,
  \"max_structure_marks\": 5,
  \"solutions\": [
    {
      \"code\": \"...\",
      \"explanation\": \"...\"
    },
    {
      \"code\": \"...\",
      \"explanation\": \"...\"
    }
  ]
}
";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, [
            "model" => "gpt-4o-mini",  // cheaper model
            "messages" => [
                [
                    "role" => "system",
                    "content" => "You are an expert coding question generator."
                ],
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            "temperature" => 0.7
        ]);

        if (!$response->successful()) {
            dd($response->status(), $response->body());
        }

        $result = $response->json();

        $text = $result['choices'][0]['message']['content'] ?? null;

        if (!$text) {
            throw new \Exception("Invalid OpenAI response.");
        }

        $text = preg_replace('/```json|```/', '', $text);

        $data = json_decode($text, true);

        if (!$data) {
            throw new \Exception("OpenAI returned invalid JSON.");
        }

        $question = Question::create([
            'topic_id' => $topic->id,
            'language' => Question::LANG_PHP,
            'problem_statement' => $data['problem_statement'],
            'max_syntax_marks' => $data['max_syntax_marks'],
            'max_logic_marks' => $data['max_logic_marks'],
            'max_structure_marks' => $data['max_structure_marks'],
            'total_marks' =>
                $data['max_syntax_marks'] +
                $data['max_logic_marks'] +
                $data['max_structure_marks'],
        ]);

        foreach ($data['solutions'] as $solution) {
            ReferenceSolution::create([
                'question_id' => $question->id,
                'solution_code' => $solution['code'],
                'explanation' => $solution['explanation'],
                'created_by' => ReferenceSolution::CREATED_BY_AI,
            ]);
        }

        return $question;
    }
}