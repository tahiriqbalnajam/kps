<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class examStudentResultSubject extends Model
{
    use HasFactory;

    protected $table = 'exam_student_result_subject';

    protected $fillable = ['student_id', 'subject_id', 'total_marks', 'obtained_marks'];
}

