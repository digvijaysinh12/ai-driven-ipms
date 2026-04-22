<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionAnswer extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'submission_answers';

    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'task_submission_id',
        'task_question_id',

        // Main Answer (all types)
        'answer_text',

        // Coding
        'execution_output',
        'error_message',

        // File / GitHub
        'file_path',
        'github_link',

        // AI feedback and scoring
        'ai_feedback',
        'ai_score',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Belongs to submission
    public function submission(): BelongsTo
    {
        return $this->belongsTo(TaskSubmission::class, 'task_submission_id');
    }

    // Belongs to question
    public function question(): BelongsTo
    {
        return $this->belongsTo(TaskQuestion::class, 'task_question_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods (for logic handling)
    |--------------------------------------------------------------------------
    */

    // Check type safely
    public function getTypeAttribute(): ?string
    {
        // If question exists → use question type
        if ($this->question) {
            return $this->question->type;
        }

        // Else → fallback to task type
        return $this->submission?->task?->type?->slug;
    }

    public function isObjective(): bool
    {
        return in_array($this->type, ['mcq', 'true_false', 'blank']);
    }

    public function isCoding(): bool
    {
        return $this->type === 'coding';
    }

    public function isDescriptive(): bool
    {
        return $this->type === 'descriptive';
    }

    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    public function isGithub(): bool
    {
        return $this->type === 'github';
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: Check if answer is empty
    |--------------------------------------------------------------------------
    */
    public function isEmpty(): bool
    {
        return empty($this->answer_text)
            && empty($this->file_path)
            && empty($this->github_link);
    }
}
