<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskType extends Model
{
    use HasFactory;

    protected $table = 'task_types';

    protected $fillable = ['name', 'slug', 'icon', 'description'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public static function listForDropdown()
    {
        return self::select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();
    }
}