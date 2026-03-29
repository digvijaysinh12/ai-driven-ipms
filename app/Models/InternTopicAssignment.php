<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternTopicAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'intern_id',
        'topic_id',
        'assigned_by',
        'deadline',
        'status',
        'grade',
        'feedback',
        'assigned_at',
        'submitted_at',
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'submitted_at' => 'datetime',
        'deadline'     => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────

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

    // ── Helpers ────────────────────────────────────────────────────

    public function getGradeLabelAttribute(): string
    {
        return match ($this->grade) {
            'A'     => 'Excellent',
            'B'     => 'Good',
            'C'     => 'Average',
            'D'     => 'Below Average',
            'E'     => 'Needs Improvement',
            default => 'Not graded',
        };
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['submitted', 'evaluated']);
    }
}