<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolDiary extends Model
{
    protected $table = 'school_diaries';

    protected $fillable = [
        'class_id',
        'section_id',   // 0 = no section (class has no sections assigned)
        'subject_id',
        'diary_text',
        'diary_date',
        'created_by',
    ];

    protected $casts = [
        'diary_date' => 'date:Y-m-d',
    ];

    public function stdclass()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
