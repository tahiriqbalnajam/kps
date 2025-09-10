<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Timetable;
use App\Models\TimetableSlot;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Period;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Laravue\JsonResponse;

class TimetableController extends Controller
{
    public function index() {
        // Get the old timetable for backward compatibility if needed
        $timetable = Timetable::find(1);
        
        return response()->json([
            'success' => true,
            'timetable' => $timetable
        ]);
    }

    public function indexSlots() {
        // Get all timetable slots with relationships
        $slots = TimetableSlot::with(['class', 'section', 'period', 'subject', 'teacher'])
                              ->orderBy('class_id')
                              ->orderBy('section_id')
                              ->orderBy('day_of_week')
                              ->orderBy('period_id')
                              ->get();
        
        return response()->json([
            'success' => true,
            'slots' => $slots
        ]);
    }

    public function storeSlot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'period_id' => 'required|exists:periods,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Check if teacher is available
        if (!TimetableSlot::isTeacherAvailable($data['teacher_id'], $data['period_id'], $data['day_of_week'])) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher is already assigned to another class during this period.'
            ], 422);
        }

        // Check if subject is already assigned to this class/section for the day
        if (!TimetableSlot::isSubjectAvailableForClassSection($data['class_id'], $data['section_id'], $data['subject_id'], $data['day_of_week'])) {
            return response()->json([
                'success' => false,
                'message' => 'Subject is already assigned to this class/section for this day.'
            ], 422);
        }

        try {
            $slot = TimetableSlot::create($data);
            $slot->load(['class', 'section', 'period', 'subject', 'teacher']);
            
            return response()->json([
                'success' => true,
                'message' => 'Timetable slot created successfully.',
                'slot' => $slot
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating timetable slot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateSlot(Request $request, $id)
    {
        $slot = TimetableSlot::find($id);
        
        if (!$slot) {
            return response()->json([
                'success' => false,
                'message' => 'Timetable slot not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'period_id' => 'required|exists:periods,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Check if teacher is available (excluding current slot)
        if (!TimetableSlot::isTeacherAvailable($data['teacher_id'], $data['period_id'], $data['day_of_week'], $slot->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher is already assigned to another class during this period.'
            ], 422);
        }

        // Check if subject is already assigned to this class/section for the day (excluding current slot)
        if (!TimetableSlot::isSubjectAvailableForClassSection($data['class_id'], $data['section_id'], $data['subject_id'], $data['day_of_week'], $slot->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Subject is already assigned to this class/section for this day.'
            ], 422);
        }

        try {
            $slot->update($data);
            $slot->load(['class', 'section', 'period', 'subject', 'teacher']);
            
            return response()->json([
                'success' => true,
                'message' => 'Timetable slot updated successfully.',
                'slot' => $slot
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating timetable slot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroySlot($id)
    {
        $slot = TimetableSlot::find($id);
        
        if (!$slot) {
            return response()->json([
                'success' => false,
                'message' => 'Timetable slot not found.'
            ], 404);
        }

        try {
            $slot->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Timetable slot deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting timetable slot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTimetableForClassSection(Request $request)
    {
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        
        $timetable = TimetableSlot::getTimetableForClassSection($classId, $sectionId);
        
        return response()->json([
            'success' => true,
            'timetable' => $timetable
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'period_id' => 'required|exists:periods,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Check if teacher is available
        if (!TimetableSlot::isTeacherAvailable($data['teacher_id'], $data['period_id'], $data['day_of_week'])) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher is already assigned to another class during this period.'
            ], 422);
        }

        // Check if subject is already assigned to this class/section for the day
        if (!TimetableSlot::isSubjectAvailableForClassSection($data['class_id'], $data['section_id'], $data['subject_id'], $data['day_of_week'])) {
            return response()->json([
                'success' => false,
                'message' => 'Subject is already assigned to this class/section for this day.'
            ], 422);
        }

        try {
            $slot = TimetableSlot::create($data);
            $slot->load(['class', 'section', 'period', 'subject', 'teacher']);
            
            return response()->json([
                'success' => true,
                'message' => 'Timetable slot created successfully.',
                'slot' => $slot
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating timetable slot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if ($id == '1') {
            // Handle old timetable update for backward compatibility
            $timetable = Timetable::find(1);
            
            if ($timetable) {
                $timetable->update($request->all());
                return response()->json(['timetable' => $timetable]);
            } else {
                return response()->json(['error' => 'No timetable record found'], 404);
            }
        }

        // Handle new timetable slot update
        $slot = TimetableSlot::find($id);
        
        if (!$slot) {
            return response()->json([
                'success' => false,
                'message' => 'Timetable slot not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'period_id' => 'required|exists:periods,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Check if teacher is available (excluding current slot)
        if (!TimetableSlot::isTeacherAvailable($data['teacher_id'], $data['period_id'], $data['day_of_week'], $slot->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher is already assigned to another class during this period.'
            ], 422);
        }

        // Check if subject is already assigned to this class/section for the day (excluding current slot)
        if (!TimetableSlot::isSubjectAvailableForClassSection($data['class_id'], $data['section_id'], $data['subject_id'], $data['day_of_week'], $slot->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Subject is already assigned to this class/section for this day.'
            ], 422);
        }

        try {
            $slot->update($data);
            $slot->load(['class', 'section', 'period', 'subject', 'teacher']);
            
            return response()->json([
                'success' => true,
                'message' => 'Timetable slot updated successfully.',
                'slot' => $slot
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating timetable slot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $slot = TimetableSlot::find($id);
        
        if (!$slot) {
            return response()->json([
                'success' => false,
                'message' => 'Timetable slot not found.'
            ], 404);
        }

        try {
            $slot->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Timetable slot deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting timetable slot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAvailableTeachers(Request $request)
    {
        $periodId = $request->input('period_id');
        $dayOfWeek = $request->input('day_of_week');
        $excludeSlotId = $request->input('exclude_slot_id');

        $occupiedTeacherIds = TimetableSlot::where('period_id', $periodId)
                                         ->where('day_of_week', $dayOfWeek);
        
        if ($excludeSlotId) {
            $occupiedTeacherIds->where('id', '!=', $excludeSlotId);
        }
        
        $occupiedTeacherIds = $occupiedTeacherIds->pluck('teacher_id')->toArray();

        $availableTeachers = Teacher::whereNotIn('id', $occupiedTeacherIds)
                                  ->where('active', 1)
                                  ->get();

        return response()->json([
            'success' => true,
            'teachers' => $availableTeachers,
            'occupied_teacher_ids' => $occupiedTeacherIds
        ]);
    }

    public function getAvailableSubjects(Request $request)
    {
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $dayOfWeek = $request->input('day_of_week');
        $excludeSlotId = $request->input('exclude_slot_id');

        $query = TimetableSlot::where('class_id', $classId)
                             ->where('day_of_week', $dayOfWeek);
        
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        } else {
            $query->whereNull('section_id');
        }
        
        if ($excludeSlotId) {
            $query->where('id', '!=', $excludeSlotId);
        }
        
        $occupiedSubjectIds = $query->pluck('subject_id')->toArray();

        $availableSubjects = Subject::whereNotIn('id', $occupiedSubjectIds)->get();

        return response()->json([
            'success' => true,
            'subjects' => $availableSubjects,
            'occupied_subject_ids' => $occupiedSubjectIds
        ]);
    }

    public function getFullTimetable()
    {
        $classes = Classes::with('sections')->get();
        $periods = Period::orderBy('id')->get();
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        $timetableData = [];
        
        foreach ($classes as $class) {
            if ($class->sections->count() > 0) {
                // Class has sections
                foreach ($class->sections as $section) {
                    $classSection = [
                        'id' => "section-{$section->id}",
                        'type' => 'section',
                        'class_id' => $class->id,
                        'section_id' => $section->id,
                        'display_name' => "{$class->name} - {$section->name}",
                        'timetable' => []
                    ];
                    
                    foreach ($days as $day) {
                        $classSection['timetable'][$day] = [];
                        foreach ($periods as $period) {
                            $slot = TimetableSlot::with(['subject', 'teacher'])
                                                ->where('class_id', $class->id)
                                                ->where('section_id', $section->id)
                                                ->where('period_id', $period->id)
                                                ->where('day_of_week', $day)
                                                ->first();
                            
                            $classSection['timetable'][$day][$period->id] = $slot;
                        }
                    }
                    
                    $timetableData[] = $classSection;
                }
            } else {
                // Class without sections
                $classData = [
                    'id' => "class-{$class->id}",
                    'type' => 'class',
                    'class_id' => $class->id,
                    'section_id' => null,
                    'display_name' => $class->name,
                    'timetable' => []
                ];
                
                foreach ($days as $day) {
                    $classData['timetable'][$day] = [];
                    foreach ($periods as $period) {
                        $slot = TimetableSlot::with(['subject', 'teacher'])
                                            ->where('class_id', $class->id)
                                            ->whereNull('section_id')
                                            ->where('period_id', $period->id)
                                            ->where('day_of_week', $day)
                                            ->first();
                        
                        $classData['timetable'][$day][$period->id] = $slot;
                    }
                }
                
                $timetableData[] = $classData;
            }
        }
        
        return response()->json([
            'success' => true,
            'timetable' => $timetableData,
            'periods' => $periods,
            'days' => $days
        ]);
    }
}
