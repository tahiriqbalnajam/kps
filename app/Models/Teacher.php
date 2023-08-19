<?php
namespace App\Models;
use Parental\HasParent;

class Teacher extends User {

    use HasParent;

    public function attendance() {
        return $this->hasMany(TeacherAttendance::class);
    }

}
