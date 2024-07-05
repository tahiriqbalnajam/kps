<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $fillable = [
        'test_id',
        'student_id',
        'score',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->select('id', 'name', 'roll_no', 'class_id', 'parent_id');
    }
}
