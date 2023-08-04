<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    public $timestamps = false;
    protected $fillable = ['name'];
    protected $table = "classes";

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_to_classes','class_id', 'subject_id');
    }
}
