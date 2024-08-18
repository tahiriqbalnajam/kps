<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'title',
        'date',
        'total_marks',
    ];

    // Relationships
    public function class()
    {
        return $this->belongsTo(Classes::class)->select('id', 'name');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class)->select('id', 'title');
    }

    public function testResults()
    {
        return $this->hasMany(TestResult::class);
    }
}
