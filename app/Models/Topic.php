<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mentor_id',
        'title',
        'description',
        'status',
        'mcq_count',
        'blank_count',
        'true_false_count',
        'output_count',
        'coding_count',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function internAssignments()
    {
        return $this->hasMany(InternTopicAssignment::class);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // ── Helpers ────────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function totalQuestionCount(): int
    {
        return $this->mcq_count
             + $this->blank_count
             + $this->true_false_count
             + $this->output_count
             + $this->coding_count;
    }
}