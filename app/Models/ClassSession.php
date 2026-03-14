<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    protected $table = 'class_sessions';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // Students enrolled in this session
    public function students()
    {
        return $this->hasMany(Student::class, 'session_id');
    }

    // Scope: only the active session
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper: get the single active session
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }
}
