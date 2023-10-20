<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    public $timestamps = false;
    protected $fillable = ['student_id', 'class_id', 'total_marks', 'obtained_marks', 'exam_id','subject_id'];
    protected $table = "exam_result_students";

    public function GetExams()
    {
        return $this->belongsToMany(Subject::class, 'student_id', 'class_id', 'total_marks', 'obtained_marks','subject_id');
    }

    public function student(){
        return $this->belongsTo(student::class, 'student_id');
    }

    public function exams(){
        return $this->belongsTo(AddExam::class,'exam_id');
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    
}
