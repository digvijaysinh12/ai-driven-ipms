<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;
    public const STATUS_DRAFT = 'draft';
    public const STATUS_READY = 'ready';
    public const STATUS_ASSIGNED = 'assigned';

    protected $fillable = [
        'title',
        'description',
        'task_type_id',
        'created_by',
        'difficulty',
        'language',
        'status',
    ];

    protected $appends = ['type_name'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }

    /**
     * The users (interns) assigned to this task.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot(['assigned_at', 'due_at', 'is_notified'])
            ->withTimestamps();
    }

    /**
     * Alias for users() relationship.
     */
    public function interns(): BelongsToMany
    {
        return $this->users();
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TaskQuestion::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TaskSubmission::class);
    }

    public function getTypeNameAttribute(): string
    {
        return $this->type?->name ?? 'Unknown';
    }
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function isAssigned(): bool
    {
        return $this->status === self::STATUS_ASSIGNED;
    }


}
