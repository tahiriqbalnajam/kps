<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimetableSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'section_id',
        'period_id',
        'subject_id',
        'teacher_id',
        'day_of_week'
    ];

    /**
     * Get the class that owns the timetable slot
     */
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Get the section that owns the timetable slot
     */
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Get the period that owns the timetable slot
     */
    public function period()
    {
        return $this->belongsTo(Period::class, 'period_id');
    }

    /**
     * Get the subject that owns the timetable slot
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Get the teacher that owns the timetable slot
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * Scope to get slots for a specific class
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope to get slots for a specific section
     */
    public function scopeForSection($query, $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    /**
     * Scope to get slots for a specific day
     */
    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * Check if a teacher is available for a specific period and day
     */
    public static function isTeacherAvailable($teacherId, $periodId, $dayOfWeek, $excludeSlotId = null)
    {
        $query = self::where('teacher_id', $teacherId)
                    ->where('period_id', $periodId)
                    ->where('day_of_week', $dayOfWeek);
        
        if ($excludeSlotId) {
            $query->where('id', '!=', $excludeSlotId);
        }
        
        return $query->count() === 0;
    }

    /**
     * Check if a subject is already assigned to a class/section for the day
     */
    public static function isSubjectAvailableForClassSection($classId, $sectionId, $subjectId, $dayOfWeek, $excludeSlotId = null)
    {
        $query = self::where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->where('day_of_week', $dayOfWeek);
        
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        } else {
            $query->whereNull('section_id');
        }
        
        if ($excludeSlotId) {
            $query->where('id', '!=', $excludeSlotId);
        }
        
        return $query->count() === 0;
    }

    /**
     * Get timetable for a specific class/section
     */
    public static function getTimetableForClassSection($classId, $sectionId = null)
    {
        $query = self::with(['period', 'subject', 'teacher'])
                    ->where('class_id', $classId);
        
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        } else {
            $query->whereNull('section_id');
        }
        
        return $query->orderBy('day_of_week')
                    ->orderBy('period_id')
                    ->get()
                    ->groupBy('day_of_week');
    }
}
