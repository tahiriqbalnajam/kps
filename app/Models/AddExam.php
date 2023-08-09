<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddExam extends Model
{
    protected $table = 'exams';
    protected $fillable = ['examname','class_id', 'total_marks'];
}
