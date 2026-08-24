<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Test;
use App\Models\TestResult;
use App\Notifications\TestOneSignalNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TestService
{
    const ITEM_PER_PAGE = 100;

    public function getAllTests($searchParams = [])
    {
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $query = QueryBuilder::for(Test::class)
            ->allowedIncludes(...['class', 'subject', 'teacher', 'section', 'testResults', 'testResults.student', 'testResults.student.parents'])
            ->allowedFilters(...[
                'id', 'class_id', 'subject_id', 'teacher_id', 'title', 'date',
                AllowedFilter::exact('session_id'),
            ])
            ->orderBy('id', 'desc');

        return $query->paginate($limit)
            ->appends(request()->query());
    }

    public function createTest(array $data)
    {
        $validatedData = $this->validateTestData($data);
        $test = Test::create($validatedData);

        return $test;

    }

    public function createTestWithResults(array $data)
    {
        $validatedData = $this->validateTestData($data);
        $test = Test::create($validatedData);
        $testresult = [];
        foreach ($data['students'] as $student) {
            $testresult[] = [
                'test_id' => $test->id,
                'student_id' => $student['id'],
                'absent' => $student['absent'] ?? 'no',
                'score' => ($student['score']) ?? 0,
            ];
        }
        TestResult::insert($testresult);

        // Push result notification to the parents of every student in the test
        // (subject name + obtained/total marks; absent students are notified as absent).
        $this->sendTestResultPushNotifications($test, $data['students']);

        return $test;

    }

    /**
     * Send a OneSignal push to each student's parent with the test result.
     */
    protected function sendTestResultPushNotifications(Test $test, array $students): void
    {
        $test->load('subject');
        $subjectTitle = $test->subject->title ?? 'Test';
        $totalMarks = (float) $test->total_marks;

        $scoresByStudent = collect($students)->keyBy('id');
        $studentsWithParents = Student::with('parents.user')
            ->whereIn('id', $scoresByStudent->keys())
            ->get();

        foreach ($studentsWithParents as $student) {
            $parentUser = $student->parents->user ?? null;
            if (! $parentUser || ! $parentUser->player_id) {
                continue;
            }

            $payload = $scoresByStudent->get($student->id);
            $isAbsent = ($payload['absent'] ?? 'no') === 'yes';
            $body = $isAbsent
                ? "Your child {$student->name} was absent in the {$subjectTitle} test on {$test->date}."
                : "Your child {$student->name} scored {$payload['score']} out of {$totalMarks} in {$subjectTitle}.";

            try {
                $parentUser->notify(new TestOneSignalNotification("Test Result: {$subjectTitle}", $body));
            } catch (\Exception $e) {
                // Never let a push failure fail the test save
            }
        }
    }

    public function getTestById($id)
    {
        return Test::with('testResults')->findOrFail($id);
    }

    public function updateTest($id, array $data)
    {
        DB::beginTransaction();
        try {
            $validatedData = $this->validateTestData($data);
            $test = Test::findOrFail($id);
            // Check if test exists
            if ($test) {
                $testresult = [];
                foreach ($data['students'] as $student) {
                    $testresultarray = [
                        'test_id' => $test->id,
                        'student_id' => $student['id'],
                        'score' => ($student['score']) ?? 0,
                        'absent' => $student['absent'] ?? 'no',
                    ];
                    $test_result_id = $student['test_result_id'] ?? '0';
                    $testResult = TestResult::firstOrNew(['id' => $test_result_id]);
                    $testResult = TestResult::updateOrCreate(['id' => $test_result_id], $testresultarray);
                }
            } else {
                return response()->json([
                    'message' => 'Test not found!',
                ], 404);
            }
            $test->update($validatedData);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $test;
    }

    public function deleteTest($id)
    {
        $test = Test::findOrFail($id);
        $test->delete();

        return response()->json(null, 204);
    }

    protected function validateTestData(array $data)
    {
        return validator($data, [
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'total_marks' => 'required|numeric',
            'session_id' => 'nullable|integer',
        ])->validate();
    }

    public function getAllTestResults($searchParams = [])
    {
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $activeSession = ClassSession::getActive();

        return QueryBuilder::for(TestResult::class)
            ->with('student')
            ->allowedIncludes(...['student', 'test', 'test.subject'])
            ->allowedFilters(...[
                'id', 'test_id', 'student_id', 'absent',
            ])
            ->when($activeSession, function ($query) use ($activeSession) {
                $query->whereHas('test', function ($q) use ($activeSession) {
                    $q->where('session_id', $activeSession->id);
                });
            })
            ->paginate($limit)
            ->appends(request()->query());
    }

    public function createTestResult(array $data)
    {
        $validatedData = $this->validateTestResultData($data);

        return TestResult::create($validatedData);
    }

    public function getTestResultById($id)
    {
        return TestResult::findOrFail($id);
    }

    public function updateTestResult($id, array $data)
    {
        // $validatedData = validator($data, [
        //     'test_results' => 'required|array',
        // ])->validate();
        $testResult = TestResult::findOrFail($id);
        $testResult->update($data);

        return $testResult;
    }

    public function deleteTestResult($id)
    {
        $testResult = TestResult::findOrFail($id);
        $testResult->delete();

        return response()->json(null, 204);
    }

    protected function validateTestResultData(array $data)
    {
        return validator($data, [
            'test_id' => 'required|exists:tests,id',
            'student_id' => 'required|exists:students,id',
            'score' => 'required|numeric',
            'absent' => 'required',
        ])->validate();
    }
}
