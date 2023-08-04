<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsQueue extends Model
{
    protected $fillable = ['id', 'message', 'phone', 'status'];
    public function student(){
        return $this->belongsTo(Student::class,'student_id');
    }
}
