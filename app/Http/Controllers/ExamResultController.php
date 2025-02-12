<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExamResultController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'results' => 'required|array',
            'results.*.exam_subject_id' => 'required|exists:exam_subjects,id',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.obtained_marks' => 'required|integer',
        ]);

        foreach ($request->results as $result) {
            ExamResult::updateOrCreate(
                [
                    'exam_id' => $request->exam_id,
                    'exam_subject_id' => $result['exam_subject_id'],
                    'student_id' => $result['student_id'],
                ],
                [
                    'obtained_marks' => $result['obtained_marks'],
                ]
            );
        }

        return response()->json(['message' => 'Results stored successfully'], 201);
    }
}
