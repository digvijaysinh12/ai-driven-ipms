<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;
<<<<<<< HEAD

=======
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    protected $fillable = [
        'topic_id',
        'language',
        'type',
        'problem_statement',
        'code',
<<<<<<< HEAD
=======

        // MCQ options (only populated for type = 'mcq')
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
        'option_a',
        'option_b',
        'option_c',
        'option_d',
<<<<<<< HEAD
=======

        // Correct answer:
        //   mcq        => "A" | "B" | "C" | "D"
        //   true_false => "True" | "False"
        //   blank      => expected fill word/phrase
        //   output     => exact expected output
        //   coding     => null (AI evaluates freely)
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
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

<<<<<<< HEAD
    /**
     * Get the option text for a given letter key (A/B/C/D)
     */
=======
    // Helper: get the option text for a given letter key (A/B/C/D)
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
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