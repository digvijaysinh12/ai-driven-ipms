<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
    use SoftDeletes;
<<<<<<< HEAD

=======
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    protected $fillable = [
        'question_id',
        'intern_id',
        'submitted_code',
<<<<<<< HEAD
        'syntax_score',
        'logic_score',
        'structure_score',
        'ai_total_score',
        'mentor_override_score',
        'final_score',
        'feedback',   // ← was missing!
        'status',
=======

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
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function intern()
    {
        return $this->belongsTo(User::class, 'intern_id');
    }

<<<<<<< HEAD
    /**
     * Resolve the effective final score
     */
    public function getEffectiveFinalScore(): int
    {
        return $this->final_score
            ?? $this->mentor_override_score
            ?? $this->ai_total_score
            ?? 0;
=======
    // Resolve the effective final score
    public function getEffectiveFinalScore(): int
    {
        return $this->final_score ?? $this->mentor_override_score ?? $this->ai_total_score ?? 0;
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    }
}