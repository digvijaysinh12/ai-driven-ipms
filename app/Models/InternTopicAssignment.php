<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternTopicAssignment extends Model
{
    protected $fillable = [
        'intern_id',
        'topic_id',
        'assigned_by',
        'deadline',
        'status',
        'grade',       // A / B / C / D / E — set by AI after exercise submission
        'feedback',    // overall AI feedback for the whole exercise
        'assigned_at',
        'submitted_at',
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'submitted_at' => 'datetime',
        'deadline'     => 'date',
    ];

    public function intern()
    {
        return $this->belongsTo(User::class, 'intern_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // ── Helper: grade label ──────────────────────────────────────
    public function getGradeLabelAttribute(): string
    {
        return match($this->grade) {
            'A' => 'Excellent',
            'B' => 'Good',
            'C' => 'Average',
            'D' => 'Below Average',
            'E' => 'Needs Improvement',
            default => 'Not graded',
        };
    }

    // ── Helper: is exercise locked (submitted or evaluated) ──────
    public function isLocked(): bool
    {
        return in_array($this->status, ['submitted', 'evaluated']);
    }
}