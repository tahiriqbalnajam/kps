<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    protected $fillable = ['class_id','student_id','status','attendance_date','date','comment'];

    public function students(){

        return $this->belongsTo(Student::class,'student_id');
    }

    public function classes(){
        return $this->belongsTo(Classes::class,'class_id');
    }
}
