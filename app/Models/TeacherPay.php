<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherPay extends Model
{
    protected $table = 'teacher_pay';
    protected $fillable = ['estimated_pay','month', 'user_id'];
}
