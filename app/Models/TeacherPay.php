<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherPay extends Model
{
    use HasFactory;

    protected $table = 'teacher_pay';
    
    protected $fillable = [
        'user_id',
        'estimated_pay',
        'month',
    ];

    /**
     * Get the user that owns the teacher pay.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}