<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentObservationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_observation_id',
        'category',
        'parameter',
        'value'
    ];

    public function observation()
    {
        return $this->belongsTo(StudentObservation::class, 'student_observation_id');
    }
}
