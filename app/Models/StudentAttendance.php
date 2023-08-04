<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    protected $fillable = ['class_id','student_id','status'];

    public function students(){

        return $this->belongsTo(Student::class,'student_id');
    }
}
