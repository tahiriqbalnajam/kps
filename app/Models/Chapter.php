<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['class_id', 'subject_id', 'title'];

    public function subject()
    {
        return $this->belongsTo(Subject::class)->select('id', 'title');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class)->select('id', 'name');
    }


}
