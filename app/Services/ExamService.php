<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Student;
use App\Models\ExamResult;
use App\Models\ExamSubject;
use Illuminate\Support\Arr;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\Contracts\ExamServiceInterface;

class ExamService implements ExamServiceInterface
{
    const ITEM_PER_PAGE = 1000;
    public function listExams(array $searchParams)
    {
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $query = QueryBuilder::for(Exam::class)
            ->allowedFields(['id', 'title', 'class_id', 'classes.name'])
            ->with('classes')
            ->allowedFilters([
                'id', 'title', 'class_id', 'created_at',
                AllowedFilter::exact('class_id'),
            ]);

        return $query->paginate($limit)
            ->appends(request()->query());
    }

    public function storeExam(array $data)
    {
        try {
            DB::beginTransaction();
    
            $exam = Exam::create([
                'title' => $data['title'],
                'class_id' => $data['class_id'],
            ]);
    
            foreach ($data['subjects'] as $subject) {
                ExamSubject::create([
                    'exam_id' => $exam->id,
                    'subject_id' => $subject['subject_id'],
                    'total_marks' => $subject['total_marks'],
                    'skip' => $subject['skip_in_report'],
                ]);
            }
    
            DB::commit();
            return response()->json(new JsonResponse(['exam' => $exam]));
        } catch (\Exception $ex) {
            DB::rollBack();
            return responseFailed($ex->getMessage());
        }
    }

    public function getExamSubjects($examId)
    {
        return ExamSubject::where('exam_id', $examId)
            ->where('skip', false)
            ->with('subject')
            ->get();
    }

    public function addExamMarks(array $data)
    {
        try {
            DB::beginTransaction();
    
            foreach ($data['marks'] as $studentId => $subjects) {
                foreach ($subjects as $subjectId => $obtained_marks) {
                    ExamResult::updateOrCreate(
                        [
                            'exam_id' => $data['exam_id'], 
                            'student_id' => $studentId, 
                            'exam_subject_id' => $subjectId
                        ],
                        [
                            'obtained_marks' => $obtained_marks
                        ]
                    );
                }
            }
    
            DB::commit();
            return response()->json(new JsonResponse(['message' => 'Marks updated successfully']));
        } catch (\Exception $ex) {
            DB::rollBack();
            return responseFailed($ex->getMessage());
        }
    }

    public function updateExamMarks(array $data)
    {
        try {
            DB::beginTransaction();
    
            foreach ($data['marks'] as $studentId => $subjects) {
                foreach ($subjects as $subjectId => $marks) {
                    ExamResult::updateOrCreate(
                        ['exam_id' => $data['exam_id'], 'student_id' => $studentId, 'subject_id' => $subjectId],
                        ['marks' => $marks]
                    );
                }
            }
    
            DB::commit();
            return response()->json(new JsonResponse(['message' => 'Marks updated successfully']));
        } catch (\Exception $ex) {
            DB::rollBack();
            return responseFailed($ex->getMessage());
        }
    }

    public function updateExam(int $id, array $data)
    {
        $exam = Exam::findOrFail($id);
        $exam->update([
            'title' => $data['title'],
            'class_id' => $data['class_id'],
        ]);

        // Update or create subjects
        foreach ($data['subjects'] as $subject) {
            ExamSubject::updateOrCreate(
                [
                    'exam_id' => $id,
                    'subject_id' => $subject['subject_id']
                ],
                [
                    'total_marks' => $subject['total_marks'],
                    'skip' => $subject['skip_in_report'],
                ]
            );
        }

        return $exam;
    }

    public function deleteExam(int $id)
    {
        return Exam::destroy($id);
    }

    public function getExamById(int $id)
    {
        return ExamResult::where('exam_id', $id)->get();
    }

    public function getExamReports(int $examId)
    {
        $exam = Exam::with(['classes', 'examSubjects' => function ($query) {
    $query->where('skip', false);
}, 'examSubjects.subject'])->findOrFail($examId);
        
        $students = Student::with('parents')->where('class_id', $exam->class_id)->get();
        $results = ExamResult::where('exam_id', $examId)->get();
        
        return [
            'students' => $students,
            'subjects' => $exam->examSubjects,
            'results' => $results,
            'exam' => $exam
        ];
    }

    public function getExamWithSubjects($examId)
    {
        $exam = Exam::with(['subjects' => function($query) {
            $query->select('exam_subjects.*', 'subjects.title');
        }])->findOrFail($examId);
        
        return $exam;
    }
}
