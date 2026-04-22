<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmissionStatus extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'color'];

    public function submissions(): HasMany
    {
        return $this->hasMany(TaskSubmission::class, 'status_id');
    }
}