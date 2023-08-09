<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddExamsReults extends Model
{
    public $timestamps = false;
    protected $fillable = ['student_id', 'class_id', 'total_marks', 'obtained_marks'];
    protected $table = "exam_result_students";

    public function GetExams()
    {
        return $this->belongsToMany(Subject::class, 'student_id', 'class_id', 'total_marks', 'obtained_marks');
    }
}
