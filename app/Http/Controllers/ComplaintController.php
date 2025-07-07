<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Complaint;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    const ITEM_PER_PAGE = 15;

    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = $searchParams['limit'] ?? static::ITEM_PER_PAGE;
        $keyword = $searchParams['keyword'] ?? '';

        $complaintsQuery = Complaint::with(['student', 'teacher', 'createdBy', 'assignedTo'])
            ->when($keyword, function ($query, $keyword) {
                return $query->where('title', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%")
                    ->orWhereHas('student', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', "%{$keyword}%");
                    });
            })
            ->when(isset($searchParams['status']), function ($query) use ($searchParams) {
                return $query->where('status', $searchParams['status']);
            })
            ->when(isset($searchParams['student_id']), function ($query) use ($searchParams) {
                return $query->where('student_id', $searchParams['student_id']);
            })
            ->when(isset($searchParams['teacher_id']), function ($query) use ($searchParams) {
                return $query->where('teacher_id', $searchParams['teacher_id']);
            })
            ->orderBy('created_at', 'desc');

        $complaints = $complaintsQuery->paginate($limit);
        
        return response()->json(new JsonResponse(['complaints' => $complaints]));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'complaint_type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $complaint = Complaint::create([
            'student_id' => $request->student_id,
            'teacher_id' => $request->teacher_id,
            'complaint_type' => $request->complaint_type,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'pending',
            'priority' => $request->priority,
            'created_by' => Auth::id(),
            'assigned_to' => $request->assigned_to,
        ]);

        $complaint->load(['student', 'teacher', 'createdBy', 'assignedTo']);

        return response()->json(new JsonResponse(['complaint' => $complaint]));
    }

    public function show(Complaint $complaint)
    {
        $complaint->load(['student', 'teacher', 'createdBy', 'assignedTo']);
        return response()->json(new JsonResponse(['complaint' => $complaint]));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'sometimes|exists:students,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'complaint_type' => 'sometimes|string',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'sometimes|in:pending,in_progress,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only([
            'student_id', 'teacher_id', 'complaint_type', 'title', 
            'description', 'status', 'priority', 'assigned_to', 'resolution_notes'
        ]);

        if ($request->status === 'resolved' && $complaint->status !== 'resolved') {
            $updateData['resolved_at'] = now();
        }

        $complaint->update($updateData);
        $complaint->load(['student', 'teacher', 'createdBy', 'assignedTo']);

        return response()->json(new JsonResponse(['complaint' => $complaint]));
    }

    public function destroy(Complaint $complaint)
    {
        $complaint->delete();
        return response()->json(new JsonResponse(['message' => 'Complaint deleted successfully']));
    }

    public function getStudents(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $students = Student::when($keyword, function ($query, $keyword) {
            return $query->where('name', 'LIKE', "%{$keyword}%");
        })->limit(20)->get(['id', 'name']);

        return response()->json(new JsonResponse(['students' => $students]));
    }

    public function getTeachers(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $teachers = Teacher::where('status', 'active')
            ->when($keyword, function ($query, $keyword) {
                return $query->where('name', 'LIKE', "%{$keyword}%");
            })->limit(20)->get(['id', 'name']);

        return response()->json(new JsonResponse(['teachers' => $teachers]));
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = ['status' => $request->status];
        
        if ($request->resolution_notes) {
            $updateData['resolution_notes'] = $request->resolution_notes;
        }

        if ($request->status === 'resolved' && $complaint->status !== 'resolved') {
            $updateData['resolved_at'] = now();
        }

        $complaint->update($updateData);
        $complaint->load(['student', 'teacher', 'createdBy', 'assignedTo']);

        return response()->json(new JsonResponse(['complaint' => $complaint]));
    }
}
