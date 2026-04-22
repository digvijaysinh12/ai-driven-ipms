<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternProgress extends Model
{
    use HasFactory;

    protected $table = 'intern_progress';

    protected $fillable = [
        'user_id',
        'task_id',
        'total_score',
        'completed_at',
        'status_id'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'total_score' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(SubmissionStatus::class, 'status_id');
    }
}
