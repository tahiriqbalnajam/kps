<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ExamResult;

class Exam extends Model
{
    protected $table = 'exams';
    
    protected $fillable = ['title', 'class_id', 'section_id', 'skip'];

    public function examSubjects()
    {
        return $this->hasMany(ExamSubject::class, 'exam_id');
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class, 'exam_id');
    }

    public function classes() {
        return $this->belongsTo(Classes::class, 'class_id');
    }
    
    public function section() {
        return $this->belongsTo(Section::class, 'section_id');
    }
    
}
