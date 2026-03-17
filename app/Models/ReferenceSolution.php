<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceSolution extends Model
{
    protected $fillable = [
        'question_id',
        'solution_code',
        'explanation',
        'created_by'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}