<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherObservation extends Model
{
    protected $fillable = [
        'teacher_id',
        'attentiveness_score',
        'syllabus_progress_score',
        'tools_usage_score',
        'homework_check_score',
        'supervisor_comments',
        'observation_date'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
