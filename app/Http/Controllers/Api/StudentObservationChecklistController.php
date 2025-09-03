<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StudentObservationChecklist;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class StudentObservationChecklistController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentObservationChecklist::with(['student', 'teacher']);
        
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        
        if ($request->has('observation_date')) {
            $query->whereDate('observation_date', $request->observation_date);
        }
        
        $checklists = $query->orderBy('observation_date', 'desc')->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $checklists
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'observation_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['teacher_id'] = Auth::id(); // Assuming the teacher is the logged-in user
        
        $checklist = StudentObservationChecklist::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Observation checklist created successfully',
            'data' => $checklist
        ], 201);
    }

    public function show($id)
    {
        $checklist = StudentObservationChecklist::with(['student', 'teacher'])->find($id);
        
        if (!$checklist) {
            return response()->json([
                'success' => false,
                'message' => 'Checklist not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $checklist
        ]);
    }

    public function update(Request $request, $id)
    {
        $checklist = StudentObservationChecklist::find($id);
        
        if (!$checklist) {
            return response()->json([
                'success' => false,
                'message' => 'Checklist not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'student_id' => 'exists:students,id',
            'observation_date' => 'date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $checklist->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Observation checklist updated successfully',
            'data' => $checklist
        ]);
    }

    public function destroy($id)
    {
        $checklist = StudentObservationChecklist::find($id);
        
        if (!$checklist) {
            return response()->json([
                'success' => false,
                'message' => 'Checklist not found'
            ], 404);
        }
        
        $checklist->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Observation checklist deleted successfully'
        ]);
    }

    public function getStudents()
    {
        $students = Student::all(['id', 'first_name', 'last_name', 'reg_no']);
        
        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }
}
