<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model {

    
    protected $fillable = [
        'user_id','class_id','name', 'father_name','father_cnic', 'doj','education', 'experience', 'gender', 
        'pay', 'cnic', 'address', 'phone', 'profession','active'
    ];


    public function attendance() {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

}
