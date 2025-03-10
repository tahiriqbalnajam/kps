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

    /**
     * Get the sections for the class
     */
    public function sections()
    {
        return $this->hasMany(Section::class, 'class_id')->withCount([
            'students',
            'students as males_count' => function ($query) {
                $query->where('gender', 'male');
            },
            'students as females_count' => function ($query) {
                $query->where('gender', 'female');
            }
        ]);
    }
}
