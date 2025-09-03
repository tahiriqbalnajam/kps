<?php

namespace App\Http\Controllers;

use App\Models\AiReport;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\TestResult;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\Http;

class AiAnalysisController extends Controller
{
    private $deepseekApiUrl = 'https://api.deepseek.com/v1';
    
    public function index()
    {
        $reports = AiReport::orderBy('created_at', 'desc')->paginate(10);
        return response()->json(new JsonResponse(['reports' => $reports]));
    }

    public function getAvailableDataSources()
    {
        $sources = [
            [
                'id' => 'students',
                'name' => 'Students',
                'fields' => ['name', 'class', 'gender', 'dob', 'admission_date']
            ],
            [
                'id' => 'teachers',
                'name' => 'Teachers',
                'fields' => ['name', 'experience', 'subjects', 'joining_date']
            ],
            [
                'id' => 'student_attendance',
                'name' => 'Student Attendance',
                'fields' => ['date', 'status', 'class']
            ],
            [
                'id' => 'teacher_attendance',
                'name' => 'Teacher Attendance',
                'fields' => ['date', 'status']
            ],
            [
                'id' => 'test_results',
                'name' => 'Test Results',
                'fields' => ['subject', 'marks', 'total_marks', 'date']
            ]
        ];

        return response()->json(new JsonResponse(['data_sources' => $sources]));
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'prompt' => 'required|string',
            'data_sources' => 'required|array',
        ]);

        // Collect data from selected sources
        $data = $this->collectData($request->data_sources);

        // Create report record
        $report = AiReport::create([
            'title' => $request->title,
            'prompt' => $request->prompt,
            'data_sources' => $request->data_sources,
            'status' => 'processing'
        ]);

        try {
            // Send to Deepseek AI
            $analysis = $this->sendToDeepseek($request->prompt, $data);

            // Update report with results
            $report->update([
                'analysis_result' => $analysis,
                'status' => 'completed',
                'metadata' => [
                    'completed_at' => now(),
                    'data_points' => count($data)
                ]
            ]);

            return response()->json(new JsonResponse([
                'message' => 'Analysis completed successfully',
                'report' => $report
            ]));

        } catch (\Exception $e) {
            $report->update([
                'status' => 'failed',
                'metadata' => [
                    'error' => $e->getMessage()
                ]
            ]);

            return response()->json(new JsonResponse([
                'error' => 'Analysis failed',
                'message' => $e->getMessage()
            ]), 500);
        }
    }

    private function collectData(array $sources)
    {
        $data = [];

        foreach ($sources as $source) {
            switch ($source) {
                case 'students':
                    $data['students'] = Student::with('stdclasses')->get();
                    break;
                case 'teachers':
                    $data['teachers'] = Teacher::with('classes')->get();
                    break;
                case 'student_attendance':
                    $data['student_attendance'] = StudentAttendance::with('students')
                        ->whereDate('created_at', '>=', now()->subMonths(3))
                        ->get();
                    break;
                case 'teacher_attendance':
                    $data['teacher_attendance'] = TeacherAttendance::with('teacher')
                        ->whereDate('created_at', '>=', now()->subMonths(3))
                        ->get();
                    break;
                case 'test_results':
                    $data['test_results'] = TestResult::with(['test', 'student'])
                        ->whereDate('created_at', '>=', now()->subMonths(6))
                        ->get();
                    break;
            }
        }

        return $data;
    }

    private function sendToDeepseek($prompt, $data)
    {
        // Implementation of Deepseek API call
        // This is a placeholder - you'll need to implement the actual API call
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.deepseek.api_key'),
            'Content-Type' => 'application/json',
        ])->post($this->deepseekApiUrl . '/analyze', [
            'prompt' => $prompt,
            'data' => $data
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to get analysis from Deepseek: ' . $response->body());
        }

        return $response->json();
    }
}
