<?php

namespace App\Events;

use App\Models\Topic;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TopicPublished
{
    use Dispatchable, SerializesModels;

    public $topic;

    public function __construct(Topic $topic)
    {
        $this->topic = $topic;
    }
}