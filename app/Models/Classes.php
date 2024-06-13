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

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function getMaleCountAttribute()
    {
        return $this->students()->where('gender', 'male')->count();
    }

    public function getFemaleCountAttribute()
    {
        return $this->students()->where('gender', 'female')->count();
    }
}
