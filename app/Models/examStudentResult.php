<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddExam extends Model
{
    protected $table = 'exam_result_students';
    protected $fillable = ['examname','student_id','class_id','total_marks','obtained_marks'];
}
