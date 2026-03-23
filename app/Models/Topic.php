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
'coding_count'
];

public function mentor()
{
return $this->belongsTo(User::class,'mentor_id');
}

public function questions()
{
return $this->hasMany(Question::class);
}

}