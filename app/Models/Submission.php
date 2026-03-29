<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'question_id',
        'intern_id',
        'submitted_code',
        'syntax_score',
        'logic_score',
        'structure_score',
        'ai_total_score',
        'mentor_override_score',
        'final_score',
        'feedback',   // ← was missing!
        'status',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function intern()
    {
        return $this->belongsTo(User::class, 'intern_id');
    }

    /**
     * Resolve the effective final score
     */
    public function getEffectiveFinalScore(): int
    {
        return $this->final_score
            ?? $this->mentor_override_score
            ?? $this->ai_total_score
            ?? 0;
    }
}