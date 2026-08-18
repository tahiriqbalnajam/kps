<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'priority', 'status'];

    protected $table = 'classes';

    /**
     * The "booted" method of the model.
     * Applies global scope to always order by priority
     */
    protected static function booted()
    {
        static::addGlobalScope('priority', function ($builder) {
            $builder->orderBy('priority', 'asc')->orderBy('id', 'asc');
        });
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_to_classes', 'class_id', 'subject_id');
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
     * Get the sections for the class.
     *
     * Note: student counts are applied where the relation is consumed
     * (see ClassesController@index) so they can be filtered by session/status.
     */
    public function sections()
    {
        return $this->hasMany(Section::class, 'class_id');
    }
}
