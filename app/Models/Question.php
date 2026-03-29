<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'topic_id',
        'language',
        'type',
        'problem_statement',
        'code',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function referenceSolution()
    {
        return $this->hasOne(ReferenceSolution::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Get the option text for a given letter key (A/B/C/D)
     */
    public function getOptionText(string $key): ?string
    {
        return match (strtoupper($key)) {
            'A' => $this->option_a,
            'B' => $this->option_b,
            'C' => $this->option_c,
            'D' => $this->option_d,
            default => null,
        };
    }
}