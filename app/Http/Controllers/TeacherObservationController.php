<?php

namespace App\Http\Controllers;

use App\Models\TeacherObservation;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;

class TeacherObservationController extends Controller
{
    public function index(Request $request)
    {
        $observations = TeacherObservation::with('teacher')
            ->when($request->teacher_id, function($query) use ($request) {
                return $query->where('teacher_id', $request->teacher_id);
            })
            ->when($request->date_from, function($query) use ($request) {
                return $query->whereDate('observation_date', '>=', $request->date_from);
            })
            ->when($request->date_to, function($query) use ($request) {
                return $query->whereDate('observation_date', '<=', $request->date_to);
            })
            ->orderBy('observation_date', 'desc')
            ->paginate($request->get('limit', 10));

        return response()->json(new JsonResponse(['observations' => $observations]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'attentiveness_score' => 'required|integer|min:1|max:5',
            'syllabus_progress_score' => 'required|integer|min:1|max:5',
            'tools_usage_score' => 'required|integer|min:1|max:5',
            'homework_check_score' => 'required|integer|min:1|max:5',
            'supervisor_comments' => 'required|string',
            'observation_date' => 'required|date'
        ]);

        $observation = TeacherObservation::create($request->all());

        return response()->json(new JsonResponse([
            'observation' => $observation,
            'message' => 'Observation report created successfully'
        ]));
    }

    public function show($id)
    {
        $observation = TeacherObservation::with('teacher')->findOrFail($id);
        return response()->json(new JsonResponse(['observation' => $observation]));
    }

    public function getTeacherProgress($teacherId)
    {
        $year = request('year', date('Y'));
        $months = [];
        
        // Get monthly observation scores
        $observations = TeacherObservation::where('teacher_id', $teacherId)
            ->whereYear('observation_date', $year)
            ->selectRaw('MONTH(observation_date) as month,
                AVG(attentiveness_score + syllabus_progress_score + tools_usage_score + homework_check_score)/4 as avg_score')
            ->groupBy('month')
            ->get()
            ->pluck('avg_score', 'month')
            ->toArray();

        // Get monthly test scores
        $testScores = DB::table('tests')
            ->join('test_results', 'tests.id', '=', 'test_results.test_id')
            ->whereYear('tests.date', $year)
            ->where('tests.teacher_id', $teacherId)
            ->selectRaw('MONTH(tests.date) as month, AVG(test_results.score) as avg_marks')
            ->groupBy('month')
            ->get()
            ->pluck('avg_marks', 'month')
            ->toArray();

        // Combine data for all 12 months
        for ($i = 1; $i <= 12; $i++) {
            $months[] = [
                'month' => date('F', mktime(0, 0, 0, $i, 1)),
                'observation_score' => $observations[$i] ?? 0,
                'test_score' => $testScores[$i] ?? 0
            ];
        }

        return response()->json(new JsonResponse(['progress' => $months]));
    }
}
