<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'class_id'];
    public $timestamps = false;
    
    /**
     * Get the class that owns the section
     */
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
    
    /**
     * Get the students for the section
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'section_id');
    }
}
