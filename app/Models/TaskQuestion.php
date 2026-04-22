<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskQuestion extends Model
{
    use HasFactory;

    /**
     * Fillable fields (ALL TYPES SUPPORT)
     */
    protected $fillable = [
        'task_id',
        'question',

        // Objective types (mcq, true_false, blank)
        'options',
        'correct_answer',

        // Descriptive & Coding
        'description',

        // Coding specific
        'input_format',
        'output_format',
        'constraints',
        'test_cases',

        // Source (ai/manual)
        'source',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'options' => 'array',
        'test_cases' => 'array',
    ];

    /**
     * Appended attributes
     */
    protected $appends = [
        'question_text',
        'type',
        'is_objective',
        'is_coding',
    ];

    /**
     * Relationships
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function submissionAnswers(): HasMany
    {
        return $this->hasMany(SubmissionAnswer::class, 'task_question_id');
    }

    /**
     * Accessors
     */

    // Standard question text
    public function getQuestionTextAttribute(): string
    {
        return $this->question;
    }

    // Task type slug (mcq, coding, etc)
    public function getTypeAttribute(): ?string
    {
        return $this->task?->type?->slug;
    }

    // Is objective type?
    public function getIsObjectiveAttribute(): bool
    {
        return in_array($this->type, ['mcq', 'true_false', 'blank']);
    }

    // Is coding type?
    public function getIsCodingAttribute(): bool
    {
        return $this->type === 'coding';
    }

    /**
     * Helper Methods (VERY USEFUL)
     */

    // Check if correct answer exists
    public function hasCorrectAnswer(): bool
    {
        return !empty($this->correct_answer);
    }

    // Normalize answer (for comparison)
    public function normalizeAnswer(string $answer): string
    {
        return strtolower(trim($answer));
    }

    // Check if answer is correct
    public function isCorrect(string $answer): ?bool
    {
        if (!$this->hasCorrectAnswer()) {
            return null; // not auto-checkable
        }

        return $this->normalizeAnswer($answer) ===
            $this->normalizeAnswer($this->correct_answer);
    }

    // Get formatted test cases (safe)
    public function getFormattedTestCases(): array
    {
        return is_array($this->test_cases) ? $this->test_cases : [];
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
}