<?php

namespace App\Http\Controllers;

use App\Models\StudentObservation;
use App\Models\StudentObservationDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StudentObservationController extends Controller
{
    public function index()
    {
        $observations = StudentObservation::with(['details', 'student', 'teacher'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($observations);
    }

    public function show($id)
    {
        $observation = StudentObservation::with(['details', 'student', 'teacher'])
            ->findOrFail($id);
            
        return response()->json($observation);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'observation_date' => 'required|date',
            'comments' => 'nullable|string',
            'details' => 'required|array'
        ]);

        DB::beginTransaction();
        try {
            $observation = StudentObservation::create([
                'student_id' => $request->student_id,
                'teacher_id' => Auth::id(),
                'observation_date' => $request->observation_date,
                'comments' => $request->comments
            ]);

            foreach ($request->details as $detail) {
                StudentObservationDetail::create([
                    'student_observation_id' => $observation->id,
                    'category' => $detail['category'],
                    'parameter' => $detail['parameter'],
                    'value' => $detail['value']
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Observation saved successfully', 'observation' => $observation], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to save observation', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'observation_date' => 'required|date',
            'comments' => 'nullable|string',
            'details' => 'required|array'
        ]);

        $observation = StudentObservation::findOrFail($id);

        DB::beginTransaction();
        try {
            $observation->update([
                'observation_date' => $request->observation_date,
                'comments' => $request->comments
            ]);

            // Delete existing details
            $observation->details()->delete();

            // Create new details
            foreach ($request->details as $detail) {
                StudentObservationDetail::create([
                    'student_observation_id' => $observation->id,
                    'category' => $detail['category'],
                    'parameter' => $detail['parameter'],
                    'value' => $detail['value']
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Observation updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update observation', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $observation = StudentObservation::findOrFail($id);
        $observation->delete();
        
        return response()->json(['message' => 'Observation deleted successfully']);
    }

    public function getStudentObservations($studentId)
    {
        $observations = StudentObservation::with('details')
            ->where('student_id', $studentId)
            ->orderBy('observation_date', 'desc')
            ->get();
            
        return response()->json($observations);
    }
}
