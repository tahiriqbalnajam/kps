<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyllabusTopic extends Model
{
    protected $fillable = ['chapter_id', 'title', 'order_no', 'completed', 'completed_date'];
    protected $casts = ['completed' => 'boolean', 'completed_date' => 'date'];

    public function chapter()
    {
        return $this->belongsTo(SyllabusChapter::class);
    }
}
