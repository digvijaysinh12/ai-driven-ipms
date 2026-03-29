<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternTopicAssignment extends Model
{
    use SoftDeletes;
<<<<<<< HEAD

=======
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    protected $fillable = [
        'intern_id',
        'topic_id',
        'assigned_by',
        'deadline',
        'status',
<<<<<<< HEAD
        'grade',
        'feedback',
=======
        'grade',       // A / B / C / D / E — set by AI after exercise submission
        'feedback',    // overall AI feedback for the whole exercise
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        'assigned_at',
        'submitted_at',
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'submitted_at' => 'datetime',
        'deadline'     => 'date',
    ];

<<<<<<< HEAD
    // ── Relationships ──────────────────────────────────────────────

=======
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
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

<<<<<<< HEAD
    // ── Helpers ────────────────────────────────────────────────────

    public function getGradeLabelAttribute(): string
    {
        return match ($this->grade) {
            'A'     => 'Excellent',
            'B'     => 'Good',
            'C'     => 'Average',
            'D'     => 'Below Average',
            'E'     => 'Needs Improvement',
=======
    // ── Helper: grade label ──────────────────────────────────────
    public function getGradeLabelAttribute(): string
    {
        return match($this->grade) {
            'A' => 'Excellent',
            'B' => 'Good',
            'C' => 'Average',
            'D' => 'Below Average',
            'E' => 'Needs Improvement',
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
            default => 'Not graded',
        };
    }

<<<<<<< HEAD
=======
    // ── Helper: is exercise locked (submitted or evaluated) ──────
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    public function isLocked(): bool
    {
        return in_array($this->status, ['submitted', 'evaluated']);
    }
}