<?php

namespace App\Services;

use App\Laravue\JsonResponse;
use App\Models\ClassSession;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSubject;
use App\Models\Student;
use App\Services\Contracts\ExamServiceInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ExamService implements ExamServiceInterface
{
    const ITEM_PER_PAGE = 1000;

    public function listExams(array $searchParams)
    {
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $query = QueryBuilder::for(Exam::class)
            ->allowedFields(...['id', 'title', 'class_id', 'classes.name'])
            ->with(['classes', 'section'])
            ->allowedFilters(...[
                'id', 'title', 'class_id', 'created_at',
                AllowedFilter::exact('class_id'),

                AllowedFilter::callback('end_date', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('end_date', '>=', $value)
                            ->orWhereNull('end_date');
                    });
                }),
                AllowedFilter::callback('session_id', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        // the exam's own session, plus exams whose marks belong to students of
                        // this session. The last clause keeps a freshly added exam in the list —
                        // it has no exam_results yet, so neither session clause can match it, and
                        // hiding it is what made "Add Exam" look like it had not saved.
                        $q->where('session_id', $value)
                            ->orWhereHas('examResults.student', function ($sub) use ($value) {
                                $sub->where('session_id', $value);
                            })
                            ->orWhereDoesntHave('examResults');
                    });
                }),
            ])
            ->orderBy('id', 'desc');

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
                'section_id' => $data['section_id'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                // tag the exam with the session it was created in, like students and tests do
                'session_id' => $data['session_id'] ?? ClassSession::getDefault()?->id,
            ]);

            foreach ($data['subjects'] as $subject) {
                ExamSubject::create([
                    'exam_id' => $exam->id,
                    'subject_id' => $subject['subject_id'],
                    'total_marks' => $subject['total_marks'],
                    'skip' => $subject['skip_in_report'],
                    'exam_date' => $subject['exam_date'] ?? null,
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

            $absentMap = $data['absent'] ?? [];

            foreach ($data['marks'] ?? [] as $studentId => $subjects) {
                foreach ($subjects as $subjectId => $obtained_marks) {
                    // The absent map wins over whatever the grid posted for that cell.
                    $isAbsent = filter_var($absentMap[$studentId][$subjectId] ?? false, FILTER_VALIDATE_BOOLEAN);
                    ExamResult::updateOrCreate(
                        [
                            'exam_id' => $data['exam_id'],
                            'student_id' => $studentId,
                            'exam_subject_id' => $subjectId,
                        ],
                        [
                            'obtained_marks' => $isAbsent ? 0 : ($obtained_marks ?? 0),
                            'absent' => $isAbsent ? 'yes' : 'no',
                        ]
                    );
                }
            }

            // A student absent in every paper posts no marks at all, so record those rows here.
            foreach ($absentMap as $studentId => $subjects) {
                foreach ($subjects as $subjectId => $isAbsent) {
                    if (! filter_var($isAbsent, FILTER_VALIDATE_BOOLEAN)) {
                        continue;
                    }
                    ExamResult::updateOrCreate(
                        [
                            'exam_id' => $data['exam_id'],
                            'student_id' => $studentId,
                            'exam_subject_id' => $subjectId,
                        ],
                        [
                            'obtained_marks' => 0,
                            'absent' => 'yes',
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
            'section_id' => $data['section_id'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ]);

        foreach ($data['subjects'] as $subject) {
            $existing = ExamSubject::where('exam_id', $id)
                ->where('subject_id', $subject['subject_id'])
                ->first();

            if ($subject['skip_in_report']) {
                if ($existing) {
                    // Keep the row so skip state is preserved on future edits,
                    // but wipe results so totals are not inflated
                    $existing->examResults()->delete();
                    $existing->update(['skip' => true, 'total_marks' => $subject['total_marks'], 'exam_date' => $subject['exam_date'] ?? null]);
                } else {
                    // Brand-new subject immediately marked skip — store it so it re-opens correctly
                    ExamSubject::create([
                        'exam_id' => $id,
                        'subject_id' => $subject['subject_id'],
                        'total_marks' => $subject['total_marks'],
                        'skip' => true,
                        'exam_date' => $subject['exam_date'] ?? null,
                    ]);
                }
            } elseif ($existing) {
                // Un-skipping or updating an existing subject — preserve its exam_results
                $existing->update([
                    'total_marks' => $subject['total_marks'],
                    'skip' => false,
                    'exam_date' => $subject['exam_date'] ?? null,
                ]);
            } else {
                // Genuinely new subject added to the exam
                ExamSubject::create([
                    'exam_id' => $id,
                    'subject_id' => $subject['subject_id'],
                    'total_marks' => $subject['total_marks'],
                    'skip' => false,
                    'exam_date' => $subject['exam_date'] ?? null,
                ]);
            }
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

    public function getExamReports(int $examId, $sessionId = null)
    {
        $exam = Exam::with(['classes', 'examSubjects' => function ($query) {
            $query->where('skip', false);
        }, 'examSubjects.subject'])->findOrFail($examId);

        // Filter students by section_id if it exists, otherwise by class_id
        $studentsQuery = Student::with('parents')->where('class_id', $exam->class_id);
        if ($exam->section_id) {
            $studentsQuery->where('section_id', $exam->section_id);
        }
        if ($sessionId) {
            $studentsQuery->where('session_id', $sessionId);
        }
        $students = $studentsQuery->get();

        $results = ExamResult::where('exam_id', $examId)->get();

        return [
            'students' => $students,
            'subjects' => $exam->examSubjects,
            'results' => $results,
            'exam' => $exam,
        ];
    }

    public function getExamWithSubjects($examId)
    {
        $exam = Exam::with(['subjects' => function ($query) {
            $query->select('exam_subjects.*', 'subjects.title');
        }])->findOrFail($examId);

        return $exam;
    }
}
