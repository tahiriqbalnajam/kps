<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSalary extends Model
{
    protected $table = 'teacher_salary';
    protected $fillable = ['teacher_id','month','salary', 'present_days', 'absent_days', 'allow_leaves', 
                                'payable_days', 'daily_salary', 'estimated_salary',
                            'fine', 'bonus', 'paid', 'previous_balance', 'balance'];
    
    public function teacher(){
        return $this->belongsTo(Teacher::class,'teacher_id');
    }

}
