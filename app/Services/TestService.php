<?php
namespace App\Services;

use App\Models\Test;
use App\Models\TestResult;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class TestService
{
    const ITEM_PER_PAGE = 100;

    public function getAllTests($searchParams = [])
    {
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        return QueryBuilder::for(Test::class)
            ->allowedIncludes(['class', 'subject', 'testResults','testResults.student','testResults.student.parents'])
            ->allowedFilters([
                'id','class_id', 'subject_id', 'title', 'date'
            ])
            ->paginate($limit)
            ->appends(request()->query());
        return Test::with(['class', 'subject'])->get();
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
        $testresult = array();
        foreach($data['students'] as $student){
            $testresult[] = [
                'test_id' => $test->id,
                'student_id' => $student['id'],
                'score' => ($student['score']) ?? 0,
            ];
        }
        TestResult::insert($testresult);
        return $test;

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
                $testresult = array();
                foreach($data['students'] as $student){
                    $testresultarray = [
                        'test_id' => $test->id,
                        'student_id' => $student['id'],
                        'score' => ($student['score']) ?? 0,
                    ];
                    $test_result_id = $student['test_result_id'] ?? '0';
                    $testResult = TestResult::firstOrNew(array('id' => $test_result_id) );
                    $testResult = TestResult::updateOrCreate( ['id' => $test_result_id],  $testresultarray );
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
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'total_marks' => 'required|numeric',
        ])->validate();
    }

    public function getAllTestResults($searchParams = [])
    {
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        return QueryBuilder::for(TestResult::class)
            ->with('student')
            ->allowedFilters([
                'id','test_id', 'student_id'
            ])
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
        ])->validate();
    }
}