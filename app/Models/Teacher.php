<?php
namespace App\Models;
use Parental\HasParent;

class Teacher extends User {

    use HasParent;
    
    protected $fillable = [
        'type','name', 'email','sex','birthday', 'password', 'status', 'gender', 'dob', 'education', 'cnic', 'pay','address', 'phone', 'profession' 
    ];
    public function attendance() {
        return $this->hasMany(TeacherAttendance::class);
    }

}
