<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['roll_no','name','user_id', 'adminssion_number', 'parent_id','class_id','section_id','session_id','dob', 
                            'doa','is_orphan','pef_admission','cast','previous_school','b_form','gender','monthly_fee','monthly_fee_discount',
                            'sibling','religion','status','action_required','action_details','nadra_pending'];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function parents(){

        return $this->belongsTo(Parents::class,'parent_id');
    }

    public function stdclasses(){

        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function section(){

        return $this->belongsTo(Section::class, 'section_id');
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
    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class, 'student_id','id');
    }
}
