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

        // AI evaluation scores
        'syntax_score',       // out of 10
        'logic_score',        // out of 10
        'structure_score',    // out of 10
        'ai_total_score',     // sum of above (max 30)

        // Mentor review
        'mentor_override_score', // nullable — set only when mentor reviews
        'final_score',           // mentor_override_score ?? ai_total_score

        'feedback',   // AI-generated + mentor feedback text
        'status',     // submitted | ai_evaluated | reviewed
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function intern()
    {
        return $this->belongsTo(User::class, 'intern_id');
    }

    // Resolve the effective final score
    public function getEffectiveFinalScore(): int
    {
        return $this->final_score ?? $this->mentor_override_score ?? $this->ai_total_score ?? 0;
    }
}