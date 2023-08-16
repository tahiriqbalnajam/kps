<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ExamResult;

class Exam extends Model
{
    protected $table = 'exams';
    protected $fillable = ['examname','class_id', 'total_marks'];

    public function results()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function classes() {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
