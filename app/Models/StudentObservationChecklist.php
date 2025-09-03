<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentObservationChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'observation_date',
        'english_reading',
        'english_writing',
        'english_spelling',
        'urdu_reading',
        'urdu_writing',
        'urdu_spelling',
        'math_numbers',
        'math_concepts',
        'respect_adults',
        'respect_classmates',
        'engages_with_classmates',
        'follows_instructions',
        'conflict_resolution',
        'uniform_clean',
        'nails_clean',
        'shoes_polished',
        'personal_hygiene',
        'good_manners',
        'school_concern',
        'activity_participation',
        'punctuality',
        'task_completion',
        'additional_comments'
    ];

    protected $casts = [
        'observation_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
