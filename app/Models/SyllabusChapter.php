<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyllabusChapter extends Model
{
    protected $fillable = ['class_id', 'subject_id', 'title', 'order_no', 'planned_start_date', 'planned_end_date'];
    protected $casts = ['planned_start_date' => 'date', 'planned_end_date' => 'date'];

    public function topics()
    {
        return $this->hasMany(SyllabusTopic::class, 'chapter_id')->orderBy('order_no');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classModel()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
