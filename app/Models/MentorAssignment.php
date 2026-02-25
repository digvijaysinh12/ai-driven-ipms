<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorAssignment extends Model
{
    protected $fillable = [
        'intern_id',
        'mentor_id',
        'assigned_by',
        'is_active',
        'assigned_at'
    ];

    public function intern(){
        return $this->belongsTo(User::class,'intern_id');
    }

    public function mentor(){
        return $this->belongsTo(User::class,'mentor_id');
    }

    public function assignedBy(){
        return $this->belongsTo(User::class,'assigned_by');
    }

    public function assignedInterns()
    {
        return $this->hasMany(MentorAssignment::class, 'mentor_id')
                    ->where('is_active', true);
    }   
}
