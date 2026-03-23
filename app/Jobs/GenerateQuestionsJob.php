<?php

namespace App\Jobs;

use App\Models\Topic;
use App\Services\GroqQuestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateQuestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $topic;
    protected $type;
    protected $count;

    public function __construct(Topic $topic, string $type, int $count)
    {
        $this->topic = $topic;
        $this->type = $type;
        $this->count = $count;
    }

    public function handle(GroqQuestionService $aiService)
    {
        $aiService->generateQuestions($this->topic, $this->type, $this->count);
    }
}