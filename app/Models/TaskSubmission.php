<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskSubmission extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'task_id',
        'user_id',
        'status_id',

        // AI Result
        'percentage',
        'ai_feedback',

        // Mentor Review
        'final_percentage',
        'final_feedback',
        'reviewed_at',
        'reviewed_by',

        // Meta
        'submitted_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'percentage' => 'float',
        'final_percentage' => 'float',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Appends (Virtual Fields)
    |--------------------------------------------------------------------------
    */
    protected $appends = [
        'result',
        'grade',
        'status_name',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Task
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    // Intern (user)
    public function intern(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Answers
    public function answers(): HasMany
    {
        return $this->hasMany(SubmissionAnswer::class);
    }

    // Status
    public function status(): BelongsTo
    {
        return $this->belongsTo(SubmissionStatus::class, 'status_id');
    }

    // Reviewer (mentor)
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    // Final result (mentor override > AI)
    public function getResultAttribute(): ?float
    {
        return $this->final_percentage ?? $this->percentage;
    }

    // Grade (based on final result)
    public function getGradeAttribute(): ?string
    {
        $p = $this->result;

        if ($p === null) return null;

        if ($p >= 90) return 'A';
        if ($p >= 75) return 'B';
        if ($p >= 50) return 'C';
        return 'D';
    }

    // Status name (easy access)
    public function getStatusNameAttribute(): string
    {
        return $this->status?->name ?? 'Unknown';
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    // Check if reviewed by mentor
    public function isReviewed(): bool
    {
        return !is_null($this->reviewed_at);
    }

    // Check if AI evaluated
    public function isEvaluated(): bool
    {
        return !is_null($this->percentage);
    }

    // Check if submission is active (not yet completed by mentor)
    public function isActive(): bool
    {
        return $this->status?->slug !== 'completed';
    }

    // Check if submission is finalized/completed
    public function isCompleted(): bool
    {
        return $this->status?->slug === 'completed';
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope for pending submissions (Waiting for mentor review)
     */
    public function scopePending($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->whereIn('slug', ['submitted', 'ai_evaluated']);
        });
    }

    /**
     * Scope for reviewed submissions
     */
    public function scopeReviewed($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->where('slug', 'completed');
        });
    }
}