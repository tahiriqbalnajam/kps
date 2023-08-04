<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['roll_no','name', 'adminssion_number', 'parent_id','class_id','session_id','dob', 'b_form','gender','monthly_fee','subling','religion','status'];

    public function parents(){

        return $this->belongsTo(Parents::class,'parent_id');
    }

    public function stdclasses(){

        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function class_session(){

        return $this->belongsTo(ClassSession::class, 'session_id');
    }

    public function fee()
    {
        return $this->hasMany(Fee::class, 'student_id');
    }

    public function scopeLastpaid()
    {
        return $this->hasOne(Fee::class, 'student_id','id')->latestOfMany();
    }

    public function attend()
    {
        return $this->hasMany(StudentAttendance::class, 'student_id','id');
    }
}
