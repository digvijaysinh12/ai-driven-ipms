<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action_type',
        'model_used',
        'prompt_data',
        'response_data',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'latency_ms'
    ];

    protected $casts = [
        'prompt_data' => 'array',
        'response_data' => 'array',
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens' => 'integer',
        'latency_ms' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
