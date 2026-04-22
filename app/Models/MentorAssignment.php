<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MentorAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'intern_id',
        'mentor_id',
        'assigned_by',
        'is_active',
        'assigned_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'assigned_at' => 'datetime',
    ];

    public function intern()
    {
        return $this->belongsTo(User::class, 'intern_id')
            ->withDefault([
                'name' => 'Unknown',
                'email' => 'N/A',
            ]);
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id')
            ->withDefault([
                'name' => 'Unknown',
            ]);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Helper
    public static function countInternsForMentor($mentorId)
    {
        return self::where('mentor_id', $mentorId)
            ->where('is_active', true)
            ->count();
    }
}
