<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceSolution extends Model
{
    protected $fillable = [
        'question_id',
        'solution_code',
        'explanation',
<<<<<<< HEAD
        'created_by',
=======
        'created_by'
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}