<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Question;
use App\Models\ReferenceSolution;
use App\Models\Topic;

class GeminiQuestionService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');

        $this->baseUrl =
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent";
    }

    public function generateQuestions(Topic $topic, $type, $count)
    {
        $generatedQuestions = [];

        for ($i = 1; $i <= $count; $i++) {

            $prompt = "
Generate ONE {$type} programming question for topic: {$topic->title}
Language: PHP

Return strictly valid JSON:

{
  \"problem_statement\": \"...\",
  \"max_syntax_marks\": 5,
  \"max_logic_marks\": 10,
  \"max_structure_marks\": 5,
  \"solutions\": [
    {
      \"code\": \"...\",
      \"explanation\": \"...\"
    }
  ]
}
";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post(
                $this->baseUrl . "?key=" . $this->apiKey,
                [
                    "contents" => [
                        [
                            "parts" => [
                                ["text" => $prompt]
                            ]
                        ]
                    ]
                ]
            );

            if (!$response->successful()) {
                throw new \Exception("Gemini API Error: " . $response->body());
            }

            $result = $response->json();

            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$text) {
                throw new \Exception("Invalid Gemini response.");
            }

            $text = preg_replace('/```json|```/', '', $text);

            $data = json_decode($text, true);

            if (!$data) {
                throw new \Exception("Gemini returned invalid JSON.");
            }

            $question = Question::create([
                'topic_id' => $topic->id,
                'language' => Question::LANG_PHP,
                'type' => $type,
                'problem_statement' => $data['problem_statement'],
                'max_syntax_marks' => $data['max_syntax_marks'],
                'max_logic_marks' => $data['max_logic_marks'],
                'max_structure_marks' => $data['max_structure_marks'],
                'total_marks' =>
                    $data['max_syntax_marks'] +
                    $data['max_logic_marks'] +
                    $data['max_structure_marks'],
            ]);

            if (isset($data['solutions'])) {

                foreach ($data['solutions'] as $solution) {

                    ReferenceSolution::create([
                        'question_id' => $question->id,
                        'solution_code' => $solution['code'],
                        'explanation' => $solution['explanation'],
                        'created_by' => ReferenceSolution::CREATED_BY_AI,
                    ]);
                }
            }

            $generatedQuestions[] = $question;
        }

        return $generatedQuestions;
    }
}