<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LowPerformanceStudentController extends Controller
{
    public function index(Request $request)
    {
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id');
        $threshold = (float) $request->query('threshold', 50);

        // Single optimized query: aggregate at DB level, 
        // only select the columns we need, guard against division-by-zero
        $query = DB::table('test_results')
            ->join('tests', 'test_results.test_id', '=', 'tests.id')
            ->join('students', 'test_results.student_id', '=', 'students.id')
            ->join('classes', 'students.class_id', '=', 'classes.id')
            ->join('subjects', 'tests.subject_id', '=', 'subjects.id')
            ->select(
                'students.id as student_id',
                'students.name as student_name',
                'students.roll_no',
                'classes.id as class_id',
                'classes.name as class_name',
                'subjects.id as subject_id',
                'subjects.title as subject_name',
                DB::raw('ROUND(AVG(CASE WHEN tests.total_marks > 0 THEN (test_results.score / tests.total_marks) * 100 ELSE 0 END), 2) as average_score')
            )
            ->whereNotNull('test_results.score')
            ->where('students.status', 'enable')         // only active students
            ->where('tests.total_marks', '>', 0)          // skip tests with 0 total marks
            ->groupBy('students.id', 'students.name', 'students.roll_no', 'classes.id', 'classes.name', 'subjects.id', 'subjects.title')
            ->havingRaw('ROUND(AVG(CASE WHEN tests.total_marks > 0 THEN (test_results.score / tests.total_marks) * 100 ELSE 0 END), 2) < ?', [$threshold])
            ->orderBy('classes.name')
            ->orderBy('students.roll_no');

        // Apply filters early (before aggregation) so MySQL processes fewer rows
        if ($classId) {
            $query->where('students.class_id', $classId);
        }

        if ($sectionId) {
            $query->where('students.section_id', $sectionId);
        }

        $results = $query->get();

        // Single-pass grouping in PHP: group by class, then by student
        $grouped = [];
        foreach ($results as $row) {
            $className = $row->class_name;
            $studentId = $row->student_id;

            if (!isset($grouped[$className])) {
                $grouped[$className] = [];
            }

            if (!isset($grouped[$className][$studentId])) {
                $grouped[$className][$studentId] = [
                    'student_id'   => $row->student_id,
                    'student_name' => $row->student_name,
                    'class_name'   => $row->class_name,
                    'subjects'     => [],
                ];
            }

            $grouped[$className][$studentId]['subjects'][] = [
                'subject_name'  => $row->subject_name,
                'average_score' => (float) $row->average_score,
            ];
        }

        // Convert associative arrays to indexed arrays for JSON
        $output = [];
        foreach ($grouped as $className => $students) {
            $output[$className] = array_values($students);
        }

        return response()->json([
            'success' => true,
            'data'    => $output,
        ]);
    }
}
